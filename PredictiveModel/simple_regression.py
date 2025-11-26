
# Basic Linear Regression

# Goal:
#     Predict total plant influent (target) using a single predictor

# This version automatically (execute with main()):
#  NOTE: MAIN1() is used to run model with each individual feature and saves the metrics into a csv.
#     - Loads OrganizedHighFlowData.csv
#     - Renames long/ugly column names to simpler ones
#     - Runs simple linear regression
#     - Produces scatter, predictions plot, residuals plot

# Steps:
#     1. Load CSV data + rename columns
#     2. Quick data checks
#     3. Select target + one feature
#     4. Train/test split
#     5. Fit LinearRegression model
#     6. Evaluate performance (MAE, RMSE, R^2)
#     7. Plot regression line + residuals


import sys
import pandas as pd
import numpy as np
import matplotlib.pyplot as plt

from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score

CSV_PATH = "OrganizedHighFlowData.csv"
DATE_COL = "Date"
TARGET_COL = "Plant_Influent"

# Choose your single predictor:
# FEATURE_COL = "Flow1_cfs"
# FEATURE_COL = "PRCP_in"
FEATURE_COL = "Vista_Level_ft"

TEST_SIZE = 0.2
RANDOM_STATE = 42
SAVE_PLOTS = True

# List of features you want to test individually
PREDICTORS_TO_TEST = [
    "Flow1_cfs",
    "Flow2_cfs",
    "Flow3_cfs",
    "Vista_Level_ft",
    "Vista_PredMax_Level_ft",
    "PRCP_in",
    "SNOW_in",
    "SNWD_in",
]


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


def load_data(csv_path: str) -> pd.DataFrame:
    """Load CSV + rename columns + parse dates."""
    try:
        df = pd.read_csv(csv_path)
    except FileNotFoundError:
        print(f"[ERROR] Could not find file: {csv_path}")
        sys.exit(1)

    print(f"[INFO] Loaded raw data. Shape: {df.shape}")

    # Rename columns to better names
    df = df.rename(columns=RENAME_MAP)

    # Parse date if present
    if "Date" in df.columns:
        df["Date"] = pd.to_datetime(df["Date"], errors="coerce")

    print("[INFO] Column names after renaming:")
    print(df.columns.tolist())

    return df


def quick_overview(df: pd.DataFrame) -> None:
    """Print basic information about the dataset."""
    print("\n[INFO] Columns in dataset:")
    print(df.columns.tolist())

    print("\n[INFO] DataFrame info:")
    print(df.info())

    print("\n[INFO] Summary statistics:")
    print(df.describe())


def select_feature_and_target(df: pd.DataFrame) -> pd.DataFrame:
    """Select the feature and target columns."""
    if TARGET_COL not in df.columns:
        print(f"[ERROR] Target column '{TARGET_COL}' not found.")
        sys.exit(1)

    if FEATURE_COL not in df.columns:
        print(f"[ERROR] Feature column '{FEATURE_COL}' not found.")
        sys.exit(1)

    data = df[[FEATURE_COL, TARGET_COL]].dropna()

    print(f"\n[INFO] Regression dataset shape: {data.shape}")
    print(data.head())

    return data


def plot_scatter(data: pd.DataFrame) -> None:
    """Scatter plot of feature vs target."""
    plt.figure(figsize=(6, 4))
    plt.scatter(data[FEATURE_COL], data[TARGET_COL])
    plt.xlabel(FEATURE_COL)
    plt.ylabel(TARGET_COL)
    plt.title(f"SCATTER PLOT: {FEATURE_COL} vs {TARGET_COL}")
    plt.tight_layout()

    if SAVE_PLOTS:
        fname = "scatter_feature_vs_target.png"
        plt.savefig(fname, dpi=150)
        print(f"[INFO] Saved scatter plot: {fname}")

    plt.show()


def train_test_split_data(data: pd.DataFrame):
    """Split into train/test sets."""
    X = data[[FEATURE_COL]]
    y = data[TARGET_COL]

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=TEST_SIZE, random_state=RANDOM_STATE
    )

    print(f"\n[INFO] Training size: {X_train.shape[0]}")
    print(f"[INFO] Test size: {X_test.shape[0]}")

    return X_train, X_test, y_train, y_test


