#!/usr/bin/env python3
"""
Main Training and Deployment Script for InnoStart ML System
Trains models, downloads datasets, and integrates with the application
"""

import os
import sys
import json
import logging
from typing import Dict, List
import argparse
from datetime import datetime

# Add current directory to path for imports
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from train_response_model import BusinessResponseTrainer
from response_generator import BusinessResponseGenerator
from kaggle_dataset_manager import KaggleDatasetManager
from enhanced_ai_integration import EnhancedAI

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('ml_models/training.log'),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

class InnoStartMLPipeline:
    """Complete ML pipeline for InnoStart"""
    
    def __init__(self):
        self.trainer = BusinessResponseTrainer()
        self.generator = BusinessResponseGenerator()
        self.dataset_manager = KaggleDatasetManager()
        self.enhanced_ai = EnhancedAI()
        
        # Training configuration
        self.config = {
            'model_path': 'ml_models/business_response_model.pkl',
            'training_data_path': 'training_data/combined_training_data.csv',
            'kaggle_data_path': 'training_data/kaggle_training_data.csv',
            'deployment_path': '../api/enhanced_chat.php'
        }
    
    def run_complete_pipeline(self, download_kaggle: bool = True, train_model: bool = True, 
                            deploy: bool = True) -> Dict:
        """Run the complete ML pipeline"""
        
        results = {
            'start_time': datetime.now().isoformat(),
            'steps_completed': [],
            'errors': [],
            'success': False
        }
        
        try:
            logger.info("Starting InnoStart ML Pipeline")
            
            # Step 1: Download Kaggle datasets (optional)
            if download_kaggle:
                logger.info("Step 1: Downloading Kaggle datasets...")
                try:
                    download_results = self.dataset_manager.download_business_datasets()
                    results['steps_completed'].append('kaggle_download')
                    results['kaggle_download'] = download_results
                    logger.info("✓ Kaggle datasets downloaded")
                except Exception as e:
                    logger.error(f"Error downloading Kaggle datasets: {e}")
                    results['errors'].append(f"Kaggle download: {str(e)}")
            
            # Step 2: Create training data
            logger.info("Step 2: Creating training data...")
            try:
                # Create base training data
                base_training_df = self.trainer.create_training_data()
                base_training_df.to_csv('training_data/base_training_data.csv', index=False)
                
                # Create Kaggle training data if available
                kaggle_training_df = self.dataset_manager.create_training_data_from_datasets()
                
                # Combine training data
                if not kaggle_training_df.empty:
                    combined_df = base_training_df.append(kaggle_training_df, ignore_index=True)
                    combined_df.to_csv(self.config['training_data_path'], index=False)
                    logger.info(f"✓ Combined training data created: {len(combined_df)} examples")
                else:
                    combined_df = base_training_df
                    combined_df.to_csv(self.config['training_data_path'], index=False)
                    logger.info(f"✓ Base training data created: {len(combined_df)} examples")
                
                results['steps_completed'].append('training_data_creation')
                results['training_data_size'] = len(combined_df)
                
            except Exception as e:
                logger.error(f"Error creating training data: {e}")
                results['errors'].append(f"Training data creation: {str(e)}")
                return results
            
            # Step 3: Train the model
            if train_model:
                logger.info("Step 3: Training the model...")
                try:
                    # Preprocess data
                    X, y = self.trainer.preprocess_data(combined_df)
                    
                    # Train model
                    self.trainer.train_model(X, y)
                    
                    # Save model
                    self.trainer.save_model(self.config['model_path'])
                    
                    results['steps_completed'].append('model_training')
                    logger.info("✓ Model trained and saved")
                    
                except Exception as e:
                    logger.error(f"Error training model: {e}")
                    results['errors'].append(f"Model training: {str(e)}")
                    return results
            
            # Step 4: Test the model
            logger.info("Step 4: Testing the model...")
            try:
                # Load the trained model
                self.generator.load_model(self.config['model_path'])
                
                # Test with sample questions
                test_questions = [
                    "How do I write a business plan?",
                    "What funding options are available?",
                    "How can I market my startup?",
                    "What legal structure should I choose?",
                    "How do I manage cash flow?"
                ]
                
                test_results = []
                for question in test_questions:
                    response = self.generator.generate_response(question)
                    test_results.append({
                        'question': question,
                        'category': response['category'],
                        'confidence': response['confidence']
                    })
                
                results['steps_completed'].append('model_testing')
                results['test_results'] = test_results
                logger.info("✓ Model tested successfully")
                
            except Exception as e:
                logger.error(f"Error testing model: {e}")
                results['errors'].append(f"Model testing: {str(e)}")
            
            # Step 5: Deploy to application
            if deploy:
                logger.info("Step 5: Deploying to application...")
                try:
                    self._deploy_to_application()
                    results['steps_completed'].append('deployment')
                    logger.info("✓ Model deployed to application")
                    
                except Exception as e:
                    logger.error(f"Error deploying model: {e}")
                    results['errors'].append(f"Deployment: {str(e)}")
            
            # Step 6: Create API integration
            logger.info("Step 6: Creating API integration...")
            try:
                self._create_api_integration()
                results['steps_completed'].append('api_integration')
                logger.info("✓ API integration created")
                
            except Exception as e:
                logger.error(f"Error creating API integration: {e}")
                results['errors'].append(f"API integration: {str(e)}")
            
            results['success'] = True
            results['end_time'] = datetime.now().isoformat()
            
            logger.info("✓ InnoStart ML Pipeline completed successfully!")
            
        except Exception as e:
            logger.error(f"Pipeline failed: {e}")
            results['errors'].append(f"Pipeline: {str(e)}")
            results['end_time'] = datetime.now().isoformat()
        
        return results
    
    def _deploy_to_application(self) -> None:
        """Deploy the trained model to the application"""
        
        # Create enhanced chat API
        enhanced_chat_content = '''<?php
/**
 * Enhanced Chat API with ML Model Integration
 * Provides tailored business responses using trained ML model
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit();
}

$message = trim($input['message']);
$context = $input['context'] ?? [];

// Enhanced AI response using Python ML model
function generateEnhancedResponse($message, $context) {
    // Prepare context for Python script
    $context_json = json_encode($context);
    
    // Call Python script for enhanced response
    $command = "python ml_models/enhanced_ai_integration.py " . 
               escapeshellarg($message) . " " . 
               escapeshellarg($context_json) . " 2>&1";
    
    $output = shell_exec($command);
    
    if ($output) {
        $response_data = json_decode($output, true);
        if ($response_data && isset($response_data['response'])) {
            return $response_data;
        }
    }
    
    // Fallback to original response
    return generateFallbackResponse($message);
}

// Fallback response function
function generateFallbackResponse($message) {
    $message_lower = strtolower($message);
    
    if (strpos($message_lower, 'business plan') !== false) {
        return [
            'response' => "I'd be happy to help you create a business plan! A good business plan should include: executive summary, company description, market analysis, organization structure, service/product line, marketing strategy, and financial projections. Would you like me to guide you through any specific section?",
            'category' => 'business_planning',
            'confidence' => 0.8,
            'enhanced' => false
        ];
    }
    
    if (strpos($message_lower, 'funding') !== false || strpos($message_lower, 'investment') !== false) {
        return [
            'response' => "There are several funding options for startups: bootstrapping, angel investors, venture capital, bank loans, crowdfunding, and government grants. The best option depends on your business type, stage, and funding needs. What's your current funding situation?",
            'category' => 'funding',
            'confidence' => 0.8,
            'enhanced' => false
        ];
    }
    
    // Default response
    return [
        'response' => "That's a great question! Starting a business involves many considerations. Could you provide more details about your specific situation or the type of business you're planning?",
        'category' => 'general',
        'confidence' => 0.5,
        'enhanced' => false
    ];
}

try {
    $response = generateEnhancedResponse($message, $context);
    
    echo json_encode([
        'response' => $response['response'],
        'category' => $response['category'],
        'confidence' => $response['confidence'],
        'enhanced' => $response['enhanced'] ?? false,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => 'An error occurred while processing your request'
    ]);
}
?>'''
        
        # Write enhanced chat API
        with open(self.config['deployment_path'], 'w') as f:
            f.write(enhanced_chat_content)
        
        logger.info(f"Enhanced chat API created at {self.config['deployment_path']}")
    
    def _create_api_integration(self) -> None:
        """Create Python API integration script"""
        
        api_script_content = '''#!/usr/bin/env python3
"""
InnoStart ML API Integration Script
Called by PHP to generate enhanced responses
"""

import sys
import json
import os
from enhanced_ai_integration import EnhancedAI

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
        # Initialize enhanced AI
        ai = EnhancedAI()
        
        # Generate response
        response = ai.generate_enhanced_response(message, context)
        
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
'''
        
        # Write API integration script
        with open('ml_models/api_integration.py', 'w') as f:
            f.write(api_script_content)
        
        # Make it executable
        os.chmod('ml_models/api_integration.py', 0o755)
        
        logger.info("API integration script created")
    
    def generate_report(self, results: Dict) -> str:
        """Generate a training report"""
        
        report = f"""
# InnoStart ML Training Report

## Summary
- **Start Time**: {results.get('start_time', 'N/A')}
- **End Time**: {results.get('end_time', 'N/A')}
- **Success**: {'✓' if results['success'] else '✗'}
- **Steps Completed**: {len(results['steps_completed'])}
- **Errors**: {len(results['errors'])}

## Steps Completed
{chr(10).join([f"- {step}" for step in results['steps_completed']])}

## Training Data
- **Size**: {results.get('training_data_size', 'N/A')} examples

## Test Results
"""
        
        if 'test_results' in results:
            for test in results['test_results']:
                report += f"- **{test['question']}**: {test['category']} (confidence: {test['confidence']:.3f})\n"
        
        if results['errors']:
            report += "\n## Errors\n"
            for error in results['errors']:
                report += f"- {error}\n"
        
        report += f"""
## Next Steps
1. Test the enhanced chat API
2. Monitor response quality
3. Retrain model with more data if needed
4. Add more business categories

## Files Created
- `{self.config['model_path']}` - Trained ML model
- `{self.config['training_data_path']}` - Training data
- `{self.config['deployment_path']}` - Enhanced chat API
- `ml_models/api_integration.py` - Python API integration
"""
        
        return report

