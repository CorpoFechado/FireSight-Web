# FireSight Fire Verification Service

FastAPI AI microservice that runs the trained MobileNetV3 ONNX model (`fire_verification_model.onnx`) to classify uploaded fire incident photos.

Called automatically by Laravel (`App\Services\FireVerificationService`) on port `8000`.

## Quick Start (Local Development)

### 1. Create and Activate Virtual Environment
```bash
python -m venv .venv
# On Windows PowerShell:
.venv\Scripts\Activate.ps1
# On Windows Command Prompt:
.venv\Scripts\activate.bat
# On macOS / Linux:
source .venv/bin/activate
```

### 2. Install Dependencies
```bash
pip install -r requirements.txt
```

### 3. Run the Service
```bash
uvicorn main:app --reload --port 8000
```

## Endpoints
* **`GET /health`** — Verify the service is online and the model is loaded.
* **`POST /predict`** — Accepts `multipart/form-data` with field `image` and returns:
  ```json
  {
    "fire": true,
    "confidence": 0.97,
    "label": "fire"
  }
  ```
