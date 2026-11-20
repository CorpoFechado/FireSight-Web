import os
import sys
import time
import socket
import threading
from concurrent.futures import ThreadPoolExecutor
import requests

# Force OpenCV FFmpeg to use TCP and zero-latency options for RTSP (prevents packet loss & buffer lag)
os.environ["OPENCV_FFMPEG_CAPTURE_OPTIONS"] = "rtsp_transport;tcp|fflags;nobuffer|flags;low_delay|max_delay;500000|stimeout;3000000"
os.environ["OPENCV_LOG_LEVEL"] = "ERROR"
os.environ["OPENCV_FFMPEG_LOGLEVEL"] = "-8"

import cv2
import numpy as np
from flask import Flask, Response, request, jsonify
from flask_cors import CORS
from fire_detection import detector

def create_offline_frame(main_text="CAMERA OFFLINE", sub_text="Reconnecting"):
    # Industry standard CCTV / NVR video loss screen (Pure dark charcoal)
    frame = np.zeros((360, 640, 3), dtype=np.uint8)
    frame[:] = (14, 14, 14)  # Clean CCTV charcoal black (no brown tint)

    # Main title: Crisp white industry-standard offline message
    (tw1, th1), _ = cv2.getTextSize(main_text, cv2.FONT_HERSHEY_SIMPLEX, 0.85, 2)
    tx1 = (640 - tw1) // 2
    cv2.putText(frame, main_text, (tx1, 170), cv2.FONT_HERSHEY_SIMPLEX, 0.85, (245, 245, 245), 2, cv2.LINE_AA)

    # Sub text with animated dots based on current time
    dots = "." * (int(time.time() * 2) % 4)
    full_sub = f"{sub_text}{dots}"
    (tw2, th2), _ = cv2.getTextSize(full_sub, cv2.FONT_HERSHEY_SIMPLEX, 0.46, 1)
    tx2 = (640 - tw2) // 2
    cv2.putText(frame, full_sub, (tx2, 205), cv2.FONT_HERSHEY_SIMPLEX, 0.46, (160, 165, 175), 1, cv2.LINE_AA)

    ret, buf = cv2.imencode('.jpg', frame, [int(cv2.IMWRITE_JPEG_QUALITY), 80])
    return buf.tobytes() if ret else None

# ---------------------------------------------------------
# Environment Configuration
# ---------------------------------------------------------
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
ENV_PATH = os.path.join(BASE_DIR, '.env')

def load_env(env_path):
    if os.path.exists(env_path):
        with open(env_path, 'r', encoding='utf-8') as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith('#') and '=' in line:
                    k, v = line.split('=', 1)
                    os.environ.setdefault(k.strip(), v.strip().strip("'\""))

load_env(ENV_PATH)

RTSP_USER = os.getenv('RTSP_USER', '')
RTSP_PASS = os.getenv('RTSP_PASSWORD', '')
CAMERA_IP = os.getenv('CAMERA_IP', '')
CAMERA_PORT = int(os.getenv('RTSP_PORT', '554'))
STREAM_PATH = os.getenv('RTSP_STREAM_PATH', 'stream2')
RTSP_OVERRIDE = os.getenv('RTSP_URL', '')

if RTSP_OVERRIDE:
    DEFAULT_RTSP_URL = RTSP_OVERRIDE
elif RTSP_USER and CAMERA_IP and CAMERA_IP not in ('', '192.168.1.1'):
    DEFAULT_RTSP_URL = f"rtsp://{RTSP_USER}:{RTSP_PASS}@{CAMERA_IP}:{CAMERA_PORT}/{STREAM_PATH}"
else:
    DEFAULT_RTSP_URL = ""

LARAVEL_API_URL = os.getenv('LARAVEL_API_URL', 'http://127.0.0.1:8000/api/cctv/alert')
ALERT_WEBHOOK_SECRET = os.getenv('CCTV_WEBHOOK_SECRET', 'firesight_cctv_secret_key')

JPEG_QUALITY = int(os.getenv('JPEG_QUALITY', '70'))
FLASK_HOST = os.getenv('FLASK_HOST', '0.0.0.0')
FLASK_PORT = int(os.getenv('FLASK_PORT', '5001'))

def get_local_ip():
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect(("8.8.8.8", 80))
        ip = s.getsockname()[0]
        s.close()
        return ip
    except Exception:
        return socket.gethostbyname(socket.gethostname())

def discover_rtsp_camera(port=CAMERA_PORT, timeout=0.8):
    local_ip = get_local_ip()
    parts = local_ip.split('.')
    if len(parts) != 4:
        return None
    prefix = '.'.join(parts[:3])

    def check_ip(ip):
        if ip == local_ip:
            return None
        try:
            with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
                s.settimeout(timeout)
                if s.connect_ex((ip, port)) == 0:
                    return ip
        except Exception:
            pass
        return None

    targets = [f"{prefix}.{i}" for i in range(1, 255)]
    with ThreadPoolExecutor(max_workers=32) as executor:
        for result in executor.map(check_ip, targets):
            if result:
                return result
    return None

