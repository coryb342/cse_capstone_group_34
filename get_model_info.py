import sys
import joblib
import pandas as pd
import json
import os


def main():
    MODEL_PATH = os.getenv("MODEL_PATH")
    package = joblib.load(MODEL_PATH)
    model = package["model"]
    features = package["features"]
    target = package["target"]

    model_info = {
        "features": features,
        "target": target,
        "model_type": type(model).__name__
    }

    # Print model information
    print(json.dumps(model_info))

if __name__ == "__main__":
    main()