def fit_linear_regression(X_train, y_train) -> LinearRegression:
    """Fit the model."""
    model = LinearRegression()
    model.fit(X_train, y_train)

    intercept = model.intercept_
    coef = model.coef_[0]

    print("\n[INFO] Model Fitted:")
    print(f"Intercept: {intercept:.4f}")
    print(f"Slope for {FEATURE_COL}: {coef:.4f}")
    print(
        f"\nMODEL:  {TARGET_COL} = {intercept:.4f} + ({coef:.4f}) * {FEATURE_COL}")

    return model


def evaluate_model(model: LinearRegression, X_test, y_test):
    """Evaluate performance metrics."""
    y_pred = model.predict(X_test)

    mae = mean_absolute_error(y_test, y_pred)
    mse = mean_squared_error(y_test, y_pred)
    rmse = np.sqrt(mse)
    r2 = r2_score(y_test, y_pred)

    print("\n[INFO] Test Performance:")
    # MAE: The typical absolute difference between prediction and true value.
    # "How far off are we on average: LOWER = BETTER"
    # SAME UNITS AS Influent (MDG)
    print(f"MAE:  {mae:.4f}")
    if 2 > mae:
        print("Mean Absolute Error: Great ")
    if 2 < mae < 3:
        print("Mean Absolute Error: Good ")
    if 3 < mae < 5:
        print("Mean Absolute Error: Keep Tweaking Model ")
    if mae > 5:
        print("Mean Absolute Error: BAD")

    # MSE: Average prediction error in the same units as influent flow.
    # PENALIZES BIG ERRORS: LOWER = BETTER
    print(f"MSE:  {mse:.4f}")
    if 10 > mse:
        print("Mean Squared Error: Great ")
    if 10 < mse < 25:
        print("Mean Squared Error: Good ")
    if 25 < mse < 50:
        print("Mean Sqaured Error: Needs Work ")
    if mse > 50:
        print("Mean Sqaured Error: BAD")

    # RMSE: Similar to MSE but gives more weight for big errors
    # LOWER = BETTER (MDG)
    print(f"RMSE: {rmse:.4f}")
    if 2 > rmse:
        print("Root Mean Squared Error: Great ")
    if 2 < rmse < 3:
        print("Root Mean Squared Error: Good ")
    if 3 < rmse < 5:
        print("Root Mean Squared Error: Keep Tweaking Model ")
    if rmse > 5:
        print("Root Mean Squared Error: BAD")

    # R^2 (Coefficient of Determination): Proportion of variation in the influent you can explain.
    print(f"R^2:  {r2:.4f}")
    if 0.0 < r2 < 0.3:
        print("R^2: Weak")
    if 0.3 < r2 < 0.5:
        print("R^2: Moderate Relationship")
    if 0.5 < r2 < 0.7:
        print("R^2: Good for time series")
    if 0.7 < r2 < 0.85:
        print("R^2: Very Good")
    if r2 > 0.85:
        print("R^2: RARE")
    return y_pred


def plot_predictions(X_test, y_test, y_pred) -> None:
    """Plot actual vs predicted."""
    plt.figure(figsize=(6, 4))
    plt.scatter(X_test[FEATURE_COL], y_test, label="Actual")
    plt.scatter(X_test[FEATURE_COL], y_pred, label="Predicted", marker="x")
    plt.xlabel(FEATURE_COL)
    plt.ylabel(TARGET_COL)
    plt.title("Actual vs Predicted")
    plt.legend()
    plt.tight_layout()

    if SAVE_PLOTS:
        fname = "regression_predictions.png"
        plt.savefig(fname, dpi=150)
        print(f"[INFO] Saved predictions plot: {fname}")

    plt.show()


