<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

session_start();

try {
    // Get user message
    $input = isset($_POST['message']) ? trim($_POST['message']) : '';
    $session_id = isset($_POST['session_id']) ? $_POST['session_id'] : uniqid('chat_');
    
    if (empty($input)) {
        echo json_encode(['success' => false, 'error' => 'Empty message']);
        exit;
    }
    
    // Store session ID and conversation history
    if (!isset($_SESSION['chat_session'])) {
        $_SESSION['chat_session'] = $session_id;
        $_SESSION['chat_history'] = [];
    }
    
    // Add to conversation history
    $_SESSION['chat_history'][] = ['role' => 'user', 'content' => $input];
    
    // Route to RAG Chatbot API (LangChain + Chroma + Hugging Face)
    $bot_response = callRAGAPI($input, $session_id);
    
    // Add bot response to history
    $_SESSION['chat_history'][] = ['role' => 'bot', 'content' => $bot_response['message']];
    
    // Keep conversation history to last 10 exchanges
    if (count($_SESSION['chat_history']) > 20) {
        $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -20);
    }
    
    echo json_encode([
        'success' => true,
        'session_id' => $session_id,
        'bot_response' => $bot_response['message'],
        'intent' => $bot_response['intent'] ?? 'unknown',
        'confidence' => $bot_response['confidence'] ?? 'N/A',
        'data' => $bot_response['data'] ?? []
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

/**
 * Call RAG Chatbot API (localhost:5000)
 * Uses LangChain + Chroma + Local Hugging Face model
 * Semantic search + Natural language generation
 */
function callRAGAPI($message, $session_id) {
    $api_url = 'http://localhost:5000/api/chat';
    
    try {
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'message' => $message
        ]));
        // Increased timeout from 45s to 60s to handle slower LLM responses
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $data = json_decode($response, true);
            
            if (isset($data['success']) && $data['success'] === true) {
                return [
                    'message' => $data['message'] ?? 'No response',
                    'intent' => $data['intent'] ?? 'unknown',
                    'confidence' => isset($data['confidence']) ? round($data['confidence'] * 100) . '%' : 'N/A',
                    'data' => [
                        'sources' => $data['sources'] ?? [],
                        'method' => $data['intent'] ?? 'unknown'
                    ]
                ];
            }
        }
    } catch (Exception $e) {
        error_log('RAG API error: ' . $e->getMessage());
    }
    return [
        'message' => 'The chatbot service is temporarily unavailable. Please try again later.',
        'intent' => 'error',
        'confidence' => 'N/A',
        'data' => []
    ];
}
function detectIntentLocal($input) {
    $input_lower = strtolower($input);
    
    $intents = [
        'how_to_instructions' => ['how to', 'how do i', 'how can i', 'steps to', 'procedure', 'guide'],
        'what_is_definition' => ['what is', 'what are', 'tell me about', 'explain', 'definition', 'describe'],
        'where_location' => ['where can i', 'where do i', 'where is', 'location', 'find'],
        'policy_rules' => ['policy', 'rule', 'guideline', 'regulation', 'requirement'],
        'benefits_compensation' => ['benefit', 'compensation', 'salary', 'allowance', 'pay', 'wage'],
        'leave_vacation' => ['leave', 'vacation', 'days off', 'time off', 'absent', 'holiday'],
        'training_development' => ['training', 'course', 'skill', 'development', 'learn', 'certification'],
        'recruitment_hiring' => ['hire', 'recruit', 'job', 'position', 'apply', 'interview'],
        'support_contact' => ['contact', 'reach', 'support', 'help', 'speak', 'email'],
        'general_information' => ['information', 'help', 'assist', 'tell', 'know']
    ];
    
    $scores = [];
    foreach ($intents as $intent_type => $keywords) {
        $score = 0;
        foreach ($keywords as $keyword) {
            if (strpos($input_lower, $keyword) !== false) {
                $score += 1;
            }
        }
        if ($score > 0) {
            $scores[$intent_type] = $score;
        }
    }
    
    return !empty($scores) ? array_key_first(array_reverse($scores)) : 'general_information';
}

// Advanced Entity Extraction with semantic understanding
function extractEntitiesAdvanced($input) {
    $input_lower = strtolower($input);
    $entities = [];
    
    $entity_map = [
        'payroll' => ['salary', 'pay', 'payroll', 'payment', 'wage', 'income', 'compensation'],
        'leave' => ['leave', 'vacation', 'off', 'absent', 'day off', 'holiday', 'time off'],
        'training' => ['training', 'course', 'learning', 'develop', 'skill', 'certification', 'workshop'],
        'recruitment' => ['hire', 'recruit', 'job', 'position', 'apply', 'career', 'opening'],
        'attendance' => ['attendance', 'present', 'absent', 'time', 'clock', 'check-in'],
        'performance' => ['performance', 'evaluation', 'review', 'appraisal', 'rating'],
        'benefits' => ['benefit', 'insurance', 'health', 'allowance', 'perks'],
        'policies' => ['policy', 'rule', 'guideline', 'regulation', 'compliance'],
        'relations' => ['relation', 'employee', 'management', 'issue', 'conflict', 'dispute'],
        'department' => ['hr', 'human resources', 'finance', 'it', 'operations', 'marketing', 'sales']
    ];
    
    foreach ($entity_map as $entity => $words) {
        foreach ($words as $word) {
            if (strpos($input_lower, $word) !== false) {
                $entities[$entity] = calculateRelevanceScore($input_lower, $word);
                break;
            }
        }
    }
    
    // Sort by relevance
    arsort($entities);
    return array_slice(array_keys($entities), 0, 3); // Return top 3 entities
}

