#!/usr/bin/env python3
"""
InnoStart AI Integration Module
Advanced AI functionality for business idea generation and analysis
"""

import json
import requests
import os
from typing import List, Dict, Any
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class InnoStartAI:
    """Main AI integration class for InnoStart"""
    
    def __init__(self, openai_api_key: str = None):
        """
        Initialize the AI integration
        
        Args:
            openai_api_key: OpenAI API key for advanced AI features
        """
        self.openai_api_key = openai_api_key or os.getenv('OPENAI_API_KEY')
        self.base_url = "https://api.openai.com/v1"
        
    def generate_business_ideas(self, location: str, interests: List[str], 
                              budget: str, market_data: Dict = None) -> List[Dict]:
        """
        Generate business ideas using AI
        
        Args:
            location: User's location
            interests: List of user interests
            budget: Budget range
            market_data: Optional market data
            
        Returns:
            List of generated business ideas
        """
        try:
            if self.openai_api_key:
                return self._generate_with_openai(location, interests, budget, market_data)
            else:
                return self._generate_fallback_ideas(location, interests, budget)
        except Exception as e:
            logger.error(f"Error generating business ideas: {e}")
            return self._generate_fallback_ideas(location, interests, budget)
    
    def _generate_with_openai(self, location: str, interests: List[str], 
                            budget: str, market_data: Dict = None) -> List[Dict]:
        """Generate ideas using OpenAI API"""
        
        interests_str = ", ".join(interests)
        market_context = ""
        if market_data:
            market_context = f"Market data: {json.dumps(market_data)}"
        
        prompt = f"""
        Generate 5 innovative business ideas for an entrepreneur with the following profile:
        
        Location: {location}
        Interests: {interests_str}
        Budget: {budget}
        {market_context}
        
        For each idea, provide:
        1. A compelling title
        2. A detailed description (2-3 sentences)
        3. Business category
        4. Required budget level (Low/Medium/High)
        5. Difficulty level (Easy/Medium/Hard)
        6. Key success factors
        7. Potential challenges
        
        Focus on ideas that are:
        - Feasible for the given budget
        - Relevant to the location
        - Aligned with the user's interests
        - Have market potential
        
        Return the response as a JSON array of objects.
        """
        
        headers = {
            "Authorization": f"Bearer {self.openai_api_key}",
            "Content-Type": "application/json"
        }
        
        data = {
            "model": "gpt-3.5-turbo",
            "messages": [
                {"role": "system", "content": "You are a business consultant AI that generates innovative, feasible business ideas for entrepreneurs."},
                {"role": "user", "content": prompt}
            ],
            "max_tokens": 1500,
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
            content = result['choices'][0]['message']['content']
            
            # Try to parse JSON from the response
            try:
                ideas = json.loads(content)
                return self._format_ideas(ideas)
            except json.JSONDecodeError:
                # If JSON parsing fails, extract ideas from text
                return self._extract_ideas_from_text(content)
        else:
            logger.error(f"OpenAI API error: {response.status_code}")
            return self._generate_fallback_ideas(location, interests, budget)
    
    def _generate_fallback_ideas(self, location: str, interests: List[str], 
                               budget: str) -> List[Dict]:
        """Generate fallback ideas when AI API is not available"""
        
        # Location-based idea templates
        location_ideas = {
            'urban': [
                {
                    'title': 'Urban Food Delivery Service',
                    'description': 'Fast, healthy food delivery service for busy urban professionals.',
                    'category': 'Food & Delivery',
                    'budget': 'Medium',
                    'difficulty': 'Medium',
                    'success_factors': ['Speed', 'Quality', 'Local partnerships'],
                    'challenges': ['Competition', 'Logistics', 'Customer acquisition']
                },
                {
                    'title': 'Co-working Space Management',
                    'description': 'Flexible co-working spaces for freelancers and remote workers.',
                    'category': 'Real Estate',
                    'budget': 'High',
                    'difficulty': 'Hard',
                    'success_factors': ['Location', 'Amenities', 'Community building'],
                    'challenges': ['High startup costs', 'Market saturation', 'Maintenance']
                }
            ],
            'rural': [
                {
                    'title': 'Agricultural Consulting Service',
                    'description': 'Modern farming techniques and technology consulting for local farmers.',
                    'category': 'Agriculture',
                    'budget': 'Low',
                    'difficulty': 'Medium',
                    'success_factors': ['Expertise', 'Local knowledge', 'Trust building'],
                    'challenges': ['Seasonal demand', 'Technology adoption', 'Market education']
                },
                {
                    'title': 'Rural Tourism Experience',
                    'description': 'Unique rural tourism experiences like farm stays and nature tours.',
                    'category': 'Tourism',
                    'budget': 'Medium',
                    'difficulty': 'Medium',
                    'success_factors': ['Authenticity', 'Marketing', 'Local partnerships'],
                    'challenges': ['Seasonal business', 'Infrastructure', 'Marketing reach']
                }
            ]
        }
        
        # Interest-based idea templates
        interest_ideas = {
            'technology': [
                {
                    'title': 'Local Tech Support Service',
                    'description': 'In-home and remote tech support for individuals and small businesses.',
                    'category': 'Technology',
                    'budget': 'Low',
                    'difficulty': 'Easy',
                    'success_factors': ['Technical expertise', 'Customer service', 'Reliability'],
                    'challenges': ['Competition', 'Technology changes', 'Customer education']
                }
            ],
            'food': [
                {
                    'title': 'Home-based Catering Service',
                    'description': 'Catering services for small events, parties, and corporate meetings.',
                    'category': 'Food & Beverage',
                    'budget': 'Low',
                    'difficulty': 'Easy',
                    'success_factors': ['Quality', 'Reliability', 'Menu variety'],
                    'challenges': ['Food safety', 'Scaling', 'Seasonal demand']
                }
            ],
            'fashion': [
                {
                    'title': 'Online Fashion Boutique',
                    'description': 'Curated fashion items through an online store with personal styling.',
                    'category': 'E-commerce',
                    'budget': 'Medium',
                    'difficulty': 'Medium',
                    'success_factors': ['Style curation', 'Customer service', 'Marketing'],
                    'challenges': ['Inventory management', 'Competition', 'Returns']
                }
            ]
        }
        
        # Select ideas based on location and interests
        selected_ideas = []
        
        # Add location-based ideas
        location_type = 'urban' if any(word in location.lower() for word in ['city', 'urban', 'metro']) else 'rural'
        if location_type in location_ideas:
            selected_ideas.extend(location_ideas[location_type][:2])
        
        # Add interest-based ideas
        for interest in interests:
            interest_lower = interest.lower()
            for key, ideas in interest_ideas.items():
                if key in interest_lower:
                    selected_ideas.extend(ideas[:1])
                    break
        
        # Filter by budget
        budget_mapping = {
            '0-1000': 'Low',
            '1000-5000': 'Medium',
            '5000-10000': 'High',
            '10000+': 'High'
        }
        
        target_budget = budget_mapping.get(budget, 'Medium')
        filtered_ideas = [idea for idea in selected_ideas if idea['budget'] == target_budget]
        
        # If no ideas match budget, return all ideas
        if not filtered_ideas:
            filtered_ideas = selected_ideas
        
        return filtered_ideas[:5]  # Return top 5 ideas
    
    def _format_ideas(self, ideas: List[Dict]) -> List[Dict]:
        """Format ideas from AI response"""
        formatted_ideas = []
        
        for idea in ideas:
            formatted_idea = {
                'title': idea.get('title', 'Business Idea'),
                'description': idea.get('description', 'A promising business opportunity.'),
                'category': idea.get('category', 'General'),
                'budget': idea.get('budget', 'Medium'),
                'difficulty': idea.get('difficulty', 'Medium'),
                'success_factors': idea.get('success_factors', []),
                'challenges': idea.get('challenges', [])
            }
            formatted_ideas.append(formatted_idea)
        
        return formatted_ideas
    
    def _extract_ideas_from_text(self, text: str) -> List[Dict]:
        """Extract ideas from text response when JSON parsing fails"""
        # Simple text parsing - in production, use more sophisticated NLP
        ideas = []
        lines = text.split('\n')
        current_idea = {}
        
        for line in lines:
            line = line.strip()
            if line.startswith('Title:') or line.startswith('1.'):
                if current_idea:
                    ideas.append(current_idea)
                current_idea = {'title': line.replace('Title:', '').replace('1.', '').strip()}
            elif line.startswith('Description:'):
                current_idea['description'] = line.replace('Description:', '').strip()
            elif line.startswith('Category:'):
                current_idea['category'] = line.replace('Category:', '').strip()
            elif line.startswith('Budget:'):
                current_idea['budget'] = line.replace('Budget:', '').strip()
            elif line.startswith('Difficulty:'):
                current_idea['difficulty'] = line.replace('Difficulty:', '').strip()
        
        if current_idea:
            ideas.append(current_idea)
        
        return self._format_ideas(ideas)
    
    def analyze_market_opportunity(self, business_idea: str, location: str) -> Dict:
        """
        Analyze market opportunity for a business idea
        
        Args:
            business_idea: The business idea to analyze
            location: Target location
            
        Returns:
            Market analysis results
        """
        try:
            if self.openai_api_key:
                return self._analyze_with_openai(business_idea, location)
            else:
                return self._analyze_fallback(business_idea, location)
        except Exception as e:
            logger.error(f"Error analyzing market: {e}")
            return self._analyze_fallback(business_idea, location)
    
    def _analyze_with_openai(self, business_idea: str, location: str) -> Dict:
        """Analyze market using OpenAI API"""
        
        prompt = f"""
        Analyze the market opportunity for this business idea: "{business_idea}" in {location}.
        
        Provide analysis on:
        1. Market size and potential
        2. Target customer segments
        3. Competitive landscape
        4. Market trends and opportunities
        5. Potential challenges
        6. Success probability (1-10)
        
        Return as JSON with these fields: market_size, target_customers, competition, trends, challenges, success_probability.
        """
        
        headers = {
            "Authorization": f"Bearer {self.openai_api_key}",
            "Content-Type": "application/json"
        }
        
        data = {
            "model": "gpt-3.5-turbo",
            "messages": [
                {"role": "system", "content": "You are a market research analyst AI that provides detailed market analysis for business ideas."},
                {"role": "user", "content": prompt}
            ],
            "max_tokens": 1000,
            "temperature": 0.5
        }
        
        response = requests.post(
            f"{self.base_url}/chat/completions",
            headers=headers,
            json=data,
            timeout=30
        )
        
        if response.status_code == 200:
            result = response.json()
            content = result['choices'][0]['message']['content']
            
            try:
                return json.loads(content)
            except json.JSONDecodeError:
                return self._analyze_fallback(business_idea, location)
        else:
            return self._analyze_fallback(business_idea, location)
    
    def _analyze_fallback(self, business_idea: str, location: str) -> Dict:
        """Fallback market analysis"""
        return {
            'market_size': 'Medium to Large',
            'target_customers': 'Local residents and businesses',
            'competition': 'Moderate competition expected',
            'trends': 'Growing market demand',
            'challenges': 'Customer acquisition and market penetration',
            'success_probability': 7
        }
    
    def generate_financial_projections(self, business_data: Dict) -> Dict:
        """
        Generate financial projections for a business
        
        Args:
            business_data: Business information and parameters
            
        Returns:
            Financial projections
        """
        try:
            # Basic financial calculations
            monthly_revenue = business_data.get('monthly_revenue', 0)
            growth_rate = business_data.get('growth_rate', 0.05)
            monthly_expenses = business_data.get('monthly_expenses', 0)
            initial_investment = business_data.get('initial_investment', 0)
            projection_months = business_data.get('projection_months', 12)
            
            projections = {
                'monthly_data': [],
                'total_revenue': 0,
                'total_expenses': 0,
                'net_profit': 0,
                'break_even_month': None,
                'roi': 0
            }
            
            current_revenue = monthly_revenue
            cumulative_profit = -initial_investment
            
            for month in range(1, projection_months + 1):
                monthly_revenue_amount = current_revenue
                monthly_expense_amount = monthly_expenses
                monthly_profit = monthly_revenue_amount - monthly_expense_amount
                
                cumulative_profit += monthly_profit
                
                projections['monthly_data'].append({
                    'month': month,
                    'revenue': monthly_revenue_amount,
                    'expenses': monthly_expense_amount,
                    'profit': monthly_profit,
                    'cumulative_profit': cumulative_profit
                })
                
                projections['total_revenue'] += monthly_revenue_amount
                projections['total_expenses'] += monthly_expense_amount
                
                # Check for break-even
                if projections['break_even_month'] is None and cumulative_profit >= 0:
                    projections['break_even_month'] = month
                
                # Apply growth rate
                current_revenue *= (1 + growth_rate)
            
            projections['net_profit'] = projections['total_revenue'] - projections['total_expenses'] - initial_investment
            
            if initial_investment > 0:
                projections['roi'] = (projections['net_profit'] / initial_investment) * 100
            
            return projections
            
        except Exception as e:
            logger.error(f"Error generating financial projections: {e}")
            return {}
    
    def get_business_advice(self, question: str, context: Dict = None) -> str:
        """
        Get business advice using AI
        
        Args:
            question: User's question
            context: Additional context about the business
            
        Returns:
            AI-generated advice
        """
        try:
            if self.openai_api_key:
                return self._get_advice_with_openai(question, context)
            else:
                return self._get_advice_fallback(question)
        except Exception as e:
            logger.error(f"Error getting business advice: {e}")
            return self._get_advice_fallback(question)
    
    def _get_advice_with_openai(self, question: str, context: Dict = None) -> str:
        """Get advice using OpenAI API"""
        
        context_str = ""
        if context:
            context_str = f"Business context: {json.dumps(context)}"
        
        prompt = f"""
        As a business consultant AI, provide helpful advice for this question: "{question}"
        
        {context_str}
        
        Provide practical, actionable advice that is specific and relevant to the situation.
        Keep the response concise but comprehensive.
        """
        
        headers = {
            "Authorization": f"Bearer {self.openai_api_key}",
            "Content-Type": "application/json"
        }
        
        data = {
            "model": "gpt-3.5-turbo",
            "messages": [
                {"role": "system", "content": "You are an experienced business consultant AI that provides practical, actionable advice for entrepreneurs."},
                {"role": "user", "content": prompt}
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
            return result['choices'][0]['message']['content']
        else:
            return self._get_advice_fallback(question)
    
    def _get_advice_fallback(self, question: str) -> str:
        """Fallback advice when AI API is not available"""
        
        question_lower = question.lower()
        
        if 'funding' in question_lower or 'investment' in question_lower:
            return "Consider multiple funding sources: bootstrapping, angel investors, bank loans, or crowdfunding. Each has different requirements and benefits. Start with a clear business plan and financial projections."
        
        if 'marketing' in question_lower:
            return "Focus on your target audience and use multiple channels: social media, content marketing, local advertising, and word-of-mouth. Start with low-cost digital marketing and scale up based on results."
        
        if 'competition' in question_lower:
            return "Analyze your competitors' strengths and weaknesses. Differentiate your business through unique value propositions, better customer service, or innovative approaches. Focus on what makes you different."
        
        if 'team' in question_lower or 'hiring' in question_lower:
            return "Hire people who share your vision and bring complementary skills. Start with essential roles and expand as you grow. Consider contractors or freelancers for specialized tasks initially."
        
        return "Focus on understanding your customers' needs, delivering value, and building strong relationships. Start small, test your assumptions, and iterate based on feedback. Success comes from persistence and continuous improvement."


def main():
    """Main function for testing the AI integration"""
    
    # Initialize AI
    ai = InnoStartAI()
    
    # Test business idea generation
    print("Testing business idea generation...")
    ideas = ai.generate_business_ideas(
        location="New York City",
        interests=["technology", "food"],
        budget="1000-5000"
    )
    
    print(f"Generated {len(ideas)} business ideas:")
    for i, idea in enumerate(ideas, 1):
        print(f"{i}. {idea['title']}")
        print(f"   {idea['description']}")
        print(f"   Category: {idea['category']}, Budget: {idea['budget']}, Difficulty: {idea['difficulty']}")
        print()
    
    # Test market analysis
    print("Testing market analysis...")
    analysis = ai.analyze_market_opportunity("Food delivery service", "New York City")
    print(f"Market analysis: {analysis}")
    print()
    
    # Test financial projections
    print("Testing financial projections...")
    projections = ai.generate_financial_projections({
        'monthly_revenue': 5000,
        'growth_rate': 0.1,
        'monthly_expenses': 3000,
        'initial_investment': 10000,
        'projection_months': 12
    })
    print(f"Financial projections: {projections}")
    print()
    
    # Test business advice
    print("Testing business advice...")
    advice = ai.get_business_advice("How can I get funding for my startup?")
    print(f"Business advice: {advice}")


if __name__ == "__main__":
    main()

