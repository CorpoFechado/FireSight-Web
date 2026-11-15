"""
predict_risk_db.py - FireSight Live Database-Driven Fire Risk Prediction (Option A)

Architecture:
  - Incidents: Loaded dynamically from MySQL (`incident_record` + `community_report`).
  - Demographics: Read from static reference file `lian_barangay_demographics_area.csv`.
  - Weather: Read from `lian_weather_monthly.csv`.
  - Predictions: Computed via `fire_risk_xgb.json` and saved to MySQL table `risk_assessment`.

Usage:
  Dry run (CSV only):        python predict_risk_db.py --as-of 2025-11
  Save to MySQL database:    python predict_risk_db.py --as-of 2025-11 --write-db
  Auto (latest month):       python predict_risk_db.py --write-db
"""
import argparse
import os
import sys

import numpy as np
import pandas as pd
import pymysql
import xgboost as xgb

BASE_DIR = os.path.dirname(os.path.abspath(__file__))


def resolve_file(filename):
    local_path = os.path.join(BASE_DIR, filename)
    if os.path.exists(local_path):
        return local_path
    dataset_path = os.path.join(BASE_DIR, "..", "dataset", filename)
    if os.path.exists(dataset_path):
        return dataset_path
    return local_path


# ------------------------- CONFIGURATION -------------------------
MODEL_PATH       = resolve_file("fire_risk_xgb.json")
DEMOGRAPHICS_CSV = resolve_file("lian_barangay_demographics_area.csv")
WEATHER_CSV      = resolve_file("lian_weather_monthly.csv")
OUTPUT_CSV       = os.path.join(BASE_DIR, "risk_assessment_upload.csv")

DB = dict(
    host=os.getenv("DB_HOST", "127.0.0.1"),
    port=int(os.getenv("DB_PORT", 3306)),
    user=os.getenv("DB_USERNAME", "root"),
    password=os.getenv("DB_PASSWORD", ""),
    database=os.getenv("DB_DATABASE", "firesight_db2"),
    charset="utf8mb4",
    cursorclass=pymysql.cursors.DictCursor,
)

CLASS_NAMES = ["mild", "moderate", "high"]
# ------------------------------------------------------------------


def group_cause(text):
    t = str(text or "").lower()
    if any(k in t for k in ["cooking", "lpg", "stove", "gasul", "kitchen"]):
        return "cooking_lpg"
    if any(k in t for k in ["electrical", "short circuit", "wiring", "wire", "breaker", "transformer", "utility pole", "power strip", "appliance", "overload"]):
        return "electrical"
    if any(k in t for k in ["open", "flame", "clearing", "kaingin", "siga", "candle", "katol", "cigarette", "rubbish", "burning", "stubble"]):
        return "open_flame_burning"
    return "other"


def fetch_weather_open_meteo(as_of):
    """
    Fetches real monthly weather data (rainfall and temperature) for Lian, Batangas
    directly from the free Open-Meteo API for month `as_of` and the 2 preceding months.
    Coordinates: Latitude 14.0392, Longitude 120.6389 (Lian, Batangas).
    """
    import json
    import urllib.request

    start_period = as_of - 2
    start_date = str(start_period.start_time.date())
    end_date = str(as_of.end_time.date())

    lat, lon = 14.0392, 120.6389
    urls = [
        f"https://archive-api.open-meteo.com/v1/archive?latitude={lat}&longitude={lon}&start_date={start_date}&end_date={end_date}&daily=temperature_2m_mean,precipitation_sum&timezone=Asia%2FManila",
        f"https://api.open-meteo.com/v1/forecast?latitude={lat}&longitude={lon}&start_date={start_date}&end_date={end_date}&daily=temperature_2m_mean,precipitation_sum&timezone=Asia%2FManila",
    ]

    for url in urls:
        try:
            req = urllib.request.Request(url, headers={"User-Agent": "FireSight/1.0"})
            with urllib.request.urlopen(req, timeout=10) as resp:
                data = json.loads(resp.read().decode())
                if "daily" in data and "time" in data["daily"]:
                    df_daily = pd.DataFrame({
                        "date": pd.to_datetime(data["daily"]["time"]),
                        "temp_c": data["daily"]["temperature_2m_mean"],
                        "rain_mm": data["daily"]["precipitation_sum"],
                    })
                    df_daily["period"] = df_daily["date"].dt.to_period("M")
                    m = df_daily.groupby("period").agg(
                        rain_mm=("rain_mm", "sum"),
                        temp_c=("temp_c", "mean")
                    ).reset_index()
                    m["rain_3m"] = m["rain_mm"].rolling(3, min_periods=3).sum()
                    match = m[m["period"] == as_of]
                    if not match.empty and not pd.isna(match["rain_3m"].iloc[0]):
                        print(f"Successfully fetched live weather from Open-Meteo API for {as_of}.")
                        return m
        except Exception:
            continue

    return None


