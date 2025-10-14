#!/usr/bin/env python3
"""
AI Chat Interface for InnoStart
Command-line interface for AI-powered chat responses
"""

import sys
import json
import argparse
import os
from typing import Dict, List, Optional

# Add the current directory to Python path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

try:
    from enhanced_ai_integration import EnhancedAI
except ImportError:
    print("Enhanced AI integration not available, using fallback")
    EnhancedAI = None

def main():
    parser = argparse.ArgumentParser(description='AI Chat Interface for InnoStart')
    parser.add_argument('--message', required=True, help='User message')
    parser.add_argument('--history', help='Conversation history as JSON string')
    parser.add_argument('--intent', help='User intent classification')
    
    args = parser.parse_args()
    
    try:
        # Parse conversation history
        history = []
        if args.history:
            try:
                history = json.loads(args.history)
            except json.JSONDecodeError:
                history = []
        
        # Always use fallback response generation for now (more reliable)
        response = generate_fallback_response(args.message, args.intent)
        
        # Return response as JSON
        result = {
            'response': response,
            'intent': args.intent or 'general_inquiry',
            'success': True
        }
        
        print(json.dumps(result))
        
    except Exception as e:
        # Return error response
        error_result = {
            'response': f"I apologize, but I'm having trouble processing your request right now. Please try again or rephrase your question.",
            'error': str(e),
            'success': False
        }
        print(json.dumps(error_result))

def generate_fallback_response(message: str, intent: Optional[str] = None) -> str:
    """Generate a fallback response when enhanced AI is not available"""
    
    message_lower = message.lower()
    
    # Intent-based responses
    if intent == 'greeting' or any(word in message_lower for word in ['hello', 'hi', 'hey']):
        return """Hello! I'm your AI business assistant for Musanze, Rwanda. I can help you with:

🎯 **Choose what you'd like to explore:**
• **Business Opportunities** - Find the right business for you
• **Startup Costs** - Understand investment requirements  
• **Planning** - Create business plans and strategies

What would you like to start with?"""

    elif intent == 'business_opportunities' or 'business' in message_lower:
        return """Great choice! Let's find the perfect business opportunity for you in Musanze, Rwanda.

💰 **First, what's your budget range?**

• **500,000 - 1,000,000 RWF** - Small services, retail, internet café
• **1,000,000 - 3,000,000 RWF** - Coffee processing, organic farming, restaurant
• **3,000,000 - 5,000,000 RWF** - Mountain tours, eco-lodges, gift shops
• **Write any amount** - Custom budget range

Please select your budget range or tell me your specific budget amount."""

    elif intent == 'budget_inquiry' or any(word in message_lower for word in ['budget', 'cost', 'investment']):
        return """Great! Let me help you understand startup costs for different business types in Musanze, Rwanda.

💰 **Budget Categories:**

• **500,000 - 1,000,000 RWF** - Small services, retail, internet café
• **1,000,000 - 3,000,000 RWF** - Coffee processing, organic farming, restaurant
• **3,000,000 - 5,000,000 RWF** - Mountain tours, eco-lodges, gift shops
• **5,000,000+ RWF** - Large eco-lodges, major tourism facilities

**What's your budget range?** I'll show you exactly what you can start with that amount, including:
• Detailed cost breakdown
• Equipment and supplies needed
• Licenses and permits required
• Working capital requirements
• Revenue projections

Please select your budget range or tell me your specific amount."""

    elif intent == 'specific_business' or any(word in message_lower for word in ['coffee', 'restaurant', 'tourism', 'agriculture']):
        return f"""Excellent! You're interested in {message}. Let me provide you with comprehensive information about this business opportunity in Musanze, Rwanda.

I'll generate a detailed business plan including:
• Startup costs and investment requirements
• Revenue projections and profit margins
• Market analysis and target customers
• Location recommendations
• Marketing strategies
• Legal requirements and permits
• Growth opportunities

**📄 Export Options:**
[PDF Export] [Word Export] [Excel Export]

Would you like me to generate a complete business plan for {message}?"""

    elif intent == 'planning' or 'plan' in message_lower:
        return """Excellent! I can help you create comprehensive business plans and strategies. Let's start with your business idea.

📋 **Planning Services Available:**

• **Business Plan Creation** - Complete professional business plan
• **Financial Projections** - Revenue, costs, and profit analysis
• **Market Research** - Target market and competition analysis
• **Marketing Strategy** - Customer acquisition and promotion plans
• **Operations Planning** - Day-to-day business operations
• **Risk Assessment** - Potential challenges and solutions

**What type of planning do you need?**

1. **I have a business idea** - Let's create a complete business plan
2. **I need financial projections** - Let's analyze costs and revenue
3. **I want market research** - Let's study your target market
4. **I need marketing help** - Let's create a marketing strategy

Please tell me what you'd like to plan for, or select one of the options above."""

    elif intent == 'export_request' or any(word in message_lower for word in ['export', 'download', 'pdf']):
        return """Perfect! I can help you export business information in multiple formats.

📄 **Export Options Available:**

• **PDF Export** - Professional business plan document
• **Word Export** - Editable business plan document
• **Excel Export** - Financial projections and data spreadsheets
• **PowerPoint Export** - Business presentation slides

**What would you like to export?**
• Complete business plan
• Financial projections
• Market analysis
• Marketing strategy
• All of the above

Please let me know what specific information you'd like to export and in which format."""

    elif intent == 'help' or 'help' in message_lower:
        return """I'm here to help you with all your business needs in Musanze, Rwanda! Here's what I can do:

🎯 **Main Services:**
• **Business Opportunities** - Find profitable business ideas
• **Startup Costs** - Understand investment requirements
• **Business Planning** - Create comprehensive business plans

💡 **How to Use:**
1. Start by saying "Hello" or "Business Opportunities"
2. Tell me your budget range
3. Choose a business sector that interests you
4. Get detailed business information with export options

📊 **What You'll Get:**
• Detailed business plans with startup costs
• Revenue projections and profit analysis
• Market research and competition analysis
• Marketing strategies and growth opportunities
• Export options (PDF, Word, Excel)

**Just ask me anything about starting a business in Musanze, Rwanda!**"""

    else:
        return f"""I understand you're asking about "{message}". Let me help you with that!

As your AI business assistant for Musanze, Rwanda, I can provide information about:
• Business opportunities and startup ideas
• Investment costs and budget planning
• Market analysis and competition research
• Business plan creation and strategy development
• Export options for business documents

Could you please be more specific about what you'd like to know? For example:
• "I want to start a coffee business"
• "What's the budget for a restaurant?"
• "Help me create a business plan"
• "Show me tourism opportunities"

I'm here to help you succeed in your business journey!"""

if __name__ == "__main__":
    main()
