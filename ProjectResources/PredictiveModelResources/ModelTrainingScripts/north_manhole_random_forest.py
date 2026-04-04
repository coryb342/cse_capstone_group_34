import sys
import pandas as pd
import joblib

from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression
from sklearn.ensemble import RandomForestRegressor

# --- CONFIG ---
CSV_PATH = "north_manhole_training_data.csv"
TARGET_COL = "North_Manhole_Level_ft"
MODEL_OUTPUT_PATH = "north_manhole_random_forest.joblib"
DATE_COL = "Date"

TEST_SIZE = 0.2
RANDOM_STATE = 42

RENAME_MAP = {
    "Date": "Date",
    "North Influent Flow": "North_Influent",
    "Vista Gauge Level (feet)": "Vista_Level_ft",
    "Vista Gauge Elevation (feet)": "Vista_Elev_ft",
    "Vista Gauge Predicted Max Level (feet)": "Vista_PredMax_Level_ft",
    "Vista Gauge Predicted Max Elevation (feet)": "Vista_PredMax_Elev_ft",
    "North Manhole Level (feet)": "North_Manhole_Level_ft",
    "Flow1 (cfs)": "Flow1_cfs",
    "Flow2 (cfs)": "Flow2_cfs",
    "Flow3 (cfs)": "Flow3_cfs",
    "PRCP (Inches)": "PRCP_in",
    "SNOW (Inches)": "SNOW_in",
    "SNWD (Inches)": "SNWD_in",
}


def main():
    try:
        df = pd.read_csv(CSV_PATH)
    except FileNotFoundError:
        print(f"ERROR: Could not find file: {CSV_PATH}")
        sys.exit(1)

    df = df.rename(columns=RENAME_MAP)

    df = df.dropna()

    if DATE_COL in df.columns:
        df = df.drop(columns=[DATE_COL])

    y = df[TARGET_COL]
    X = df.drop(columns=[TARGET_COL])

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=TEST_SIZE, random_state=RANDOM_STATE
    )

    model = RandomForestRegressor(random_state=RANDOM_STATE)
    model.fit(X_train, y_train)

    model_package = {
        "model": model,
        "features": list(X.columns),  # list of feature names
        "target": TARGET_COL
    }

    joblib.dump(model_package, MODEL_OUTPUT_PATH)
    print(f"Model saved to: {MODEL_OUTPUT_PATH}")


if __name__ == "__main__":
    main()
