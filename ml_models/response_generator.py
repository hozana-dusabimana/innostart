#!/usr/bin/env python3
"""
InnoStart Response Generator
Generates tailored business responses based on user input using trained ML model
"""

import os
import json
import pickle
import logging
from typing import Dict, List, Tuple, Optional
import re
import random
from datetime import datetime

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class BusinessResponseGenerator:
    """Generates tailored business responses based on user input"""
    
    def __init__(self, model_path: str = "ml_models/business_response_model.pkl"):
        self.model = None
        self.vectorizer = None
        self.label_encoder = None
        self.response_categories = {}
        self.model_path = model_path
        self.response_templates = {}
        
        # Load model if it exists
        if os.path.exists(model_path):
            self.load_model()
            self._load_response_templates()
        else:
            logger.warning(f"Model not found at {model_path}. Please train the model first.")
    
    def load_model(self) -> None:
        """Load the trained model"""
        try:
            with open(self.model_path, 'rb') as f:
                model_data = pickle.load(f)
            
            self.model = model_data['model']
            self.vectorizer = model_data['vectorizer']
            self.label_encoder = model_data['label_encoder']
            self.response_categories = model_data['response_categories']
            
            logger.info("Model loaded successfully!")
        except Exception as e:
            logger.error(f"Error loading model: {e}")
    
    def _load_response_templates(self) -> None:
        """Load response templates for each category"""
        self.response_templates = {
            'business_planning': {
                'greeting': [
                    "Great question about business planning!",
                    "Business planning is crucial for success. Here's what you need to know:",
                    "Let me help you with your business planning needs:"
                ],
                'responses': [
                    "A solid business plan is your roadmap to success. It should clearly outline your business concept, target market, competitive advantage, and financial projections. Start with your executive summary and work through each section systematically.",
                    "Your business plan should include: executive summary, company description, market analysis, organization structure, service/product line, marketing strategy, and financial projections. Each section builds on the previous one.",
                    "Market analysis is crucial - define your target customers, analyze market size and trends, study competitors, and identify market gaps. Use surveys, interviews, and industry reports to gather data.",
                    "Remember to update your business plan regularly as your business evolves and market conditions change. It's a living document that should guide your decisions."
                ],
                'tips': [
                    "Keep your business plan concise but comprehensive - aim for 20-30 pages.",
                    "Use data and research to support your claims - investors want evidence.",
                    "Include realistic financial projections with clear assumptions.",
                    "Get feedback from mentors, advisors, or other entrepreneurs."
                ]
            },
            'funding': {
                'greeting': [
                    "Funding is a critical aspect of business growth!",
                    "Let me help you understand your funding options:",
                    "Great question about financing your business:"
                ],
                'responses': [
                    "There are several funding options available: bootstrapping, angel investors, venture capital, bank loans, crowdfunding, and government grants.",
                    "Choose your funding source based on your stage, amount needed, and growth plans. Each has different requirements and benefits.",
                    "Before seeking funding, ensure you have a solid business plan, clear financial projections, and evidence of market demand.",
                    "Consider the trade-offs: equity financing dilutes ownership but provides capital without repayment obligations."
                ],
                'tips': [
                    "Prepare a compelling pitch deck.",
                    "Know your numbers inside and out.",
                    "Build relationships with potential investors.",
                    "Consider multiple funding sources."
                ]
            },
            'marketing': {
                'greeting': [
                    "Marketing is essential for business growth!",
                    "Let me share some effective marketing strategies:",
                    "Great question about marketing your business:"
                ],
                'responses': [
                    "Start by clearly defining your target audience and understanding their needs, preferences, and behaviors.",
                    "Develop a strong brand identity that resonates with your target market and differentiates you from competitors.",
                    "Use multiple marketing channels: social media, content marketing, email campaigns, partnerships, and local advertising.",
                    "Focus on providing value to your customers rather than just selling. Build relationships and trust."
                ],
                'tips': [
                    "Consistency is key in marketing.",
                    "Measure and track your marketing results.",
                    "Adapt your strategy based on what works.",
                    "Focus on channels where your audience is most active."
                ]
            },
            'legal': {
                'greeting': [
                    "Legal considerations are important for business success!",
                    "Let me help you understand the legal aspects:",
                    "Great question about business legal requirements:"
                ],
                'responses': [
                    "Choose the right business structure based on liability protection, tax implications, and growth plans.",
                    "Protect your intellectual property through patents, trademarks, copyrights, and trade secrets.",
                    "Ensure you have proper contracts, insurance, and compliance with regulations in your industry.",
                    "Consider consulting with a business attorney for complex legal matters."
                ],
                'tips': [
                    "Don't skip legal requirements.",
                    "Protect your intellectual property early.",
                    "Keep contracts and agreements in writing.",
                    "Stay updated on regulatory changes."
                ]
            },
            'operations': {
                'greeting': [
                    "Operations are the backbone of your business!",
                    "Let me help you optimize your business operations:",
                    "Great question about business operations:"
                ],
                'responses': [
                    "Focus on building efficient systems and processes that can scale with your business growth.",
                    "Hire the right people who share your vision and bring complementary skills to your team.",
                    "Use technology to automate routine tasks and improve efficiency.",
                    "Monitor key performance indicators and continuously improve your operations."
                ],
                'tips': [
                    "Document your processes.",
                    "Invest in good people and systems.",
                    "Use technology to your advantage.",
                    "Focus on customer satisfaction."
                ]
            },
            'financial': {
                'greeting': [
                    "Financial management is crucial for business success!",
                    "Let me help you with financial planning:",
                    "Great question about business finances:"
                ],
                'responses': [
                    "Track your key financial metrics: revenue, expenses, profit margins, cash flow, and customer acquisition costs.",
                    "Create realistic financial projections and update them regularly based on actual performance.",
                    "Maintain healthy cash flow by managing receivables, controlling expenses, and planning for seasonal variations.",
                    "Consider working with a financial advisor or accountant for complex financial decisions."
                ],
                'tips': [
                    "Keep detailed financial records.",
                    "Monitor cash flow regularly.",
                    "Plan for different scenarios.",
                    "Invest in good accounting software."
                ]
            }
        }
    
    def _extract_keywords(self, text: str) -> List[str]:
        """Extract keywords from text"""
        keywords = re.findall(r'\b\w+\b', text.lower())
        stop_words = {'how', 'do', 'i', 'what', 'is', 'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'my', 'me', 'we', 'you', 'your'}
        keywords = [word for word in keywords if word not in stop_words and len(word) > 2]
        return keywords
    
    def predict_category(self, question: str) -> Tuple[str, float]:
        """Predict the category for a given question"""
        if self.model is None:
            # Fallback to rule-based classification
            return self._fallback_classification(question)
        
        try:
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
        except Exception as e:
            logger.error(f"Error in prediction: {e}")
            return self._fallback_classification(question)
    
    def _fallback_classification(self, question: str) -> Tuple[str, float]:
        """Fallback classification using keyword matching"""
        question_lower = question.lower()
        
        # Keyword-based classification
        category_keywords = {
            'business_planning': ['business plan', 'planning', 'strategy', 'executive summary', 'mission', 'vision'],
            'funding': ['funding', 'investment', 'investor', 'capital', 'money', 'loan', 'grant', 'equity'],
            'marketing': ['marketing', 'advertising', 'promotion', 'brand', 'social media', 'customer', 'sales'],
            'legal': ['legal', 'law', 'contract', 'liability', 'insurance', 'patent', 'trademark', 'compliance'],
            'operations': ['operation', 'process', 'system', 'hiring', 'employee', 'management', 'efficiency'],
            'financial': ['financial', 'finance', 'budget', 'cash flow', 'profit', 'revenue', 'expense', 'pricing']
        }
        
        scores = {}
        for category, keywords in category_keywords.items():
            score = sum(1 for keyword in keywords if keyword in question_lower)
            scores[category] = score
        
        if scores:
            best_category = max(scores, key=scores.get)
            confidence = min(0.8, scores[best_category] * 0.2)  # Cap at 0.8 for fallback
            return best_category, confidence
        else:
            return 'business_planning', 0.5  # Default category
    
    def generate_response(self, question: str, context: Optional[Dict] = None) -> Dict:
        """Generate a tailored response for the given question"""
        if not question.strip():
            return {
                'response': "I'd be happy to help you with your business questions. Please ask me anything about business planning, funding, marketing, legal matters, operations, or finances!",
                'category': 'general',
                'confidence': 0.0,
                'timestamp': datetime.now().isoformat()
            }
        
        # Predict category
        category, confidence = self.predict_category(question)
        
        # Generate response
        if category in self.response_templates:
            templates = self.response_templates[category]
            
            # Select random greeting and response
            greeting = random.choice(templates['greeting'])
            main_response = random.choice(templates['responses'])
            tip = random.choice(templates['tips'])
            
            # Combine into full response
            full_response = f"{greeting}\n\n{main_response}\n\nPro Tip: {tip}"
            
            # Add context-specific information if available
            if context:
                context_info = self._add_context_info(category, context)
                if context_info:
                    full_response += f"\n\n{context_info}"
        else:
            # Generic response for unknown categories
            full_response = self._generate_generic_response(question)
        
        return {
            'response': full_response,
            'category': category,
            'confidence': confidence,
            'timestamp': datetime.now().isoformat(),
            'keywords': self._extract_keywords(question)
        }
    
    def _add_context_info(self, category: str, context: Dict) -> str:
        """Add context-specific information to the response"""
        context_info = ""
        
        if category == 'funding' and 'budget' in context:
            budget = context['budget']
            if budget < 10000:
                context_info = "Given your budget range, consider bootstrapping or small business loans as primary funding sources."
            elif budget < 100000:
                context_info = "With your budget range, you might consider angel investors or crowdfunding platforms."
            else:
                context_info = "Your budget range suggests you could pursue venture capital or larger investment rounds."
        
        elif category == 'marketing' and 'business_type' in context:
            business_type = context['business_type']
            if business_type in ['technology', 'software']:
                context_info = "For tech businesses, focus on digital marketing, content marketing, and developer communities."
            elif business_type in ['retail', 'ecommerce']:
                context_info = "For retail businesses, consider social media marketing, influencer partnerships, and local advertising."
        
        return context_info
    
    def _generate_generic_response(self, question: str) -> str:
        """Generate a generic response for unknown categories"""
        generic_responses = [
            "That's a great question! Starting a business involves many considerations. I'd recommend focusing on understanding your market, building a strong foundation, and seeking advice from experienced entrepreneurs.",
            "I'd be happy to help you with that. To give you the most relevant advice, could you provide more details about your specific situation or business type?",
            "That's an important aspect of entrepreneurship. Let me know more about your business context, and I can provide more targeted guidance.",
            "Great question! The answer often depends on your specific business model and target market. Could you share more details about your business concept?"
        ]
        
        return random.choice(generic_responses)
    
    def get_category_info(self, category: str) -> Dict:
        """Get information about a specific category"""
        if category in self.response_categories:
            return {
                'category': category,
                'description': self._get_category_description(category),
                'common_questions': self._get_common_questions(category),
                'resources': self._get_category_resources(category)
            }
        else:
            return {'error': 'Category not found'}
    
    def _get_category_description(self, category: str) -> str:
        """Get description for a category"""
        descriptions = {
            'business_planning': 'Creating comprehensive business plans, strategies, and roadmaps for success',
            'funding': 'Understanding funding options, investor relations, and financial planning',
            'marketing': 'Developing marketing strategies, brand building, and customer acquisition',
            'legal': 'Business legal requirements, compliance, and intellectual property protection',
            'operations': 'Business operations, process optimization, and team management',
            'financial': 'Financial planning, budgeting, and performance tracking'
        }
        return descriptions.get(category, 'General business advice and guidance')
    
    def _get_common_questions(self, category: str) -> List[str]:
        """Get common questions for a category"""
        common_questions = {
            'business_planning': [
                'How do I write a business plan?',
                'What should be in my executive summary?',
                'How do I analyze my target market?'
            ],
            'funding': [
                'How can I get funding for my startup?',
                'What do investors look for?',
                'How much equity should I give investors?'
            ],
            'marketing': [
                'How do I market my startup?',
                'What\'s the best social media strategy?',
                'How do I build brand awareness?'
            ],
            'legal': [
                'What legal structure should I choose?',
                'Do I need business insurance?',
                'How do I protect my intellectual property?'
            ],
            'operations': [
                'How do I hire my first employee?',
                'How do I manage cash flow?',
                'What technology do I need?'
            ],
            'financial': [
                'How do I price my product?',
                'What financial metrics should I track?',
                'How do I create financial projections?'
            ]
        }
        return common_questions.get(category, [])
    
    def _get_category_resources(self, category: str) -> List[str]:
        """Get resources for a category"""
        resources = {
            'business_planning': [
                'Business Plan Templates',
                'Market Research Tools',
                'SWOT Analysis Framework'
            ],
            'funding': [
                'Pitch Deck Templates',
                'Investor Database',
                'Funding Calculator'
            ],
            'marketing': [
                'Marketing Strategy Templates',
                'Social Media Calendar',
                'Brand Guidelines Template'
            ],
            'legal': [
                'Legal Checklist',
                'Contract Templates',
                'Compliance Guide'
            ],
            'operations': [
                'Process Documentation Templates',
                'Hiring Checklist',
                'Technology Stack Guide'
            ],
            'financial': [
                'Financial Projection Templates',
                'Budget Planning Tools',
                'KPI Dashboard'
            ]
        }
        return resources.get(category, [])

def main():
    """Test the response generator"""
    generator = BusinessResponseGenerator()
    
    test_questions = [
        "How do I write a business plan?",
        "What funding options are available for startups?",
        "How can I market my new product?",
        "What legal structure should I choose for my business?",
        "How do I manage my business finances?",
        "What technology do I need for my startup?"
    ]
    
    print("Testing Business Response Generator")
    print("=" * 50)
    
    for question in test_questions:
        print(f"\nQuestion: {question}")
        response = generator.generate_response(question)
        print(f"Category: {response['category']}")
        print(f"Confidence: {response['confidence']:.3f}")
        print(f"Response: {response['response']}")
        print("-" * 50)

if __name__ == "__main__":
    main()
