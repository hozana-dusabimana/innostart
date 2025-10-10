#!/usr/bin/env python3
"""
InnoStart ML Model Training Script
Trains a model to provide tailored business responses based on user input
"""

import os
import json
import pandas as pd
import numpy as np
import pickle
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.linear_model import LogisticRegression
from sklearn.svm import SVC
from sklearn.metrics import classification_report, accuracy_score
from sklearn.pipeline import Pipeline
import re
import logging
from typing import Dict, List, Tuple
import warnings
warnings.filterwarnings('ignore')

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class BusinessResponseTrainer:
    """Trainer class for business response model"""
    
    def __init__(self):
        self.vectorizer = TfidfVectorizer(
            max_features=5000,
            stop_words='english',
            ngram_range=(1, 2),
            min_df=2,
            max_df=0.95
        )
        self.model = None
        self.label_encoder = None
        self.response_categories = {}
        
    def create_training_data(self) -> pd.DataFrame:
        """Create comprehensive training data for business responses"""
        
        training_data = []
        
        # Business Planning Questions
        business_planning_data = [
            ("How do I write a business plan?", "business_planning", 
             "A business plan should include: executive summary, company description, market analysis, organization structure, service/product line, marketing strategy, and financial projections. Start with your executive summary and work through each section systematically."),
            
            ("What should be in my executive summary?", "business_planning",
             "Your executive summary should include: business concept, financial features, financial requirements, current business position, major achievements, and a brief description of your team. Keep it concise but compelling - this is often the first thing investors read."),
            
            ("How do I analyze my target market?", "business_planning",
             "Market analysis involves: defining your target customers, analyzing market size and trends, studying competitors, identifying market gaps, and understanding customer needs. Use surveys, interviews, and industry reports to gather data."),
            
            ("What's the difference between mission and vision?", "business_planning",
             "Mission statement describes what your company does and why it exists. Vision statement describes where you want to be in the future. Mission is about the present, vision is about the future. Both should be clear, inspiring, and aligned with your values."),
        ]
        
        # Funding Questions
        funding_data = [
            ("How can I get funding for my startup?", "funding",
             "Funding options include: bootstrapping (self-funding), angel investors, venture capital, bank loans, crowdfunding, government grants, and business incubators. Choose based on your stage, amount needed, and growth plans."),
            
            ("What do investors look for?", "funding",
             "Investors look for: strong business model, large market opportunity, experienced team, competitive advantage, clear financial projections, and evidence of traction. Prepare a compelling pitch deck and be ready to answer tough questions."),
            
            ("How much equity should I give investors?", "funding",
             "Equity depends on valuation, funding amount, and negotiation. Generally, seed rounds take 10-25% equity, Series A takes 15-30%. Consider future funding rounds and maintain enough equity for you and your team."),
            
            ("What's the difference between debt and equity financing?", "funding",
             "Debt financing means borrowing money you must repay with interest. Equity financing means selling ownership shares. Debt doesn't dilute ownership but requires repayment. Equity provides capital without repayment but dilutes ownership."),
        ]
        
        # Marketing Questions
        marketing_data = [
            ("How do I market my startup?", "marketing",
             "Start with: defining your target audience, creating a strong brand identity, developing a content strategy, using social media effectively, building partnerships, and measuring results. Focus on channels where your customers are most active."),
            
            ("What's the best social media strategy?", "marketing",
             "Choose platforms where your audience is active. Create valuable content consistently, engage with followers, use hashtags strategically, collaborate with influencers, and track metrics. Quality over quantity - better to excel on 2-3 platforms than be mediocre on all."),
            
            ("How do I build brand awareness?", "marketing",
             "Build brand awareness through: consistent messaging across all channels, content marketing, public relations, partnerships, events, and word-of-mouth. Focus on providing value and building relationships rather than just selling."),
            
            ("What's content marketing?", "marketing",
             "Content marketing is creating and sharing valuable content to attract and engage your target audience. It includes blogs, videos, podcasts, infographics, and social media posts. The goal is to build trust and establish expertise."),
        ]
        
        # Legal Questions
        legal_data = [
            ("What legal structure should I choose?", "legal",
             "Common structures: Sole proprietorship (simple but unlimited liability), LLC (flexible with limited liability), Corporation (complex but strong liability protection), S-Corp (tax benefits). Choose based on liability, taxes, and growth plans."),
            
            ("Do I need business insurance?", "legal",
             "Yes, consider: general liability insurance, professional liability, product liability, cyber liability, and workers' compensation. Insurance protects against unexpected costs and is often required by clients or partners."),
            
            ("How do I protect my intellectual property?", "legal",
             "Protect IP through: patents (inventions), trademarks (brand names/logos), copyrights (creative works), and trade secrets (confidential information). File applications early and use NDAs when sharing sensitive information."),
            
            ("What contracts do I need?", "legal",
             "Essential contracts: customer agreements, vendor contracts, employment agreements, partnership agreements, NDAs, and terms of service. Have a lawyer review important contracts to protect your interests."),
        ]
        
        # Operations Questions
        operations_data = [
            ("How do I hire my first employee?", "operations",
             "Steps: define the role clearly, write a job description, post on relevant platforms, screen candidates, conduct interviews, check references, make an offer, and onboard properly. Consider culture fit and growth potential."),
            
            ("How do I manage cash flow?", "operations",
             "Monitor cash flow by: tracking income and expenses, forecasting future cash needs, maintaining cash reserves, invoicing promptly, managing receivables, and controlling costs. Use accounting software to stay organized."),
            
            ("What technology do I need?", "operations",
             "Essential tech: accounting software, CRM system, project management tools, communication platforms, website, and security software. Start with basics and add complexity as you grow. Cloud-based solutions are often most cost-effective."),
            
            ("How do I scale my business?", "operations",
             "Scaling requires: systematizing processes, building a strong team, automating tasks, expanding market reach, diversifying revenue streams, and maintaining quality. Focus on sustainable growth rather than rapid expansion."),
        ]
        
        # Financial Questions
        financial_data = [
            ("How do I price my product?", "financial",
             "Pricing strategies: cost-plus (cost + margin), value-based (what customers will pay), competitive (match competitors), and penetration (low price to gain market share). Consider your costs, market position, and customer value perception."),
            
            ("What financial metrics should I track?", "financial",
             "Key metrics: revenue growth, gross margin, net profit margin, customer acquisition cost, lifetime value, burn rate, and cash runway. Track these monthly and compare to industry benchmarks."),
            
            ("How do I create financial projections?", "financial",
             "Create projections by: estimating revenue based on sales forecasts, calculating costs (fixed and variable), projecting cash flow, and creating scenarios (optimistic, realistic, pessimistic). Update monthly with actual results."),
            
            ("What's the difference between profit and cash flow?", "financial",
             "Profit is revenue minus expenses on paper. Cash flow is actual money coming in and going out. You can be profitable but have negative cash flow due to timing differences in payments and expenses."),
        ]
        
        # Combine all data
        all_data = (business_planning_data + funding_data + marketing_data + 
                   legal_data + operations_data + financial_data)
        
        for question, category, response in all_data:
            training_data.append({
                'question': question,
                'category': category,
                'response': response,
                'keywords': self._extract_keywords(question)
            })
        
        # Add more variations and synonyms
        variations = self._create_question_variations()
        training_data.extend(variations)
        
        return pd.DataFrame(training_data)
    
    def _extract_keywords(self, text: str) -> List[str]:
        """Extract keywords from text"""
        # Simple keyword extraction
        keywords = re.findall(r'\b\w+\b', text.lower())
        # Remove common words
        stop_words = {'how', 'do', 'i', 'what', 'is', 'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'}
        keywords = [word for word in keywords if word not in stop_words and len(word) > 2]
        return keywords
    
    def _create_question_variations(self) -> List[Dict]:
        """Create variations of questions for better training"""
        variations = []
        
        # Question patterns
        patterns = [
            ("How can I {action}?", "business_planning"),
            ("What should I know about {topic}?", "business_planning"),
            ("How do I {action} my business?", "operations"),
            ("What are the best {topic} strategies?", "marketing"),
            ("How much {financial_aspect} do I need?", "financial"),
            ("What {legal_aspect} should I consider?", "legal"),
            ("How do I {action} for {purpose}?", "funding"),
        ]
        
        # Business actions and topics
        actions = ["start", "grow", "scale", "manage", "improve", "optimize"]
        topics = ["marketing", "financing", "operations", "legal", "strategy"]
        business_types = ["startup", "business", "company", "venture"]
        financial_aspects = ["funding", "capital", "investment", "money"]
        legal_aspects = ["legal requirements", "compliance", "regulations"]
        purposes = ["funding", "growth", "success", "expansion"]
        
        for pattern, category in patterns:
            if "{action}" in pattern and "{purpose}" not in pattern:
                for action in actions:
                    question = pattern.format(action=action)
                    variations.append({
                        'question': question,
                        'category': category,
                        'response': f"To {action} your business effectively, focus on understanding your market, building strong systems, and maintaining consistent execution. Start with clear goals and measure progress regularly.",
                        'keywords': self._extract_keywords(question)
                    })
            elif "{topic}" in pattern:
                for topic in topics:
                    question = pattern.format(topic=topic)
                    variations.append({
                        'question': question,
                        'category': category,
                        'response': f"Key aspects of {topic} include understanding your target audience, developing clear strategies, measuring results, and adapting based on feedback. Focus on building sustainable systems.",
                        'keywords': self._extract_keywords(question)
                    })
            elif "{financial_aspect}" in pattern:
                for aspect in ['funding', 'capital', 'investment', 'money']:
                    question = pattern.format(financial_aspect=aspect)
                    variations.append({
                        'question': question,
                        'category': category,
                        'response': f"For {aspect}, consider your business stage, growth plans, and risk tolerance. Research different options and prepare a solid business case.",
                        'keywords': self._extract_keywords(question)
                    })
            elif "{legal_aspect}" in pattern:
                for aspect in ['legal requirements', 'compliance', 'regulations']:
                    question = pattern.format(legal_aspect=aspect)
                    variations.append({
                        'question': question,
                        'category': category,
                        'response': f"For {aspect}, consult with legal professionals, research industry regulations, and ensure proper documentation and compliance.",
                        'keywords': self._extract_keywords(question)
                    })
            elif "{action}" in pattern and "{purpose}" in pattern:
                for action in actions:
                    for purpose in ['funding', 'growth', 'success', 'expansion']:
                        question = pattern.format(action=action, purpose=purpose)
                        variations.append({
                            'question': question,
                            'category': category,
                            'response': f"To {action} for {purpose}, develop a clear strategy, build necessary resources, and execute consistently. Focus on measurable outcomes and continuous improvement.",
                            'keywords': self._extract_keywords(question)
                        })
        
        return variations
    
    def preprocess_data(self, df: pd.DataFrame) -> Tuple[np.ndarray, np.ndarray]:
        """Preprocess training data"""
        logger.info("Preprocessing training data...")
        
        # Combine question and keywords for better feature representation
        df['combined_text'] = df['question'] + ' ' + df['keywords'].apply(lambda x: ' '.join(x))
        
        # Vectorize text
        X = self.vectorizer.fit_transform(df['combined_text'])
        
        # Encode categories
        from sklearn.preprocessing import LabelEncoder
        self.label_encoder = LabelEncoder()
        y = self.label_encoder.fit_transform(df['category'])
        
        # Store category mapping
        self.response_categories = dict(zip(
            self.label_encoder.classes_, 
            range(len(self.label_encoder.classes_))
        ))
        
        logger.info(f"Features shape: {X.shape}")
        logger.info(f"Categories: {list(self.response_categories.keys())}")
        
        return X, y
    
    def train_model(self, X: np.ndarray, y: np.ndarray) -> None:
        """Train the response classification model"""
        logger.info("Training model...")
        
        # Split data
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=0.2, random_state=42, stratify=y
        )
        
        # Try multiple models
        models = {
            'Random Forest': RandomForestClassifier(n_estimators=100, random_state=42),
            'Logistic Regression': LogisticRegression(random_state=42, max_iter=1000),
            'SVM': SVC(random_state=42, probability=True)
        }
        
        best_model = None
        best_score = 0
        
        for name, model in models.items():
            logger.info(f"Training {name}...")
            model.fit(X_train, y_train)
            score = model.score(X_test, y_test)
            logger.info(f"{name} accuracy: {score:.3f}")
            
            if score > best_score:
                best_score = score
                best_model = model
        
        self.model = best_model
        logger.info(f"Best model accuracy: {best_score:.3f}")
        
        # Generate classification report
        y_pred = self.model.predict(X_test)
        logger.info("\nClassification Report:")
        logger.info(classification_report(y_test, y_pred, 
                                        target_names=self.label_encoder.classes_))
    
    def save_model(self, model_path: str = "ml_models/business_response_model.pkl") -> None:
        """Save the trained model and components"""
        logger.info(f"Saving model to {model_path}")
        
        model_data = {
            'model': self.model,
            'vectorizer': self.vectorizer,
            'label_encoder': self.label_encoder,
            'response_categories': self.response_categories
        }
        
        with open(model_path, 'wb') as f:
            pickle.dump(model_data, f)
        
        # Also save as JSON for easy access
        json_data = {
            'response_categories': self.response_categories,
            'model_info': {
                'type': type(self.model).__name__,
                'features': self.vectorizer.get_feature_names_out().tolist()[:100]  # First 100 features
            }
        }
        
        with open(model_path.replace('.pkl', '.json'), 'w') as f:
            json.dump(json_data, f, indent=2)
        
        logger.info("Model saved successfully!")
    
    def load_model(self, model_path: str = "ml_models/business_response_model.pkl") -> None:
        """Load a trained model"""
        logger.info(f"Loading model from {model_path}")
        
        with open(model_path, 'rb') as f:
            model_data = pickle.load(f)
        
        self.model = model_data['model']
        self.vectorizer = model_data['vectorizer']
        self.label_encoder = model_data['label_encoder']
        self.response_categories = model_data['response_categories']
        
        logger.info("Model loaded successfully!")
    
    def predict_category(self, question: str) -> Tuple[str, float]:
        """Predict the category for a given question"""
        if self.model is None:
            raise ValueError("Model not trained or loaded")
        
        # Preprocess question
        keywords = self._extract_keywords(question)
        combined_text = question + ' ' + ' '.join(keywords)
        
        # Vectorize
        X = self.vectorizer.transform([combined_text])
        
        # Predict
        prediction = self.model.predict(X)[0]
        probability = self.model.predict_proba(X)[0].max()
        
        category = self.label_encoder.inverse_transform([prediction])[0]
        
        return category, probability

def main():
    """Main training function"""
    logger.info("Starting InnoStart ML Model Training")
    
    # Create trainer
    trainer = BusinessResponseTrainer()
    
    # Create training data
    logger.info("Creating training data...")
    training_df = trainer.create_training_data()
    logger.info(f"Created {len(training_df)} training examples")
    
    # Save training data for inspection
    training_df.to_csv('training_data/business_training_data.csv', index=False)
    logger.info("Training data saved to training_data/business_training_data.csv")
    
    # Preprocess data
    X, y = trainer.preprocess_data(training_df)
    
    # Train model
    trainer.train_model(X, y)
    
    # Save model
    trainer.save_model()
    
    # Test the model
    logger.info("\nTesting the model...")
    test_questions = [
        "How do I write a business plan?",
        "What funding options are available?",
        "How can I market my startup?",
        "What legal structure should I choose?",
        "How do I manage cash flow?"
    ]
    
    for question in test_questions:
        category, confidence = trainer.predict_category(question)
        logger.info(f"Q: {question}")
        logger.info(f"A: Category: {category}, Confidence: {confidence:.3f}")
        logger.info("---")
    
    logger.info("Training completed successfully!")

if __name__ == "__main__":
    main()
