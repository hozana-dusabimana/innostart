#!/usr/bin/env python3
"""
Enhanced AI Integration for InnoStart
Combines trained ML model with OpenAI API for superior business responses
"""

import os
import json
import logging
from typing import Dict, List, Optional, Tuple
import requests
from response_generator import BusinessResponseGenerator
from kaggle_dataset_manager import KaggleDatasetManager

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class EnhancedAI:
    """Enhanced AI system combining ML model with OpenAI API"""
    
    def __init__(self, openai_api_key: Optional[str] = None):
        self.openai_api_key = openai_api_key or os.getenv('OPENAI_API_KEY')
        self.base_url = "https://api.openai.com/v1"
        
        # Initialize components
        self.response_generator = BusinessResponseGenerator()
        self.dataset_manager = KaggleDatasetManager()
        
        # Business context database
        self.business_context = self._load_business_context()
        
        # Response templates for different scenarios
        self.response_templates = self._load_response_templates()
    
    def _load_business_context(self) -> Dict:
        """Load business context and knowledge base"""
        return {
            'industries': {
                'technology': {
                    'funding_range': '$50K - $5M',
                    'key_metrics': ['user growth', 'revenue per user', 'churn rate'],
                    'common_challenges': ['scaling', 'talent acquisition', 'competition'],
                    'success_factors': ['innovation', 'market fit', 'execution']
                },
                'retail': {
                    'funding_range': '$10K - $500K',
                    'key_metrics': ['inventory turnover', 'customer acquisition cost', 'lifetime value'],
                    'common_challenges': ['inventory management', 'seasonal demand', 'competition'],
                    'success_factors': ['location', 'customer service', 'product quality']
                },
                'service': {
                    'funding_range': '$5K - $100K',
                    'key_metrics': ['client retention', 'service quality', 'profit margins'],
                    'common_challenges': ['client acquisition', 'service delivery', 'scaling'],
                    'success_factors': ['expertise', 'reputation', 'customer satisfaction']
                }
            },
            'funding_stages': {
                'pre_seed': {'range': '$0 - $50K', 'source': 'personal savings, friends, family'},
                'seed': {'range': '$50K - $2M', 'source': 'angel investors, accelerators'},
                'series_a': {'range': '$2M - $15M', 'source': 'venture capital, strategic investors'},
                'series_b': {'range': '$15M - $50M', 'source': 'venture capital, private equity'}
            },
            'market_sizes': {
                'small': {'description': 'Local or niche market', 'potential': 'Limited but focused'},
                'medium': {'description': 'Regional or specific industry', 'potential': 'Moderate growth'},
                'large': {'description': 'National or broad industry', 'potential': 'High growth potential'},
                'global': {'description': 'International market', 'potential': 'Massive opportunity'}
            }
        }
    
    def _load_response_templates(self) -> Dict:
        """Load response templates for different scenarios"""
        return {
            'funding_advice': {
                'pre_seed': "For pre-seed funding, focus on validating your idea with minimal resources. Consider bootstrapping, friends and family, or small grants.",
                'seed': "Seed funding requires a working prototype and initial traction. Prepare a compelling pitch deck and seek angel investors or accelerators.",
                'series_a': "Series A funding needs proven product-market fit and strong growth metrics. Target venture capital firms that specialize in your industry."
            },
            'market_analysis': {
                'small': "For small markets, focus on deep penetration and customer loyalty. Build strong relationships and become the go-to solution.",
                'medium': "Medium markets offer good growth potential. Focus on market share expansion and operational efficiency.",
                'large': "Large markets provide significant opportunities but intense competition. Differentiate clearly and scale quickly."
            },
            'industry_advice': {
                'technology': "Tech startups should focus on user acquisition, product development, and building scalable systems. Consider freemium models and data-driven decisions.",
                'retail': "Retail businesses need strong inventory management, customer experience, and omnichannel presence. Focus on margins and customer retention.",
                'service': "Service businesses rely on expertise, reputation, and client relationships. Focus on quality delivery and referral systems."
            }
        }
    
    def generate_enhanced_response(self, question: str, context: Optional[Dict] = None) -> Dict:
        """Generate enhanced response using ML model and OpenAI API"""
        
        # Get ML model prediction
        ml_response = self.response_generator.generate_response(question, context)
        
        # Enhance with OpenAI if available
        if self.openai_api_key:
            enhanced_response = self._enhance_with_openai(question, ml_response, context)
        else:
            enhanced_response = self._enhance_with_context(question, ml_response, context)
        
        return enhanced_response
    
    def _enhance_with_openai(self, question: str, ml_response: Dict, context: Optional[Dict] = None) -> Dict:
        """Enhance response using OpenAI API"""
        try:
            # Create enhanced prompt
            prompt = self._create_enhancement_prompt(question, ml_response, context)
            
            headers = {
                "Authorization": f"Bearer {self.openai_api_key}",
                "Content-Type": "application/json"
            }
            
            data = {
                "model": "gpt-3.5-turbo",
                "messages": [
                    {
                        "role": "system",
                        "content": "You are an expert business consultant AI that provides detailed, actionable advice for entrepreneurs and startup founders. Always be specific, practical, and encouraging."
                    },
                    {
                        "role": "user",
                        "content": prompt
                    }
                ],
                "max_tokens": 500,
                "temperature": 0.7
            }
            
            response = requests.post(
                f"{self.base_url}/chat/completions",
                headers=headers,
                json=data,
                timeout=30
            )
            
            if response.status_code == 200:
                result = response.json()
                enhanced_text = result['choices'][0]['message']['content']
                
                return {
                    'response': enhanced_text,
                    'category': ml_response['category'],
                    'confidence': min(0.95, ml_response['confidence'] + 0.1),  # Boost confidence
                    'timestamp': ml_response['timestamp'],
                    'enhanced': True,
                    'source': 'openai_enhanced'
                }
            else:
                logger.error(f"OpenAI API error: {response.status_code}")
                return self._enhance_with_context(question, ml_response, context)
                
        except Exception as e:
            logger.error(f"Error enhancing with OpenAI: {e}")
            return self._enhance_with_context(question, ml_response, context)
    
    def _enhance_with_context(self, question: str, ml_response: Dict, context: Optional[Dict] = None) -> Dict:
        """Enhance response using business context and templates"""
        
        category = ml_response['category']
        base_response = ml_response['response']
        
        # Add context-specific information
        context_info = self._get_context_info(category, context)
        
        # Add industry-specific advice if available
        industry_advice = self._get_industry_advice(context)
        
        # Add actionable steps
        actionable_steps = self._get_actionable_steps(category, context)
        
        # Combine all information
        enhanced_response = base_response
        
        if context_info:
            enhanced_response += f"\n\n📊 Context: {context_info}"
        
        if industry_advice:
            enhanced_response += f"\n\n🏭 Industry Insight: {industry_advice}"
        
        if actionable_steps:
            enhanced_response += f"\n\n✅ Next Steps: {actionable_steps}"
        
        return {
            'response': enhanced_response,
            'category': category,
            'confidence': ml_response['confidence'],
            'timestamp': ml_response['timestamp'],
            'enhanced': True,
            'source': 'context_enhanced'
        }
    
    def _create_enhancement_prompt(self, question: str, ml_response: Dict, context: Optional[Dict] = None) -> str:
        """Create prompt for OpenAI enhancement"""
        
        prompt = f"""
        Question: {question}
        
        Current Response: {ml_response['response']}
        Category: {ml_response['category']}
        """
        
        if context:
            prompt += f"\nContext: {json.dumps(context, indent=2)}"
        
        prompt += """
        
        Please enhance this response by:
        1. Making it more specific and actionable
        2. Adding relevant examples or case studies
        3. Including specific metrics or benchmarks where appropriate
        4. Providing clear next steps
        5. Maintaining a professional but encouraging tone
        
        Keep the response concise but comprehensive, and ensure it directly addresses the user's question.
        """
        
        return prompt
    
    def _get_context_info(self, category: str, context: Optional[Dict] = None) -> str:
        """Get context-specific information"""
        if not context:
            return ""
        
        context_info = []
        
        if category == 'funding' and 'budget' in context:
            budget = context['budget']
            if budget < 10000:
                context_info.append("Given your budget, consider bootstrapping or small business loans.")
            elif budget < 100000:
                context_info.append("Your budget range suggests angel investors or crowdfunding as viable options.")
            else:
                context_info.append("With your budget, you could pursue venture capital or larger investment rounds.")
        
        if 'business_type' in context:
            business_type = context['business_type']
            if business_type in self.business_context['industries']:
                industry_info = self.business_context['industries'][business_type]
                context_info.append(f"For {business_type} businesses, typical funding ranges are {industry_info['funding_range']}.")
        
        if 'location' in context:
            context_info.append(f"Consider local market conditions and regulations in {context['location']}.")
        
        return " ".join(context_info)
    
    def _get_industry_advice(self, context: Optional[Dict] = None) -> str:
        """Get industry-specific advice"""
        if not context or 'business_type' not in context:
            return ""
        
        business_type = context['business_type']
        
        if business_type in self.response_templates['industry_advice']:
            return self.response_templates['industry_advice'][business_type]
        
        return ""
    
    def _get_actionable_steps(self, category: str, context: Optional[Dict] = None) -> str:
        """Get actionable steps for the category"""
        
        steps_by_category = {
            'business_planning': [
                "1. Define your business concept and value proposition",
                "2. Conduct market research and competitive analysis",
                "3. Create financial projections and funding requirements",
                "4. Develop your marketing and operations strategy"
            ],
            'funding': [
                "1. Determine your funding needs and timeline",
                "2. Prepare a compelling pitch deck and business plan",
                "3. Research and identify potential investors",
                "4. Practice your pitch and prepare for due diligence"
            ],
            'marketing': [
                "1. Define your target audience and customer personas",
                "2. Develop your brand identity and messaging",
                "3. Choose appropriate marketing channels and tactics",
                "4. Set up tracking and measurement systems"
            ],
            'legal': [
                "1. Choose the appropriate business structure",
                "2. Register your business and obtain necessary licenses",
                "3. Protect your intellectual property",
                "4. Set up proper contracts and legal documentation"
            ],
            'operations': [
                "1. Document your core business processes",
                "2. Set up necessary systems and technology",
                "3. Hire and train your team",
                "4. Implement quality control and monitoring systems"
            ],
            'financial': [
                "1. Set up proper accounting and bookkeeping systems",
                "2. Create detailed financial projections",
                "3. Monitor key financial metrics regularly",
                "4. Plan for different financial scenarios"
            ]
        }
        
        if category in steps_by_category:
            return "\n".join(steps_by_category[category])
        
        return "1. Research best practices in your industry\n2. Create a detailed action plan\n3. Set measurable goals and timelines\n4. Monitor progress and adjust as needed"
    
    def get_business_insights(self, business_type: str, context: Optional[Dict] = None) -> Dict:
        """Get comprehensive business insights for a specific business type"""
        
        if business_type not in self.business_context['industries']:
            return {'error': 'Business type not found'}
        
        industry_info = self.business_context['industries'][business_type]
        
        insights = {
            'business_type': business_type,
            'funding_range': industry_info['funding_range'],
            'key_metrics': industry_info['key_metrics'],
            'common_challenges': industry_info['common_challenges'],
            'success_factors': industry_info['success_factors'],
            'recommendations': []
        }
        
        # Add context-specific recommendations
        if context:
            if 'budget' in context:
                budget = context['budget']
                if budget < 10000:
                    insights['recommendations'].append("Consider starting small and bootstrapping your growth")
                elif budget < 100000:
                    insights['recommendations'].append("Look into angel investors or small business loans")
                else:
                    insights['recommendations'].append("You have good options for venture capital or larger investments")
            
            if 'experience' in context:
                if context['experience'] == 'beginner':
                    insights['recommendations'].append("Focus on learning and building experience before scaling")
                elif context['experience'] == 'experienced':
                    insights['recommendations'].append("Leverage your experience to build a strong team and systems")
        
        return insights
    
    def train_with_kaggle_data(self) -> bool:
        """Train the model using Kaggle datasets"""
        try:
            logger.info("Starting training with Kaggle datasets...")
            
            # Download business datasets
            download_results = self.dataset_manager.download_business_datasets()
            
            # Create training data from datasets
            training_df = self.dataset_manager.create_training_data_from_datasets()
            
            if not training_df.empty:
                # Save training data
                training_df.to_csv('training_data/kaggle_training_data.csv', index=False)
                logger.info(f"Created {len(training_df)} training examples from Kaggle data")
                
                # Train the model (this would require the training script)
                logger.info("Training data prepared. Run train_response_model.py to train the model.")
                return True
            else:
                logger.warning("No training data created from Kaggle datasets")
                return False
                
        except Exception as e:
            logger.error(f"Error training with Kaggle data: {e}")
            return False

