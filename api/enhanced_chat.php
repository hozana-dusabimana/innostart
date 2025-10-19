<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

class EnhancedChatAPI {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function handleRequest() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['message'])) {
                throw new Exception('Invalid request format');
            }
            
            $message = trim($input['message']);
            $conversation_history = $input['history'] ?? [];
            
            if (empty($message)) {
                throw new Exception('Message cannot be empty');
            }
            
            // Save user message to database
            $this->saveMessage($message, 'user');
            
            // Get AI response using enhanced chat interface
            $response = $this->getAIResponse($message, $conversation_history);
            
            // Save AI response to database
            $this->saveMessage($response['response'], 'assistant');
            
            return [
                'success' => true,
                'response' => $response['response'],
                'source' => $response['source'] ?? 'enhanced_ai',
                'conversation_history' => $response['conversation_history'] ?? []
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response' => 'I apologize, but I encountered an error. Please try again.'
            ];
        }
    }
    
    private function getAIResponse($message, $conversation_history) {
        // Prepare data for Python script
        $data = [
            'message' => $message,
            'conversation_history' => $conversation_history
        ];
        
        // Call Python enhanced chat interface
        $python_script = 'ml_models/enhanced_chat_cli.py';
        
        // Check if Python script exists
        if (!file_exists($python_script)) {
            error_log("Python script not found: " . $python_script);
            return [
                'response' => $this->generateFallbackResponse($message),
                'source' => 'fallback'
            ];
        }
        
        $command = "python " . escapeshellarg($python_script) . " " . escapeshellarg(json_encode($data));
        
        $output = shell_exec($command . ' 2>&1');
        
        if ($output) {
            // Log the output for debugging
            error_log("Enhanced chat Python output: " . $output);
            
            $result = json_decode(trim($output), true);
            if ($result && isset($result['response'])) {
                return $result;
            } else {
                error_log("Failed to parse enhanced chat Python output as JSON: " . $output);
            }
        } else {
            error_log("No output from enhanced chat Python script");
        }
        
        // Fallback to basic response
        return [
            'response' => $this->generateFallbackResponse($message),
            'source' => 'fallback'
        ];
    }
    
    private function generateFallbackResponse($message) {
        $message_lower = strtolower($message);
        
        // Check for greetings
        if (preg_match('/\b(hello|hi|hey|good morning|good afternoon|good evening)\b/', $message_lower)) {
            return "Hello! I'm your AI business assistant for Musanze, Rwanda. I can help you with business opportunities, startup costs, and planning. What would you like to know about starting a business in Musanze?";
        }
        
        // Check for business-related queries
        if (preg_match('/\b(business|startup|entrepreneur|company|investment)\b/', $message_lower)) {
            return "Great! I can help you explore business opportunities in Musanze, Rwanda. Here are some popular sectors:\n\n🏔️ Tourism & Hospitality: Eco-lodges, mountain hiking tours\n🌱 Agriculture: Coffee processing, organic farming\n🚗 Services: Local transport, souvenir shops\n🏪 Retail: Traditional crafts, gift shops\n\nWhat specific type of business interests you?";
        }
        
        // Default response
        return "I'm here to help you with business advice for Musanze, Rwanda. You can ask me about:\n\n• Business opportunities and ideas\n• Startup costs and investment requirements\n• Business planning and strategy\n• Market research and analysis\n\nWhat would you like to know?";
    }
    
    private function saveMessage($message, $role) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO chat_messages (conversation_id, role, content, created_at) 
                VALUES (1, ?, ?, NOW())
            ");
            $stmt->execute([$role, $message]);
        } catch (Exception $e) {
            // Log error but don't fail the request
            error_log("Failed to save message: " . $e->getMessage());
        }
    }
}

// Handle the request
$api = new EnhancedChatAPI();
$response = $api->handleRequest();
echo json_encode($response);
?>
