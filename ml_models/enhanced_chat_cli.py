#!/usr/bin/env python3
"""
Enhanced Chat CLI - Command line interface for the enhanced chat system
This script can be called from PHP to get AI responses
"""

import sys
import json
import os

# Add current directory to path for imports
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from enhanced_chat_interface import EnhancedChatInterface

def main():
    """Main function to handle command line arguments"""
    try:
        # Get input from command line arguments
        if len(sys.argv) < 2:
            print(json.dumps({
                "success": False,
                "error": "No input provided",
                "response": "Please provide a message to process."
            }))
            return
        
        # Parse input data - handle both JSON string and direct message
        input_arg = sys.argv[1]
        
        # Try to parse as JSON first
        try:
            input_data = json.loads(input_arg)
            message = input_data.get('message', '')
            conversation_history = input_data.get('conversation_history', [])
        except json.JSONDecodeError:
            # If not JSON, treat as direct message
            message = input_arg
            conversation_history = []
        
        if not message:
            print(json.dumps({
                "success": False,
                "error": "Empty message",
                "response": "Please provide a non-empty message."
            }))
            return
        
        # Initialize enhanced chat interface
        chat_interface = EnhancedChatInterface()
        
        # Process the message
        result = chat_interface.process_message(message, conversation_history)
        
        # Output result as JSON
        print(json.dumps(result))
        
    except Exception as e:
        # Handle any errors gracefully
        error_result = {
            "success": False,
            "error": str(e),
            "response": "I apologize, but I'm having trouble processing your request right now. Please try again or rephrase your question."
        }
        print(json.dumps(error_result))

if __name__ == "__main__":
    main()
