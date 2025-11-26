import sys
import pandas as pd
import joblib

from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression

# Configuration
CSV_PATH = "OrganizedHighFlowData.csv"
TARGET_COL = "Plant_Influent"
FEATURE_COL = "Vista_Level_ft"
TEST_SIZE = 0.2
RANDOM_STATE = 42
MODEL_OUTPUT_PATH = "plant_influent_model.joblib"

# Column name mapping
RENAME_MAP = {
    "Unnamed: 0": "Date",
    "Plant Influent\n[Plant Influent Total Flow]": "Plant_Influent",
    "Vista Gauge Level (feet)": "Vista_Level_ft",
    "Vista Gauge Elevation (feet)": "Vista_Elev_ft",
    "Vista Gauge Predicted Max Level (feet)": "Vista_PredMax_Level_ft",
    "Vista Gauge Predicted Max Elevation (feet)": "Vista_PredMax_Elev_ft",
    "Flow1 (cfs)": "Flow1_cfs",
    "Flow2 (cfs)": "Flow2_cfs",
    "Flow3 (cfs)": "Flow3_cfs",
    "PRCP (Inches)": "PRCP_in",
    "SNOW (Inches)": "SNOW_in",
    "SNWD (Inches)": "SNWD_in",
}


def main():
    # Load data
    try:
        df = pd.read_csv(CSV_PATH)
    except FileNotFoundError:
        print(f"ERROR: Could not find file: {CSV_PATH}")
        sys.exit(1)

    df = df.rename(columns=RENAME_MAP)

    # Prepare data
    data = df[[FEATURE_COL, TARGET_COL]].dropna()
    X = data[[FEATURE_COL]]
    y = data[TARGET_COL]

    # Split data
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=TEST_SIZE, random_state=RANDOM_STATE
    )

    # Train model
    model = LinearRegression()
    model.fit(X_train, y_train)

    # Package model
    model_package = {
        'model': model,
        'feature_name': FEATURE_COL,
        'target_name': TARGET_COL,
        'intercept': model.intercept_,
        'coefficient': model.coef_[0]
    }

    # Save model
    joblib.dump(model_package, MODEL_OUTPUT_PATH)
    print(f"Model saved to: {MODEL_OUTPUT_PATH}")


if __name__ == "__main__":
    main()