def load_data(as_of=None):
    """Loads demographics reference CSV, live MySQL incidents, and weather data."""
    # 1. Load Demographics Reference CSV (Option A)
    dem = pd.read_csv(DEMOGRAPHICS_CSV)
    dem = dem[dem["Barangay"].str.upper() != "LIAN"].copy()
    dem = dem.rename(columns={
        "Barangay": "barangay",
        "Total Population": "population",
        "Number of Households": "households",
        "Land Area (Hectares)": "area_ha"
    })
    dem["pop_density"] = dem.population / dem.area_ha
    dem["hh_per_ha"] = dem.households / dem.area_ha
    if len(dem) != 19:
        sys.exit(f"Expected 19 barangays in the demographics file, found {len(dem)}.")

    # 2. Query Live Incidents from MySQL Database
    try:
        conn = pymysql.connect(**DB)
    except Exception as e:
        sys.exit(f"Database connection failed: {e}\nCheck MySQL status and credentials in .env.")

    with conn.cursor() as cur:
        cur.execute("""
            SELECT 
                ir.incident_id,
                b.barangay_id,
                COALESCE(b.barangay_name, 'Unknown') AS barangay_name,
                COALESCE(ir.incident_type, 'others') AS fire_type,
                COALESCE(ir.cause_of_fire, 'Undetermined') AS cause_of_fire,
                COALESCE(ir.alarm_level, '1st_alarm') AS alarm_level,
                COALESCE(ir.estimated_damage, 0) AS estimated_damages_php,
                COALESCE(ir.incident_datetime, cr.created_at) AS date_of_occurrence
            FROM incident_record ir
            LEFT JOIN barangay b ON b.barangay_id = ir.barangay_id
            LEFT JOIN community_report cr ON cr.report_id = ir.report_id
            WHERE (ir.incident_datetime IS NOT NULL OR cr.created_at IS NOT NULL)
        """)
        inc_rows = cur.fetchall()

    conn.close()

    inc = pd.DataFrame(inc_rows)
    if inc.empty:
        sys.exit("No incident records found in MySQL table `incident_record`.")

    # Standardize barangay names for clean feature joining
    inc["barangay"] = (
        inc["barangay_name"]
        .str.replace("(Poblacion)", "(Pob.)", regex=False)
        .str.replace("Puting Kahoy", "Puting-Kahoy", regex=False)
        .str.strip()
    )

    inc["month"] = pd.to_datetime(inc["date_of_occurrence"]).dt.to_period("M")
    inc["cause_group"] = inc.cause_of_fire.map(group_cause)
    inc["is_resid"] = (inc.fire_type == "residential_fire").astype(int)
    inc["alarm_num"] = inc.alarm_level.astype(str).str.extract(r"(\d)")[0].fillna(1.0).astype(float)
    inc["damages"] = inc.estimated_damages_php.astype(float)

    # 3. Weather: Query Open-Meteo API directly, falling back to local weather CSV if offline
    wx = None
    if as_of:
        try:
            wx = fetch_weather_open_meteo(as_of)
        except Exception as e:
            print(f"Notice: Open-Meteo API skipped ({e}). Falling back to local weather data.")

    if wx is None:
        if os.path.exists(WEATHER_CSV):
            print(f"Loading weather from reference file: {os.path.basename(WEATHER_CSV)}")
            raw_wx = pd.read_csv(WEATHER_CSV)[["year", "month", "rain_mm", "temp_c"]]
            raw_wx["period"] = pd.to_datetime(dict(year=raw_wx.year, month=raw_wx.month, day=1)).dt.to_period("M")
            raw_wx = raw_wx.sort_values("period").reset_index(drop=True)
            raw_wx["rain_3m"] = raw_wx.rain_mm.rolling(3, min_periods=3).sum()
            wx = raw_wx.drop(columns=["year", "month"])
        else:
            sys.exit(f"Weather data unavailable: Open-Meteo API was not reachable and {WEATHER_CSV} does not exist.")

    return inc, dem, wx