# ---------------------------------------------------------
# Decoupled Dual-Thread Camera Stream Engine
# ---------------------------------------------------------
class AsynchronousCameraStream:
    def __init__(self, rtsp_url, camera_token='default', quality=80):
        self.rtsp_url = rtsp_url
        self.camera_token = camera_token
        self.quality = quality

        self.offline_jpeg = create_offline_frame()
        self.raw_frame = None
        self.annotated_jpeg = self.offline_jpeg
        self.latest_result = None

        self.frame_lock = threading.Lock()
        self.output_lock = threading.Lock()
        self.running = True
        self.connected = False
        self.last_frame_time = 0.0
        self.last_alert_time = 0

        self.capture_thread = threading.Thread(target=self._capture_worker, daemon=True)
        self.capture_thread.start()

        self.ai_thread = threading.Thread(target=self._ai_worker, daemon=True)
        self.ai_thread.start()

    def update_url(self, new_url):
        if new_url != self.rtsp_url:
            self.rtsp_url = new_url
            self.connected = False

    def _capture_worker(self):
        while self.running:
            if not self.rtsp_url:
                with self.output_lock:
                    self.annotated_jpeg = self.offline_jpeg
                time.sleep(1)
                continue

            cap = cv2.VideoCapture()
            cap.open(self.rtsp_url, cv2.CAP_FFMPEG, [cv2.CAP_PROP_OPEN_TIMEOUT_MSEC, 3000])
            cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)

            if not cap.isOpened():
                self.connected = False
                self.last_frame_time = 0.0
                with self.output_lock:
                    self.annotated_jpeg = self.offline_jpeg
                cap.release()
                time.sleep(2)
                continue

            self.connected = True
            consecutive_failures = 0
            while self.running and cap.isOpened():
                success, frame = cap.read()
                if not success or frame is None:
                    consecutive_failures += 1
                    # Tolerate temporary Wi-Fi jitter; only break if 25 consecutive reads fail (~2.5s)
                    if consecutive_failures >= 25:
                        print(f"[{self.camera_token}] Camera read failed or stream interrupted ({consecutive_failures} dropped frames). Reconnecting...")
                        break
                    time.sleep(0.05)
                    continue

                consecutive_failures = 0
                self.last_frame_time = time.time()
                self.connected = True

                with self.frame_lock:
                    self.raw_frame = frame

                # Draw YOLO bounding boxes
                annotated = frame.copy()
                if self.latest_result is not None:
                    annotated = detector.draw_hud(annotated, self.latest_result)
                else:
                    annotated = detector.draw_hud(annotated)

                ret, buffer = cv2.imencode('.jpg', annotated, [int(cv2.IMWRITE_JPEG_QUALITY), self.quality])
                if ret:
                    with self.output_lock:
                        self.annotated_jpeg = buffer.tobytes()

                time.sleep(0.005)

            self.connected = False
            self.last_frame_time = 0.0
            with self.frame_lock:
                self.raw_frame = None
            with self.output_lock:
                self.annotated_jpeg = self.offline_jpeg
            self.latest_result = None
            cap.release()
            time.sleep(1)

    def _ai_worker(self):
        while self.running:
            if not self.connected:
                time.sleep(0.25)
                continue

            target_frame = None
            with self.frame_lock:
                if self.raw_frame is not None:
                    target_frame = self.raw_frame.copy()

            if target_frame is None:
                time.sleep(0.05)
                continue

            try:
                res = detector.predict(target_frame)
                self.latest_result = res

                # Fire alert trigger (debounced to 30 seconds between push notifications)
                is_fire = res.get("fire", False)
                top_conf = res.get("top_confidence", 0.0)

                if is_fire and top_conf >= 0.60:
                    now = time.time()
                    if now - self.last_alert_time >= 30.0:
                        self.last_alert_time = now
                        print(f"[{self.camera_token}] Triggering fire alert! Top confidence: {top_conf:.2f}")
                        self._trigger_laravel_alert(res)

            except Exception as e:
                print(f"[AI Worker Error] {e}")

            time.sleep(0.08)

    def _trigger_laravel_alert(self, detection_result):
        """Sends an immediate webhook to Laravel backend to dispatch push notification to resident."""
        payload = {
            "token": self.camera_token,
            "secret": ALERT_WEBHOOK_SECRET,
            "type": "fire",
            "flames_count": detection_result.get("fire_count", 1),
            "confidence": detection_result.get("score_percent", 0.0),
            "timestamp": int(time.time()),
        }
        endpoints = [
            LARAVEL_API_URL,
            "http://127.0.0.1/api/cctv/alert",
            "http://127.0.0.1:8000/api/cctv/alert",
            "http://localhost/api/cctv/alert",
            "http://localhost/api/v1/cctv/alert",
        ]
        seen = set()
        unique_endpoints = [x for x in endpoints if x and not (x in seen or seen.add(x))]

        for ep in unique_endpoints:
            try:
                res = requests.post(ep, json=payload, timeout=2.0)
                if res.status_code == 200:
                    print(f"[AlertWebhook] Dispatched to Laravel ({ep}). Response: {res.json()}")
                    return
                else:
                    print(f"[AlertWebhook] Attempted {ep} -> Status: {res.status_code}")
            except Exception as e:
                continue
        print("[AlertWebhook Error] Failed to contact Laravel on any endpoint.")

    def get_frame(self):
        now = time.time()
        # If camera is disconnected OR has not delivered a frame in > 4.5s (camera GOP / Wi-Fi buffer), serve offline frame
        if not self.connected or (self.last_frame_time > 0 and (now - self.last_frame_time > 4.5)):
            return create_offline_frame()

        with self.output_lock:
            if self.annotated_jpeg is not None:
                return self.annotated_jpeg
        return create_offline_frame()

    def stop(self):
        self.running = False

