import google.generativeai as genai
import os
import json
from typing import Dict, List, Optional

class GeminiAI:
    def __init__(self, api_key: str = "AIzaSyAfZ4MVyx-t4lvr0kiqwVEfoMYPzKIG-3A"):
        """Initialize Gemini AI with API key"""
        self.api_key = api_key
        genai.configure(api_key=self.api_key)
        self.model = genai.GenerativeModel('gemini-2.0-flash-exp')
        
    def generate_response(self, user_message: str, context: str = "", local_data: str = "") -> str:
        """Generate AI response using Gemini with local dataset context"""
        try:
            # Determine the type of message and create appropriate prompt
            message_type = self._classify_message_type(user_message)
            
            if message_type == "personal_greeting":
                prompt = f"""
                You are a friendly AI assistant. The user said: "{user_message}"
                
                Respond warmly and naturally, then gently guide them toward business-related topics.
                Be friendly, use emojis, and mention that you're here to help with business advice for Musanze, Rwanda.
                Keep it brief and welcoming.
                """
            elif message_type == "general_question":
                prompt = f"""
                You are an AI assistant. The user asked: "{user_message}"
                
                Provide a helpful answer to their question, then mention that you're also a business assistant 
                for entrepreneurs in Musanze, Rwanda, and can help with business-related questions.
                Be informative and friendly.
                """
            else:  # business_related
                # Enhanced prompt with local dataset context
                prompt = f"""
                You are an AI business assistant for entrepreneurs in Musanze, Rwanda. 
                
                LOCAL MUSANZE BUSINESS DATASET CONTEXT:
                {local_data}
                
                CONVERSATION CONTEXT:
                {context}
                
                USER QUESTION: {user_message}
                
                INSTRUCTIONS:
                1. Use the local Musanze business dataset context above to provide specific, accurate information
                2. If the user asks for business opportunities, provide specific opportunities from the dataset
                3. Include budget ranges, startup costs, and detailed information from the dataset
                4. Be encouraging and supportive in your tone
                5. Use emojis to make it more engaging
                6. Focus on practical, actionable advice for Musanze, Rwanda
                7. If the dataset contains specific business types, mention them with details
                
                Provide comprehensive business advice based on the local dataset and context.
                """
            
            response = self.model.generate_content(prompt)
            return response.text
            
        except Exception as e:
            return f"I apologize, but I'm having trouble processing your request right now. Please try again or rephrase your question. Error: {str(e)}"
    
    def _classify_message_type(self, message: str) -> str:
        """Classify the type of message"""
        message_lower = message.lower()
        
        # Personal/greeting messages
        personal_keywords = [
            'love', 'like', 'hate', 'feel', 'emotion', 'personal', 'private',
            'hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening',
            'thank you', 'thanks', 'bye', 'goodbye', 'see you', 'nice to meet',
            'how are you', 'what\'s up', 'how do you do', 'pleasure', 'nice day'
        ]
        
        # Business-related keywords
        business_keywords = [
            'business', 'startup', 'entrepreneur', 'company', 'investment',
            'musanze', 'rwanda', 'restaurant', 'hotel', 'tourism', 'farming',
            'coffee', 'shop', 'store', 'guide', 'transport', 'market', 'plan',
            'cost', 'revenue', 'profit', 'customer', 'service', 'product'
        ]
        
        if any(keyword in message_lower for keyword in personal_keywords):
            return "personal_greeting"
        elif any(keyword in message_lower for keyword in business_keywords):
            return "business_related"
        else:
            return "general_question"
    
    def is_business_related(self, message: str) -> bool:
        """Check if message is business-related"""
        business_keywords = [
            'business', 'startup', 'entrepreneur', 'company', 'investment', 'revenue',
            'profit', 'market', 'customer', 'product', 'service', 'plan', 'strategy',
            'funding', 'loan', 'capital', 'musanze', 'rwanda', 'restaurant', 'hotel',
            'tourism', 'farming', 'coffee', 'shop', 'store', 'guide', 'transport'
        ]
        
        message_lower = message.lower()
        return any(keyword in message_lower for keyword in business_keywords)
