#!/usr/bin/env python3
"""
InnoStart ML API Integration Script
Called by PHP to generate enhanced responses
"""

import sys
import json
import os
from response_generator import BusinessResponseGenerator

def main():
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'No message provided'}))
        return
    
    message = sys.argv[1]
    context = {}
    
    if len(sys.argv) > 2:
        try:
            context = json.loads(sys.argv[2])
        except:
            context = {}
    
    try:
        # Initialize response generator
        generator = BusinessResponseGenerator()
        
        # Generate response
        response = generator.generate_response(message, context)
        
        # Output JSON response
        print(json.dumps(response))
        
    except Exception as e:
        print(json.dumps({
            'error': 'AI processing failed',
            'message': str(e),
            'response': 'I apologize, but I encountered an error processing your request. Please try again.',
            'category': 'general',
            'confidence': 0.0,
            'enhanced': False
        }))

if __name__ == "__main__":
    main()