def build_features(inc, dem, wx, as_of):
    """Rebuilds the exact 16 model features for reference month `as_of`."""
    months = pd.period_range(as_of - 11, as_of, freq="M")
    grid = pd.MultiIndex.from_product([dem.barangay, months], names=["barangay", "month"]).to_frame(index=False)

    sub = inc[inc.month.isin(months)]
    agg = sub.groupby(["barangay", "month"]).agg(
        n_fires=("incident_id", "size"),
        n_resid=("is_resid", "sum"),
        alarm_sum=("alarm_num", "sum"),
        dmg_sum=("damages", "sum")
    ).reset_index()

    for grp, name in [("electrical", "n_elec"), ("cooking_lpg", "n_cook"), ("open_flame_burning", "n_burn")]:
        x = sub[sub.cause_group == grp].groupby(["barangay", "month"]).size().rename(name).reset_index()
        agg = agg.merge(x, how="left", on=["barangay", "month"])

    grid = grid.merge(agg, how="left", on=["barangay", "month"])
    for c in ["n_fires", "n_resid", "alarm_sum", "dmg_sum", "n_elec", "n_cook", "n_burn"]:
        if c not in grid:
            grid[c] = 0
        grid[c] = grid[c].fillna(0)

    cur = grid[grid.month == as_of].set_index("barangay")
    last3 = grid[grid.month >= as_of - 2].groupby("barangay").n_fires.sum()
    last6 = grid[grid.month >= as_of - 5].groupby("barangay").n_fires.sum()
    tot = grid.groupby("barangay")[["n_fires", "n_resid", "n_elec", "n_cook", "n_burn", "alarm_sum", "dmg_sum"]].sum()
    f12 = tot.n_fires.replace(0, np.nan)

    feats = pd.DataFrame(index=tot.index)
    feats["fires_m0"] = cur.n_fires
    feats["fires_last3"] = last3
    feats["fires_last6"] = last6
    feats["fires_last12"] = tot.n_fires
    feats["share_resid"] = (tot.n_resid / f12).fillna(0)
    feats["share_elec"] = (tot.n_elec / f12).fillna(0)
    feats["share_cook"] = (tot.n_cook / f12).fillna(0)
    feats["share_burn"] = (tot.n_burn / f12).fillna(0)
    feats["avg_alarm_12"] = (tot.alarm_sum / f12).fillna(0)
    feats["log_damages_12"] = np.log1p(tot.dmg_sum)

    d = dem.set_index("barangay")
    feats["pop_density"] = d.pop_density
    feats["hh_per_ha"] = d.hh_per_ha

    # Weather extraction with 3-tier fault tolerance:
    # Tier 1: Live Open-Meteo API
    # Tier 2: Specific month in CSV
    # Tier 3: 10-year historical seasonal climate average for this calendar month
    w = wx[wx.period == as_of] if wx is not None else pd.DataFrame()
    if not w.empty and not pd.isna(w.rain_3m.iloc[0]):
        rain_mm = float(w.rain_mm.iloc[0])
        temp_c = float(w.temp_c.iloc[0])
        rain_3m = float(w.rain_3m.iloc[0])
    else:
        cal_month = as_of.month
        month_name = as_of.strftime("%B")
        month_hist = wx[wx.period.dt.month == cal_month] if wx is not None else pd.DataFrame()
        if not month_hist.empty:
            print(f"Notice: Specific weather for {as_of} unavailable. Using Lian 10-year historical seasonal average for {month_name}.")
            rain_mm = float(month_hist.rain_mm.mean())
            temp_c = float(month_hist.temp_c.mean())
            clean_rain3m = month_hist.rain_3m.dropna()
            rain_3m = float(clean_rain3m.mean() if not clean_rain3m.empty else rain_mm * 3)
        else:
            print("Notice: Using overall climatological median for Lian.")
            rain_mm = 145.0
            temp_c = 27.5
            rain_3m = 435.0

    feats["rain_mm"] = rain_mm
    feats["temp_c"] = temp_c
    feats["rain_3m"] = rain_3m
    feats["target_month"] = (as_of.month % 12) + 1

    return feats.reset_index()


