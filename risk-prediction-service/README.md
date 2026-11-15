# FireSight Risk Prediction Service

Python machine learning inference pipeline for Lian monthly fire risk assessment using an **XGBoost Classifier** (`fire_risk_xgb.json`).

The model predicts fire risk levels (`mild`, `moderate`, `high`) and numerical prediction scores across all 19 barangays of Lian, Batangas based on 16 spatio-temporal features (historical incident trends, demographic density, causes, alarms, damages, and monthly weather metrics).

---

## Service Contents

* **`predict_risk.py`** — Main prediction script. Queries live incidents directly from MySQL (`incident_record` + `community_report`), reads demographics, fetches live weather via Open-Meteo API, runs XGBoost inference, and commits predictions into the `risk_assessment` table.
* **`fire_risk_xgb.json`** — Trained 177-tree multi-class XGBoost model exported from Google Colab.
* **`requirements.txt`** — Python dependencies (`xgboost`, `scikit-learn`, `pandas`, `numpy`, `pymysql`).
* **`lian_barangay_demographics_area.csv`** — Official PSA demographics & land area for the 19 barangays of Lian (Option A).
* **`lian_weather_monthly.csv`** — Monthly rainfall and temperature reference data (fallback when offline).

---

## Setup & Installation

### 1. Create a Python Virtual Environment
```bash
cd risk-prediction-service
python -m venv .venv

# On Windows PowerShell:
.venv\Scripts\Activate.ps1
# On Windows Command Prompt:
.venv\Scripts\activate.bat
```

### 2. Install Required Packages
```bash
pip install -r requirements.txt
```

---

## Running Predictions

### A. Dry Run (Generates CSV output only, no database modification)
```bash
python predict_risk.py --as-of 2025-11
```

### B. Write to FireSight MySQL Database (`risk_assessment` table)
```bash
python predict_risk.py --as-of 2025-11 --write-db
```

### C. Run via Laravel Artisan Command (from FireSight-Web root)
```bash
php artisan firesight:predict-risk
```
*(Runs automatically on the 1st of every month at 01:00 AM via Laravel Scheduler in `routes/console.php`)*.