def plot_residuals(y_test, y_pred) -> None:
    """Plot residuals."""
    residuals = y_test - y_pred

    plt.figure(figsize=(6, 4))
    plt.scatter(y_pred, residuals)
    plt.axhline(0, linestyle="--")
    plt.xlabel("Predicted")
    plt.ylabel("Residuals")
    plt.title("Residuals vs Predicted")
    plt.tight_layout()

    if SAVE_PLOTS:
        fname = "residuals_plot.png"
        plt.savefig(fname, dpi=150)
        print(f"[INFO] Saved residuals plot: {fname}")

    plt.show()

    print(f"[INFO] Residual mean: {residuals.mean():.6f}")


def run_single_feature_model(df: pd.DataFrame, feature_col: str) -> dict:
    # Run the full simple linear regression pipeline for a single feature:

    # Make sure the columns exist
    if TARGET_COL not in df.columns:
        print(f"[ERROR] Target column '{TARGET_COL}' not found.")
        return None

    if feature_col not in df.columns:
        print(f"[WARN] Feature column '{feature_col}' not found. Skipping.")
        return None
    # drop NaNs
    data = df[[feature_col, TARGET_COL]].dropna()

    if data.shape[0] < 10:
        print(f"[WARN] Not enough data for feature '{feature_col}'. Skipping.")
        return None

    X = data[[feature_col]]
    y = data[TARGET_COL]

    # split data for test and train
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=TEST_SIZE, random_state=RANDOM_STATE
    )

    # fit model
    model = LinearRegression()
    model.fit(X_train, y_train)

    # compute metrics
    y_pred = model.predict(X_test)

    mae = mean_absolute_error(y_test, y_pred)
    mse = mean_squared_error(y_test, y_pred)
    rmse = np.sqrt(mse)
    r2 = r2_score(y_test, y_pred)

    intercept = model.intercept_
    coef = model.coef_[0]

    result = {
        "feature": feature_col,
        "n_samples": data.shape[0],
        "intercept": intercept,
        "slope": coef,
        "MAE": mae,
        "MSE": mse,
        "RMSE": rmse,
        "R2": r2,
    }

    print(f"\n[RESULT] Feature: {feature_col}")
    print(f"  n = {data.shape[0]}")
    print(f"  Intercept = {intercept:.4f}")
    print(f"  Slope     = {coef:.4f}")
    print(f"  MAE       = {mae:.4f}")
    print(f"  MSE       = {mse:.4f}")
    print(f"  RMSE      = {rmse:.4f}")
    print(f"  R^2       = {r2:.4f}")

    # return dictrionary of results for analysis
    return result


def main1():
    print("=== Basic Linear Regression: Single Feature Comparison ===")

    # 1. Load and clean data (renaming happens inside load_data)
    df = load_data(CSV_PATH)

    # 2. Optional: Print overview once
    quick_overview(df)

    # 3. Loop through each predictor and evaluate
    results = []

    for feature in PREDICTORS_TO_TEST:
        print("\n" + "=" * 60)
        print(f"[INFO] Running model for feature: {feature}")
        print("=" * 60)

        res = run_single_feature_model(df, feature)
        if res is not None:
            results.append(res)

    # 4. Save results to a CSV for later analysis / report
    if results:
        results_df = pd.DataFrame(results)
        results_df = results_df.sort_values(by="R2", ascending=False)

        out_path = "single_feature_model_results.csv"
        results_df.to_csv(out_path, index=False)

        print(f"\n[INFO] Saved results for all features to: {out_path}")
        print("\n[TOP RESULTS]")
        print(results_df.head())
    else:
        print("[WARN] No successful models were run.")


def main():
    print(f"=== Basic Linear Regression {TARGET_COL} vs {FEATURE_COL}===")

    df = load_data(CSV_PATH)
    quick_overview(df)

    data = select_feature_and_target(df)

    plot_scatter(data)

    X_train, X_test, y_train, y_test = train_test_split_data(data)

    model = fit_linear_regression(X_train, y_train)

    y_pred = evaluate_model(model, X_test, y_test)

    plot_predictions(X_test, y_test, y_pred)
    plot_residuals(y_test, y_pred)


if __name__ == "__main__":
    main()
