#!/usr/bin/env python3
"""
Test script for the enhanced chat system
This script tests both the local model and Gemini AI integration
"""

import sys
import os
import json

# Add current directory to path for imports
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

def test_enhanced_chat():
    """Test the enhanced chat interface"""
    try:
        from ml_models.enhanced_chat_interface import EnhancedChatInterface
        
        print("🧪 Testing Enhanced Chat Interface...")
        print("=" * 50)
        
        # Initialize the chat interface
        chat = EnhancedChatInterface()
        print("✅ Enhanced chat interface initialized")
        
        # Test messages - different types
        test_messages = [
            # Business-related messages
            "Hello, I want to start a business in Musanze",
            "What are the startup costs for a restaurant?",
            "How do I create a business plan?",
            "What legal requirements do I need to know?",
            "Tell me about tourism opportunities in Musanze",
            
            # Personal/greeting messages (should go to Gemini)
            "I love you",
            "Hello, how are you?",
            "Thank you for your help",
            "Good morning!",
            
            # General questions (should go to Gemini)
            "What is artificial intelligence?",
            "How does the weather work?",
            "Tell me about space exploration"
        ]
        
        for i, message in enumerate(test_messages, 1):
            print(f"\n📝 Test {i}: {message}")
            print("-" * 30)
            
            try:
                result = chat.process_message(message)
                
                if result['success']:
                    print(f"✅ Response ({result['source']}):")
                    print(f"   {result['response'][:100]}...")
                else:
                    print(f"❌ Error: {result.get('error', 'Unknown error')}")
                    
            except Exception as e:
                print(f"❌ Test failed: {e}")
        
        print("\n" + "=" * 50)
        print("🎉 Enhanced chat testing completed!")
        
    except ImportError as e:
        print(f"❌ Import error: {e}")
        print("Please make sure all dependencies are installed.")
    except Exception as e:
        print(f"❌ Test error: {e}")

def test_gemini_integration():
    """Test Gemini AI integration separately"""
    try:
        from ml_models.gemini_integration import GeminiAI
        
        print("\n🤖 Testing Gemini AI Integration...")
        print("=" * 50)
        
        # Initialize Gemini AI
        gemini = GeminiAI()
        print("✅ Gemini AI initialized")
        
        # Test different types of messages
        test_messages = [
            "Hello, I'm interested in starting a coffee business in Musanze, Rwanda. Can you help me?",
            "I love you",
            "What is artificial intelligence?",
            "How are you today?"
        ]
        
        for i, test_message in enumerate(test_messages, 1):
            print(f"\n📝 Test {i}: {test_message}")
            print("-" * 30)
            
            response = gemini.generate_response(test_message)
            message_type = gemini._classify_message_type(test_message)
            
            print(f"✅ Message Type: {message_type}")
            print(f"✅ Gemini Response:")
            print(f"   {response[:200]}...")
        
        print("\n🎉 Gemini AI testing completed!")
        
    except Exception as e:
        print(f"❌ Gemini test error: {e}")

if __name__ == "__main__":
    print("🚀 InnoStart Enhanced Chat System Test")
    print("=" * 60)
    
    # Test Gemini integration first
    test_gemini_integration()
    
    # Test enhanced chat interface
    test_enhanced_chat()
    
    print("\n" + "=" * 60)
    print("✨ All tests completed!")
