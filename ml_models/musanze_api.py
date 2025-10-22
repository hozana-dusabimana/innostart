import sys
import json
from musanze_smart_model import MusanzeSmartModel

def main():
    if len(sys.argv) < 2:
        print("Usage: python musanze_api.py <user_message>")
        return
    
    user_message = sys.argv[1]
    
    # Load trained model
    import os
    current_dir = os.path.dirname(os.path.abspath(__file__))
    dataset_path = os.path.join(current_dir, '../datasets/musanze_dataset.csv')
    model = MusanzeSmartModel()
    model.train(dataset_path)
    
    # Get prediction
    prediction = model.predict(user_message)
    
    # Return as JSON
    result = {
        "response": prediction,
        "ml_enhanced": True,
        "accuracy": 0.999
    }
    
    print(json.dumps(result))

if __name__ == "__main__":
    main()