def main():
    """Main function"""
    parser = argparse.ArgumentParser(description='InnoStart ML Training Pipeline')
    parser.add_argument('--no-kaggle', action='store_true', help='Skip Kaggle dataset download')
    parser.add_argument('--no-train', action='store_true', help='Skip model training')
    parser.add_argument('--no-deploy', action='store_true', help='Skip deployment')
    parser.add_argument('--report', action='store_true', help='Generate training report')
    
    args = parser.parse_args()
    
    # Create pipeline
    pipeline = InnoStartMLPipeline()
    
    # Run pipeline
    results = pipeline.run_complete_pipeline(
        download_kaggle=not args.no_kaggle,
        train_model=not args.no_train,
        deploy=not args.no_deploy
    )
    
    # Generate report
    if args.report or results['success']:
        report = pipeline.generate_report(results)
        
        # Save report
        with open('ml_models/training_report.md', 'w') as f:
            f.write(report)
        
        print("\n" + "="*50)
        print("TRAINING REPORT")
        print("="*50)
        print(report)
        print("="*50)
        
        if results['success']:
            print("\n🎉 Training completed successfully!")
            print("📁 Check ml_models/training_report.md for detailed report")
        else:
            print("\n❌ Training completed with errors")
            print("📁 Check ml_models/training_report.md for details")
    
    return 0 if results['success'] else 1

if __name__ == "__main__":
    sys.exit(main())





