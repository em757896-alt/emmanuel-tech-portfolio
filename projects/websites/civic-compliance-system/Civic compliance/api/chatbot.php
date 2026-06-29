<?php
/**
 * Chatbot API Endpoint
 * Handles Q&A requests using rule-based knowledge base
 * PBO Compliance Platform - CRECO Kenya
 * 
 * DB: if0_42280606_if0_42280606_
 * Host: sql303.infinityfree.com
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';

// Rate limiting - simple session-based
session_start();
$_SESSION['chat_requests'] = ($_SESSION['chat_requests'] ?? 0) + 1;
if ($_SESSION['chat_requests'] > 100) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again later.']);
    exit();
}

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

// ── POST: Handle chat message ─────────────────────────────
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['message'])) {
        echo json_encode(['success' => false, 'message' => 'Message is required']);
        exit();
    }
    
    $userMessage = sanitize(trim($input['message']));
    $lang = in_array($input['lang'] ?? 'en', ['en', 'sw']) ? $input['lang'] : 'en';
    $sessionId = sanitize($input['session_id'] ?? session_id());
    
    // Truncate to 500 chars for safety
    $userMessage = mb_substr($userMessage, 0, 500);
    
    // Search knowledge base
    $result = searchKnowledgeBase($db, $userMessage, $lang);
    
    // Determine user ID if logged in
    $userId = null;
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    // Log conversation
    $conversationId = $db->insert('chatbot_conversations', [
        'session_id'     => $sessionId,
        'user_id'        => $userId,
        'user_message'   => $userMessage,
        'bot_response'   => $result['answer'],
        'kb_item_id'     => $result['kb_id'],
        'confidence'     => $result['confidence'],
        'language'       => $lang,
        'ip_address'     => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);
    
    // Update KB usage count
    if ($result['kb_id']) {
        $db->query(
            "UPDATE chatbot_knowledge_base SET usage_count = usage_count + 1, last_used = NOW() WHERE id = :id",
            ['id' => $result['kb_id']]
        );
    }
    
    echo json_encode([
        'success'         => true,
        'response'        => $result['answer'],
        'source'          => $result['source'],
        'confidence'      => $result['confidence'],
        'conversation_id' => $conversationId,
        'has_answer'      => $result['found'],
    ]);
    exit();
}

// ── PATCH: Handle feedback ────────────────────────────────
if ($method === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || empty($input['conversation_id'])) {
        echo json_encode(['success' => false]);
        exit();
    }
    
    $convId = (int)$input['conversation_id'];
    $feedback = sanitize($input['feedback'] ?? '');
    $note = sanitize($input['feedback_note'] ?? '');
    $flagged = $feedback === 'flagged' ? 1 : 0;
    
    $db->update('chatbot_conversations',
        [
            'feedback'           => in_array($feedback, ['helpful','not_helpful','flagged']) ? $feedback : null,
            'feedback_note'      => $note ?: null,
            'flagged_for_review' => $flagged,
        ],
        'id = :id',
        ['id' => $convId]
    );
    
    echo json_encode(['success' => true]);
    exit();
}

// ── Knowledge Base Search Function ───────────────────────
function searchKnowledgeBase(Database $db, string $query, string $lang): array {
    $query = strtolower(trim($query));
    $words = array_filter(explode(' ', $query), fn($w) => strlen($w) > 3);
    
    $noAnswerMsg = $lang === 'sw'
        ? "Samahani, sijapata jibu la swali lako katika maktaba ya PBO Act 2013. Tafadhali:\n\n1) Jaribu maneno tofauti\n2) Tazama sehemu ya Maswali Yanayoulizwa Mara Kwa Mara\n3) Wasiliana na timu ya CRECO Kenya kwa msaada zaidi\n\n⚠️ Kumbuka: Majibu yetu hayawakilishi ushauri wa kisheria."
        : "I'm sorry, I couldn't find a specific answer to your question in my PBO Act 2013 knowledge base. Please:\n\n1) Try rephrasing your question\n2) Browse our **FAQs section** for common questions\n3) Contact CRECO Kenya for additional support\n4) Visit the **Knowledge Hub** for comprehensive legal summaries\n\n⚠️ Remember: Responses do not constitute legal advice.";
    
    if (empty($words)) {
        return ['answer' => $noAnswerMsg, 'source' => null, 'confidence' => 0, 'kb_id' => null, 'found' => false];
    }
    
    // Try full-text search first
    try {
        $searchTerms = implode(' ', $words);
        $result = $db->fetchOne(
            "SELECT *, MATCH(question_pattern, keywords, answer_en) AGAINST(:q IN NATURAL LANGUAGE MODE) AS score
             FROM chatbot_knowledge_base 
             WHERE is_active = 1 
             AND MATCH(question_pattern, keywords, answer_en) AGAINST(:q2 IN NATURAL LANGUAGE MODE)
             ORDER BY score DESC LIMIT 1",
            ['q' => $searchTerms, 'q2' => $searchTerms]
        );
        
        if ($result && $result['score'] > 0) {
            $answer = $lang === 'sw' && !empty($result['answer_sw'])
                ? $result['answer_sw']
                : $result['answer_en'];
            
            if ($result['pbo_act_section']) {
                $answer .= "\n\n📋 **PBO Act Reference:** " . $result['pbo_act_section'];
            }
            
            $answer .= "\n\n⚠️ *This information is for general guidance only and does not constitute legal advice.*";
            
            return [
                'answer'     => $answer,
                'source'     => $result['pbo_act_section'] ?? 'PBO Act 2013',
                'confidence' => min(1, $result['score'] / 10),
                'kb_id'      => $result['id'],
                'found'      => true,
            ];
        }
    } catch (Exception $e) {
        error_log("Chatbot DB error: " . $e->getMessage());
    }
    
    // Fallback: keyword matching
    $keywordMatches = [
        'register|registration|kusajili|sajili'              => 'registration',
        'compliance|comply|annual|returns|ripoti|kuripoti'   => 'compliance',
        'governance|board|directors|trustees|utawala'        => 'governance',
        'fine|penalty|penalties|adhabu|faini'                => 'penalties',
        'finance|financial|audit|fedha|ukaguzi'              => 'finance',
        'rights|right|haki|protection|ulinzi'                => 'rights',
        'deregister|cancel|revoke|kufutwa'                   => 'deregistration',
        'foreign|international|nje ya nchi'                  => 'foreign_pbo',
    ];
    
    $matchedCategory = null;
    foreach ($keywordMatches as $pattern => $category) {
        if (preg_match("/$pattern/i", $query)) {
            $matchedCategory = $category;
            break;
        }
    }
    
    if ($matchedCategory) {
        $kb = $db->fetchOne(
            "SELECT * FROM chatbot_knowledge_base WHERE category = :cat AND is_active = 1 ORDER BY usage_count DESC LIMIT 1",
            ['cat' => $matchedCategory]
        );
        
        if ($kb) {
            $answer = $lang === 'sw' && !empty($kb['answer_sw']) ? $kb['answer_sw'] : $kb['answer_en'];
            $answer .= "\n\n⚠️ *This information is for general guidance only and does not constitute legal advice.*";
            return [
                'answer'     => $answer,
                'source'     => $kb['pbo_act_section'] ?? 'PBO Act 2013',
                'confidence' => 0.5,
                'kb_id'      => $kb['id'],
                'found'      => true,
            ];
        }
    }
    
    return ['answer' => $noAnswerMsg, 'source' => null, 'confidence' => 0, 'kb_id' => null, 'found' => false];
}