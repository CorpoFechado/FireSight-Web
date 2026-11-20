import os
import cv2
import numpy as np
from PIL import Image
import io

# ---------------------------------------------------------
# Configuration
# ---------------------------------------------------------
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
# Check for YOLO best.onnx first, then fallback
YOLO_MODEL_PATH = os.path.join(BASE_DIR, "model", "fsyv8.onnx")
LEGACY_MODEL_PATH = os.path.join(BASE_DIR, "model", "fsyv8.onnx")

MODEL_PATH = YOLO_MODEL_PATH if os.path.exists(YOLO_MODEL_PATH) else LEGACY_MODEL_PATH

IMG_SIZE = (640, 640)        # YOLO11n input resolution
CONF_THRESHOLD = 0.65       # Bounding box confidence threshold
IOU_THRESHOLD = 0.45         # Non-Maximum Suppression (NMS) threshold
CLASSES = ["fire", "smoke"]  # Trained 2-class setup

# Colors for bounding boxes (BGR)
COLOR_FIRE = (0, 0, 255)     # Bright Red for Fire
COLOR_SMOKE = (0, 215, 255)  # Amber / Yellow for Smoke
COLOR_NORMAL = (0, 220, 80)  # Bright Green

# ---------------------------------------------------------
# YOLO11n Fire & Smoke Detection Engine
# ---------------------------------------------------------
class FireDetector:
    """Loads YOLO11n ONNX model and detects Fire & Smoke bounding boxes in real-time."""

    def __init__(self, model_path=MODEL_PATH, conf_threshold=CONF_THRESHOLD, iou_threshold=IOU_THRESHOLD):
        self.model_path = model_path
        self.conf_threshold = conf_threshold
        self.iou_threshold = iou_threshold
        self.net = None
        self.ort_session = None
        self.is_yolo = True

        self.latest_result = {
            "fire": False,
            "smoke": False,
            "detections": [],
            "fire_count": 0,
            "smoke_count": 0,
            "top_confidence": 0.0,
            "label": "normal",
            "score_percent": 0.0
        }
        self.load_model()

    def load_model(self):
        if not os.path.exists(self.model_path):
            print(f"[FireDetector] Model file not found at: {self.model_path}")
            return False

        # 1. Try onnxruntime if installed
        try:
            import onnxruntime as ort
            self.ort_session = ort.InferenceSession(self.model_path, providers=["CPUExecutionProvider"])
            print(f"[FireDetector] Loaded {os.path.basename(self.model_path)} via onnxruntime.")
            return True
        except ImportError:
            pass

        # 2. Fallback to OpenCV cv2.dnn
        try:
            self.net = cv2.dnn.readNetFromONNX(self.model_path)
            print(f"[FireDetector] Loaded {os.path.basename(self.model_path)} via cv2.dnn.")
            return True
        except Exception as e:
            print(f"[FireDetector] Failed to load ONNX model: {e}")
            return False

    def preprocess_frame(self, frame_bgr: np.ndarray):
        """Preprocesses frame to 640x640 with letterbox / ratio calculation."""
        h, w = frame_bgr.shape[:2]
        scale_x = w / IMG_SIZE[0]
        scale_y = h / IMG_SIZE[1]

        blob = cv2.dnn.blobFromImage(
            frame_bgr,
            scalefactor=1.0 / 255.0,
            size=IMG_SIZE,
            swapRB=True,
            crop=False
        )
        return blob, scale_x, scale_y

    def predict(self, frame_bgr: np.ndarray) -> dict:
        """Runs YOLO11n inference, parses outputs, and applies NMS."""
        if self.net is None and self.ort_session is None:
            return self.latest_result

        h, w = frame_bgr.shape[:2]
        blob, scale_x, scale_y = self.preprocess_frame(frame_bgr)

        try:
            if self.ort_session is not None:
                input_name = self.ort_session.get_inputs()[0].name
                raw_out = self.ort_session.run(None, {input_name: blob})[0]
            else:
                self.net.setInput(blob)
                raw_out = self.net.forward()

            # YOLO11 shape is typically (1, 6, 8400) where 6 = [x_center, y_center, width, height, class0_conf, class1_conf]
            # If shape is (1, 6, 8400), transpose to (8400, 6)
            if raw_out.ndim == 3 and raw_out.shape[1] == 6:
                preds = np.squeeze(raw_out, axis=0).T
            elif raw_out.ndim == 3 and raw_out.shape[2] == 6:
                preds = np.squeeze(raw_out, axis=0)
            else:
                preds = np.squeeze(raw_out)
                if preds.shape[0] < preds.shape[1]:
                    preds = preds.T

            boxes = []
            confidences = []
            class_ids = []

            for row in preds:
                cls_scores = row[4:]
                cls_id = int(np.argmax(cls_scores))
                score = float(cls_scores[cls_id])

                if score >= self.conf_threshold:
                    xc, yc, bw, bh = row[0], row[1], row[2], row[3]
                    # Rescale coordinates back to original frame size
                    x1 = int((xc - bw / 2.0) * scale_x)
                    y1 = int((yc - bh / 2.0) * scale_y)
                    box_w = int(bw * scale_x)
                    box_h = int(bh * scale_y)

                    # Clamp inside frame
                    x1 = max(0, x1)
                    y1 = max(0, y1)

                    boxes.append([x1, y1, box_w, box_h])
                    confidences.append(score)
                    class_ids.append(cls_id)

            # Apply Non-Maximum Suppression (NMS)
            indices = cv2.dnn.NMSBoxes(boxes, confidences, self.conf_threshold, self.iou_threshold)

            detections = []
            fire_count = 0
            smoke_count = 0
            top_conf = 0.0

            if len(indices) > 0:
                for idx in np.array(indices).flatten():
                    c_id = class_ids[idx]
                    label = CLASSES[c_id] if c_id < len(CLASSES) else f"class_{c_id}"
                    conf = confidences[idx]
                    box = boxes[idx]

                    if label == "fire":
                        fire_count += 1
                    elif label == "smoke":
                        smoke_count += 1

                    if conf > top_conf:
                        top_conf = conf

                    detections.append({
                        "class_id": c_id,
                        "label": label,
                        "confidence": round(conf, 3),
                        "box": box  # [x, y, w, h]
                    })

            is_fire = fire_count > 0
            is_smoke = smoke_count > 0
            has_incident = is_fire or is_smoke

            primary_label = "fire" if is_fire else ("smoke" if is_smoke else "normal")

            self.latest_result = {
                "fire": is_fire,
                "smoke": is_smoke,
                "has_incident": has_incident,
                "detections": detections,
                "fire_count": fire_count,
                "smoke_count": smoke_count,
                "top_confidence": round(top_conf, 3),
                "label": primary_label,
                "score_percent": round(top_conf * 100, 1)
            }
            return self.latest_result

        except Exception as e:
            print(f"[FireDetector] Prediction error: {e}")
            return self.latest_result

    def draw_hud(self, frame_bgr: np.ndarray, result: dict = None) -> np.ndarray:
        """Draws bounding boxes on detected fire/smoke + modern HUD telemetry overlay."""
        if result is None:
            result = self.latest_result

        h, w = frame_bgr.shape[:2]
        detections = result.get("detections", [])
        is_fire = result.get("fire", False)
        is_smoke = result.get("smoke", False)

        # 1. Draw Bounding Boxes ONLY for detected fire (flames)
        for det in detections:
            label = det["label"]
            if label != "fire":
                continue  # Skip smoke bounding boxes so they don't clutter the display

            x, y, bw, bh = det["box"]
            conf = det["confidence"]
            color = COLOR_FIRE

            # Draw bounding box rectangle
            cv2.rectangle(frame_bgr, (x, y), (x + bw, y + bh), color, 2)

            # Draw label tag with filled background
            tag = f"FIRE {int(conf * 100)}%"
            (tw, th), _ = cv2.getTextSize(tag, cv2.FONT_HERSHEY_SIMPLEX, 0.5, 1)
            tag_y1 = max(0, y - th - 8)
            cv2.rectangle(frame_bgr, (x, tag_y1), (x + tw + 8, tag_y1 + th + 6), color, -1)
            cv2.putText(frame_bgr, tag, (x + 4, tag_y1 + th + 2),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.45, (10, 10, 10), 1, cv2.LINE_AA)

        # 2. Draw subtle border alert on screen edge when fire is detected
        if is_fire:
            cv2.rectangle(frame_bgr, (0, 0), (w - 1, h - 1), COLOR_FIRE, 3)

        return frame_bgr

# Global detector instance
detector = FireDetector()

if __name__ == "__main__":
    print("[FireDetector] Testing YOLO11n model with dummy 640x360 frame...")
    dummy = np.zeros((360, 640, 3), dtype=np.uint8)
    res = detector.predict(dummy)
    print("Test Output:", res)