# Map of camera_token -> AsynchronousCameraStream instance
active_cameras = {}

def get_or_create_stream(token, rtsp_url):
    if token not in active_cameras:
        stream = AsynchronousCameraStream(rtsp_url, camera_token=token, quality=JPEG_QUALITY)
        active_cameras[token] = stream
    else:
        active_cameras[token].update_url(rtsp_url)
    return active_cameras[token]

# Initialize default camera if configured
if DEFAULT_RTSP_URL and 'default' not in active_cameras:
    get_or_create_stream('default', DEFAULT_RTSP_URL)

# ---------------------------------------------------------
# Flask App
# ---------------------------------------------------------
app = Flask(__name__)
CORS(app)

@app.route('/health')
def health():
    stream = active_cameras.get('default')
    connected = stream.connected if stream else False
    return jsonify({
        "status": "online",
        "service": "FireSight Fire Detection Stream Service",
        "model": "fsyv8.onnx (YOLO)",
        "active_streams": len(active_cameras),
        "default_connected": connected
    })

@app.route('/api/configure', methods=['POST'])
def configure_camera():
    data = request.get_json(silent=True) or {}
    token = data.get('token', 'default')
    rtsp_url = data.get('rtsp_url', '')

    if not rtsp_url:
        return jsonify({"success": False, "message": "Missing rtsp_url"}), 400

    stream = get_or_create_stream(token, rtsp_url)
    return jsonify({
        "success": True,
        "token": token,
        "connected": stream.connected,
        "stream_url": f"/stream/{token}"
    })

def resolve_camera_from_laravel(token):
    """Auto-fetches RTSP URL from Laravel if app.py was restarted or stream is not yet in memory."""
    endpoints = [
        f"http://127.0.0.1:8000/api/cctv/resolve/{token}",
        f"http://127.0.0.1:8000/api/v1/cctv/resolve/{token}",
        f"http://localhost/api/cctv/resolve/{token}",
    ]
    for url in endpoints:
        try:
            res = requests.get(url, timeout=2.0)
            if res.status_code == 200:
                payload = res.json()
                data = payload.get("data") if (isinstance(payload, dict) and isinstance(payload.get("data"), dict)) else payload
                rtsp_url = data.get("rtsp_url") if isinstance(data, dict) else None
                if rtsp_url:
                    print(f"[AutoResolve] Successfully resolved camera token [{token}] -> {rtsp_url}")
                    return rtsp_url
        except Exception:
            continue
    print(f"[AutoResolve] Unable to resolve token [{token}] from Laravel.")
    return None

@app.route('/stream/<token>')
@app.route('/video_feed')
def video_feed(token='default'):
    stream = active_cameras.get(token)
    if not stream:
        rtsp_url = resolve_camera_from_laravel(token)
        if rtsp_url:
            stream = get_or_create_stream(token, rtsp_url)
        else:
            stream = active_cameras.get('default')

    if not stream:
        return Response("Camera stream not configured.", status=404)

    def generate():
        while True:
            frame = stream.get_frame()
            if frame is None:
                time.sleep(0.05)
                continue
            yield (b'--frame\r\n'
                   b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')
            time.sleep(0.045)  # Optimal ~22 FPS: eliminates network buffer bloat and lag

    return Response(generate(), mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/telemetry/<token>')
@app.route('/api/status')
def telemetry(token='default'):
    stream = active_cameras.get(token)
    if not stream:
        rtsp_url = resolve_camera_from_laravel(token)
        if rtsp_url:
            stream = get_or_create_stream(token, rtsp_url)
        else:
            stream = active_cameras.get('default')

    if not stream:
        return jsonify({"connected": False, "detection": None})

    res = stream.latest_result or detector.latest_result
    return jsonify({
        "connected": stream.connected,
        "detection": res
    })

if __name__ == '__main__':
    print(f"[FireDetectionService] Running on {FLASK_HOST}:{FLASK_PORT}")
    app.run(host=FLASK_HOST, port=FLASK_PORT, threaded=True, debug=False)