def predict(feats):
    """Loads XGBoost model and calculates class probabilities."""
    try:
        model = xgb.XGBClassifier()
        model.load_model(MODEL_PATH)
        cols = model.get_booster().feature_names
        proba = model.predict_proba(feats[cols])
    except Exception:
        # Fallback to native booster if scikit-learn is not installed
        booster = xgb.Booster()
        booster.load_model(MODEL_PATH)
        cols = booster.feature_names
        dmatrix = xgb.DMatrix(feats[cols])
        proba = booster.predict(dmatrix)

    return proba


def main():
    ap = argparse.ArgumentParser(description="Live MySQL Database-driven fire risk prediction.")
    ap.add_argument("--as-of", help="Cutoff reference month, e.g. 2025-11")
    ap.add_argument("--write-db", action="store_true", help="Commit predictions to table `risk_assessment`")
    args = ap.parse_args()

    if args.as_of:
        as_of = pd.Period(args.as_of, "M")
    else:
        # Automated monthly cron: default to the full month that just ended
        as_of = pd.Timestamp.now().to_period("M") - 1

    print("Loading live incident records from MySQL, demographics reference CSV, and weather...")
    inc, dem, wx = load_data(as_of)

    target_month = as_of + 1
    window_start = str(target_month.to_timestamp().date())

    print(f"Reference data cutoff: {as_of} | Target prediction window: {target_month} ({window_start})")
    print(f"Total live incidents loaded from MySQL: {len(inc)}")

    feats = build_features(inc, dem, wx, as_of)
    proba = predict(feats)

    # Convert barangay names to official DB display format
    official_names = (
        feats.barangay
        .str.replace("(Pob.)", "(Poblacion)", regex=False)
        .str.replace("Puting-Kahoy", "Puting Kahoy", regex=False)
    )

    out = pd.DataFrame({
        "barangay": official_names.values,
        "window_start": window_start,
        "p_mild": proba[:, 0].round(4),
        "p_moderate": proba[:, 1].round(4),
        "p_high": proba[:, 2].round(4),
        "prediction_score": (proba[:, 2] + 0.5 * proba[:, 1]).round(4),
        "risk_level": [CLASS_NAMES[i] for i in proba.argmax(axis=1)],
    })

    out.to_csv(OUTPUT_CSV, index=False)
    print("\n" + out[["barangay", "prediction_score", "risk_level"]].to_string(index=False))
    print(f"\nSaved CSV: {OUTPUT_CSV}")

    if not args.write_db:
        print("\n[DRY RUN] No database changes made. Pass --write-db to commit to `risk_assessment`.")
        return

    conn = pymysql.connect(**DB)
    with conn.cursor() as cur:
        # Fetch barangay ID mapping from database
        cur.execute("SELECT barangay_id, barangay_name FROM barangay")
        db_rows = cur.fetchall()
        id_by_name = {}
        for r in db_rows:
            bid = r["barangay_id"]
            name = r["barangay_name"]
            id_by_name[name] = bid
            id_by_name[name.replace("(Poblacion)", "(Pob.)")] = bid
            id_by_name[name.replace("Puting Kahoy", "Puting-Kahoy")] = bid

        missing = set(out.barangay) - set(id_by_name)
        if missing:
            conn.close()
            sys.exit(f"Names not matching `barangay` table: {missing}. Aborting save.")

        # Clear existing predictions for this window date to avoid duplicates
        cur.execute("DELETE FROM risk_assessment WHERE date = %s", (window_start,))

        rows = [
            (id_by_name[r.barangay], r.window_start, float(r.prediction_score), str(r.risk_level))
            for r in out.itertuples()
        ]

        cur.executemany(
            """INSERT INTO risk_assessment (barangay_id, date, prediction_score, risk_level, generated_at)
               VALUES (%s, %s, %s, %s, NOW())""",
            rows
        )
        conn.commit()

    conn.close()
    print(f"\nSuccessfully committed {len(rows)} predictions to table `risk_assessment` for date {window_start}!")


if __name__ == "__main__":
    main()
