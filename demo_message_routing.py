#!/usr/bin/env python3
"""
Demo script showing how the enhanced chat system routes different message types
"""

import sys
import os

# Add current directory to path for imports
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

def demo_message_routing():
    """Demonstrate how different message types are routed"""
    try:
        from ml_models.enhanced_chat_interface import EnhancedChatInterface
        from ml_models.gemini_integration import GeminiAI
        
        print("🎯 Message Routing Demo")
        print("=" * 50)
        
        # Initialize components
        chat_interface = EnhancedChatInterface()
        gemini_ai = GeminiAI()
        
        # Test messages with expected routing
        test_cases = [
            {
                "message": "I love you",
                "expected_route": "Gemini AI (Personal/Greeting)",
                "description": "Personal message - should go to Gemini for friendly response"
            },
            {
                "message": "What is artificial intelligence?",
                "expected_route": "Gemini AI (General Question)",
                "description": "General question - should go to Gemini for comprehensive answer"
            },
            {
                "message": "I want to start a restaurant in Musanze",
                "expected_route": "Local Dataset (Business-specific)",
                "description": "Business question - should try local dataset first"
            },
            {
                "message": "Hello, how are you?",
                "expected_route": "Gemini AI (Personal/Greeting)",
                "description": "Greeting - should go to Gemini for friendly response"
            },
            {
                "message": "What are the startup costs for a coffee shop?",
                "expected_route": "Local Dataset (Business-specific)",
                "description": "Business question - should try local dataset first"
            }
        ]
        
        for i, test_case in enumerate(test_cases, 1):
            print(f"\n📝 Test Case {i}: {test_case['message']}")
            print(f"Expected: {test_case['expected_route']}")
            print(f"Description: {test_case['description']}")
            print("-" * 40)
            
            # Check routing decision
            should_use_gemini = chat_interface._should_use_gemini_only(test_case['message'])
            message_type = gemini_ai._classify_message_type(test_case['message'])
            
            print(f"✅ Routing Decision: {'Gemini AI' if should_use_gemini else 'Local Dataset First'}")
            print(f"✅ Message Type: {message_type}")
            
            # Get actual response
            result = chat_interface.process_message(test_case['message'])
            
            if result['success']:
                print(f"✅ Response Source: {result['source']}")
                print(f"✅ Response Preview: {result['response'][:100]}...")
            else:
                print(f"❌ Error: {result.get('error', 'Unknown error')}")
        
        print("\n" + "=" * 50)
        print("🎉 Message routing demo completed!")
        print("\nKey Benefits:")
        print("• Personal messages get friendly, appropriate responses")
        print("• General questions get comprehensive answers")
        print("• Business questions get local dataset + Gemini enhancement")
        print("• No more inappropriate business responses to personal messages")
        
    except Exception as e:
        print(f"❌ Demo error: {e}")

if __name__ == "__main__":
    demo_message_routing()