function calculateRelevanceScore($text, $term) {
    $pos = strpos($text, $term);
    $length = strlen($term);
    $textLength = strlen($text);
    
    // Score based on term position and length match
    $positionScore = 1 - ($pos / $textLength);
    $lengthScore = $length / 10;
    
    return ($positionScore + $lengthScore) / 2;
}

// Advanced Article Search - searches ALL articles in database
function searchArticlesAdvanced($conn, $input, $intent, $entities) {
    $responses = [];
    $input_lower = strtolower($input);
    
    // Get ALL articles from database (no limit)
    $fetch_sql = "SELECT a.article_id, a.title, a.category, a.content 
                  FROM articles a 
                  ORDER BY a.article_id DESC";
    $fetch_stmt = $conn->prepare($fetch_sql);
    $all_articles = [];
    
    if ($fetch_stmt && $fetch_stmt->execute()) {
        $result = $fetch_stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $all_articles[] = $row;
            }
        }
        $fetch_stmt->close();
    }
    
    // Extract all keywords from user input (even short ones)
    $input_words = array_filter(explode(' ', $input_lower), function($term) {
        return strlen(trim($term)) > 1;
    });
    
    // Score ALL articles based on relevance
    $scored_articles = [];
    
    foreach ($all_articles as $article) {
        $title_lower = strtolower($article['title']);
        $category_lower = strtolower($article['category']);
        $content_lower = strtolower($article['content']);
        
        $score = 0;
        $title_matches = 0;
        $content_matches = 0;
        
        // Check each word from user input
        foreach ($input_words as $word) {
            $word = trim($word);
            if (empty($word)) continue;
            
            // Check title (highest weight)
            if (strpos($title_lower, $word) !== false) {
                $score += 20;
                $title_matches++;
            }
            
            // Check category (medium weight)
            if (strpos($category_lower, $word) !== false) {
                $score += 10;
            }
            
            // Check content (lower weight)
            if (strpos($content_lower, $word) !== false) {
                $score += 3;
                $content_matches++;
            }
        }
        
        // Bonus for multiple matches
        if ($title_matches > 1) {
            $score += 15;
        }
        if ($content_matches > 2) {
            $score += 5;
        }
        
        // Bonus if any entity is in title
        foreach ($entities as $entity) {
            if (strpos($title_lower, strtolower($entity)) !== false) {
                $score += 25;
            }
        }
        
        // Include all articles with any matching score
        if ($score > 0) {
            $scored_articles[] = [
                'article' => $article,
                'score' => $score
            ];
        }
    }
    
    // Sort by relevance score (highest first)
    usort($scored_articles, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });
    
    // Format top results
    foreach (array_slice($scored_articles, 0, 5) as $item) {
        $article = $item['article'];
        $content = strip_tags($article['content'] ?? '');
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
        $content = trim(preg_replace('/\s+/', ' ', $content));
        
        $responses[] = [
            'type' => 'article',
            'title' => htmlspecialchars($article['title'] ?? 'Untitled'),
            'category' => htmlspecialchars($article['category'] ?? 'Uncategorized'),
            'content' => substr($content, 0, 150),
            'id' => $article['article_id'],
            'relevance' => $item['score']
        ];
    }
    
    return $responses;
}



// Advanced Response Generation with Context
function generateAdvancedResponse($intent, $responses, $input, $entities) {
    $bot_response = '';
    
    // Personalize based on intent and entities
    $entity_context = !empty($entities) ? ' about ' . implode(', ', $entities) : '';
    
    if (empty($responses)) {
        // Context-aware helpful responses
        $responses_map = [
            'how_to_instructions' => "I couldn't find specific instructions on that topic. Could you be more specific? For example: 'How to apply for leave?' or 'How to submit a ticket?'",
            'what_is_definition' => "I don't have information about that specific topic. Try searching for: 'training programs', 'leave policy', 'benefits', or 'recruitment process'.",
            'policy_rules' => "I couldn't find the specific policy. Try asking about: 'attendance policy', 'leave policy', 'training guidelines', or 'company rules'.",
            'support_contact' => "For support, you can submit a ticket through the 'Submit a Ticket' button, or I can help answer questions about HR policies and procedures.",
            'where_location' => "Try searching for specific topics like 'leave', 'training', 'recruitment', or 'benefits'.",
            'general_information' => "I couldn't find matching articles. Please try rephrasing your question or browsing our categories."
        ];
        
        $bot_response = $responses_map[$intent] ?? $responses_map['general_information'];
    } else {
        // Context-aware introduction messages
        $intro_map = [
            'how_to_instructions' => "Here's what I found to help you with that:",
            'what_is_definition' => "Here's the information about that topic:",
            'policy_rules' => "Here are the relevant policies and guidelines:",
            'benefits_compensation' => "Here's information about benefits and compensation:",
            'leave_vacation' => "Here's information about leave and time off:",
            'training_development' => "Here are training and development resources:",
            'recruitment_hiring' => "Here's information about recruitment and hiring:",
            'general_information' => "I found relevant information for you:"
        ];
        
        $intro = $intro_map[$intent] ?? "I found the following relevant information:";
        $bot_response = $intro . "\n\n";
        
        foreach ($responses as $idx => $resp) {
            $bot_response .= ($idx + 1) . ". " . $resp['title'] . "\n";
            $bot_response .= "📁 " . $resp['category'] . "\n";
            $bot_response .= $resp['content'] . "\n\n";
        }
        
        $bot_response .= "Would you like more information about any of these topics?";
    }
    
    return $bot_response;
}
?>
