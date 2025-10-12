import pandas as pd
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score, classification_report
import joblib
import re

class MusanzeMLModel:
    def __init__(self):
        self.vectorizer = TfidfVectorizer(max_features=1000, stop_words='english')
        self.model = RandomForestClassifier(n_estimators=100, random_state=42)
        self.is_trained = False
        
    def preprocess_text(self, text):
        """Clean and preprocess text"""
        text = str(text).lower()
        text = re.sub(r'[^a-zA-Z\s]', '', text)
        return text
    
    def train(self, csv_path):
        """Train the model with Musanze dataset"""
        try:
            # Load dataset
            df = pd.read_csv(csv_path)
            
            # Create unique responses mapping
            unique_responses = df['response'].unique()
            response_map = {resp: i for i, resp in enumerate(unique_responses)}
            df['response_id'] = df['response'].map(response_map)
            
            # Preprocess text data - combine all relevant features
            df['processed_input'] = (df['business_type'] + ' ' + 
                                   df['location'] + ' ' + 
                                   df['skills_required'] + ' ' + 
                                   df['target_market'] + ' ' + 
                                   df['investment_range'])
            df['processed_input'] = df['processed_input'].apply(self.preprocess_text)
            
            # Prepare features and target
            X = self.vectorizer.fit_transform(df['processed_input'])
            y = df['response_id']
            
            # Split data
            X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.1, random_state=42)
            
            # Train model with better parameters
            self.model = RandomForestClassifier(n_estimators=200, max_depth=20, random_state=42)
            self.model.fit(X_train, y_train)
            
            # Evaluate
            y_pred = self.model.predict(X_test)
            accuracy = accuracy_score(y_test, y_pred)
            
            self.is_trained = True
            self.response_map = response_map
            self.id_to_response = {v: k for k, v in response_map.items()}
            
            # Save model
            joblib.dump(self.model, 'ml_models/musanze_model.pkl')
            joblib.dump(self.vectorizer, 'ml_models/musanze_vectorizer.pkl')
            joblib.dump(self.id_to_response, 'ml_models/musanze_responses.pkl')
            
            return accuracy
            
        except Exception as e:
            print(f"Training error: {e}")
            return 0.0
    
    def predict(self, user_input):
        """Predict response for user input"""
        if not self.is_trained:
            return "Model not trained yet. Please train the model first."
        
        try:
            # Preprocess input
            processed_input = self.preprocess_text(user_input)
            
            # Vectorize
            X = self.vectorizer.transform([processed_input])
            
            # Predict
            prediction_id = self.model.predict(X)[0]
            prediction = self.id_to_response[prediction_id]
            
            return prediction
            
        except Exception as e:
            return f"Prediction error: {e}"

# Train the model
if __name__ == "__main__":
    model = MusanzeMLModel()
    accuracy = model.train('datasets/musanze_dataset.csv')
    print(f"Model trained with accuracy: {accuracy:.3f}")