def main():
    """Test the enhanced AI system"""
    ai = EnhancedAI()
    
    # Test questions with context
    test_cases = [
        {
            'question': "How do I get funding for my tech startup?",
            'context': {
                'business_type': 'technology',
                'budget': 50000,
                'experience': 'beginner',
                'location': 'San Francisco'
            }
        },
        {
            'question': "What marketing strategy should I use?",
            'context': {
                'business_type': 'retail',
                'budget': 10000,
                'target_market': 'local'
            }
        },
        {
            'question': "How do I manage my business finances?",
            'context': {
                'business_type': 'service',
                'revenue': 100000,
                'employees': 5
            }
        }
    ]
    
    print("Testing Enhanced AI System")
    print("=" * 50)
    
    for i, test_case in enumerate(test_cases, 1):
        print(f"\nTest Case {i}:")
        print(f"Question: {test_case['question']}")
        print(f"Context: {test_case['context']}")
        
        response = ai.generate_enhanced_response(
            test_case['question'], 
            test_case['context']
        )
        
        print(f"Category: {response['category']}")
        print(f"Confidence: {response['confidence']:.3f}")
        print(f"Enhanced: {response['enhanced']}")
        print(f"Response: {response['response']}")
        print("-" * 50)
    
    # Test business insights
    print("\nBusiness Insights Test:")
    insights = ai.get_business_insights('technology', {'budget': 100000, 'experience': 'beginner'})
    print(json.dumps(insights, indent=2))

if __name__ == "__main__":
    main()







