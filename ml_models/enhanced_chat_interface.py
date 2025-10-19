import sys
import json
import os
from typing import Dict, List, Optional

# Add current directory to path for imports
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from musanze_smart_model import MusanzeSmartModel
from gemini_integration import GeminiAI

class EnhancedChatInterface:
    def __init__(self):
        """Initialize enhanced chat interface with both local and Gemini AI"""
        self.local_model = MusanzeSmartModel()
        self.gemini_ai = GeminiAI()
        self.conversation_history = []
        
        # Train local model
        try:
            # Try different possible paths for the dataset
            dataset_paths = [
                '../datasets/musanze_dataset.csv',
                'datasets/musanze_dataset.csv',
                './datasets/musanze_dataset.csv'
            ]
            
            dataset_found = False
            for path in dataset_paths:
                if os.path.exists(path):
                    self.local_model.train(path)
                    dataset_found = True
                    break
            
            if not dataset_found:
                print("Warning: Musanze dataset not found, using Gemini AI only")
                
        except Exception as e:
            print(f"Warning: Could not train local model: {e}")
    
    def process_message(self, user_message: str, conversation_history: List[Dict] = None) -> Dict:
        """Process user message with enhanced AI capabilities"""
        try:
            # Add to conversation history
            if conversation_history:
                self.conversation_history = conversation_history
            else:
                self.conversation_history = []
            
            self.conversation_history.append({"role": "user", "content": user_message})
            
            # Check if message should go directly to Gemini AI
            if self._should_use_gemini_only(user_message):
                # Use Gemini AI directly for non-business or general questions
                context = self._build_context()
                response = self.gemini_ai.generate_response(user_message, context)
                source = "gemini_ai"
            else:
                # First, try local model for business-specific queries
                local_response = self._try_local_model(user_message)
                
                if local_response and self._is_comprehensive_response(local_response):
                    # Local model provided good response
                    response = local_response
                    source = "local_dataset"
                else:
                    # Use Gemini AI for enhanced response with local dataset context
                    context = self._build_context()
                    local_data = self._get_local_dataset_context()
                    response = self.gemini_ai.generate_response(user_message, context, local_data)
                    source = "gemini_ai_enhanced"
            
            # Add response to history
            self.conversation_history.append({"role": "assistant", "content": response})
            
            return {
                "response": response,
                "source": source,
                "success": True,
                "conversation_history": self.conversation_history
            }
            
        except Exception as e:
            return {
                "response": f"I apologize, but I'm having trouble processing your request right now. Please try again or rephrase your question.",
                "error": str(e),
                "success": False
            }
    
    def _try_local_model(self, message: str) -> Optional[str]:
        """Try to get response from local model first"""
        try:
            return self.local_model.predict(message)
        except Exception as e:
            print(f"Local model error: {e}")
            return None
    
    def _is_comprehensive_response(self, response: str) -> bool:
        """Check if local response is comprehensive enough"""
        # If response is too short or generic, use Gemini
        if len(response) < 50:
            return False
        
        # If response contains error messages, use Gemini
        if "error" in response.lower() or "not found" in response.lower():
            return False
            
        return True
    
    def _should_use_gemini_only(self, message: str) -> bool:
        """Determine if message should go directly to Gemini AI"""
        message_lower = message.lower()
        
        # Strong business-related keywords - ALWAYS try local dataset first
        strong_business_keywords = [
            'business opportunities', 'business ideas', 'start a business', 'entrepreneurship',
            'business plan', 'startup costs', 'investment', 'funding', 'revenue', 'profit',
            'musanze', 'rwanda', 'restaurant', 'hotel', 'tourism', 'farming', 'coffee',
            'shop', 'store', 'guide', 'transport', 'market', 'lodge', 'guesthouse',
            'eco-lodge', 'hiking', 'volcano', 'gorilla', 'souvenir', 'local transport',
            'food processing', 'organic farming', 'internet cafe', 'mountain hiking',
            'businesses', 'business', 'startup cost', 'startup', 'costs', 'rwf', 'currency',
            'require', 'budget', 'amount', 'price', 'expensive', 'cheap', 'affordable',
            'eco-lodges', 'ecolodges', 'eco lodge', 'eco lodges', 'tourism', 'hospitality',
            'mountain hiking tours', 'volcano trekking', 'local guide services', 'souvenir shop',
            'local restaurant', 'guesthouse', 'food processing', 'coffee processing', 'organic farming'
        ]
        
        # If message contains strong business keywords, try local dataset first
        if any(keyword in message_lower for keyword in strong_business_keywords):
            return False
        
        # Non-business related messages - go directly to Gemini
        personal_keywords = [
            'love', 'like', 'hate', 'feel', 'emotion', 'personal', 'private',
            'hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening',
            'thank you', 'thanks', 'bye', 'goodbye', 'see you', 'nice to meet',
            'how are you', 'what\'s up', 'how do you do', 'pleasure', 'nice day'
        ]
        
        # Check for personal/greeting messages
        if any(keyword in message_lower for keyword in personal_keywords):
            return True
            
        # General questions without business context - go to Gemini
        general_keywords = [
            'what is', 'how does', 'explain', 'tell me about', 'define',
            'weather', 'time', 'date', 'news', 'world', 'global', 'international'
        ]
        
        if any(keyword in message_lower for keyword in general_keywords):
            # If it's a general question without business context, use Gemini
            business_context = any(word in message_lower for word in [
                'business', 'startup', 'entrepreneur', 'company', 'investment',
                'musanze', 'rwanda', 'restaurant', 'hotel', 'tourism', 'farming',
                'coffee', 'shop', 'store', 'guide', 'transport', 'market'
            ])
            if not business_context:
                return True
        
        # Default: try local dataset first for any other queries
        return False
    
    def _build_context(self) -> str:
        """Build context from conversation history"""
        context = "Previous conversation:\n"
        for msg in self.conversation_history[-6:]:  # Last 6 messages
            role = "User" if msg["role"] == "user" else "Assistant"
            context += f"{role}: {msg['content']}\n"
        
        context += "\nYou are helping entrepreneurs in Musanze, Rwanda with business advice."
        return context
    
    def _get_local_dataset_context(self) -> str:
        """Get relevant context from local dataset"""
        try:
            # Get business opportunities from local model
            if hasattr(self.local_model, 'get_businesses_by_budget'):
                # Get businesses for different budget ranges
                low_budget = self.local_model.get_businesses_by_budget('low')
                medium_budget = self.local_model.get_businesses_by_budget('medium')
                high_budget = self.local_model.get_businesses_by_budget('high')
                
                context = f"""
                AVAILABLE BUSINESS OPPORTUNITIES IN MUSANZE, RWANDA:
                
                LOW BUDGET OPPORTUNITIES (Under $5,000):
                {low_budget}
                
                MEDIUM BUDGET OPPORTUNITIES ($5,000 - $50,000):
                {medium_budget}
                
                HIGH BUDGET OPPORTUNITIES (Over $50,000):
                {high_budget}
                
                These are specific business opportunities available in Musanze, Rwanda with detailed information about startup costs, requirements, and potential returns.
                """
                return context
            else:
                return "Local Musanze business dataset is available with specific business opportunities, startup costs, and requirements for entrepreneurs in Musanze, Rwanda."
        except Exception as e:
            return "Local Musanze business dataset contains specific business opportunities for entrepreneurs in Musanze, Rwanda."
