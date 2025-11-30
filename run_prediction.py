import sys
import os
import joblib
import pandas as pd

def main():
    MODEL_PATH = os.getenv("MODEL_PATH")
    model_package = joblib.load(MODEL_PATH)
    model = model_package["model"]
    features = model_package["features"]

    input_features = sys.argv[1:]
    if len(input_features) != len(features):
        print(f"Error: Expected {len(features)} features, got {len(input_features)}")
        return

    input_floats = list(map(float, input_features))

    X = pd.DataFrame([input_floats], columns=features)

    prediction = model.predict(X)[0]

    print(prediction)

if __name__ == "__main__":
    main()
