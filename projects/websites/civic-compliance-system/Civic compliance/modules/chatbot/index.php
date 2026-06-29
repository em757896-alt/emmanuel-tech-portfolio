<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../config/auth.php';

$auth = new Auth();
$pageTitle = 'AI Legal Assistant - PBO Kenya';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        .chatbot-page { background: #f0f4f8; min-height: calc(100vh - 76px); padding: 2rem 0; }
        .chat-container { max-width: 900px; margin: 0 auto; }
        .chat-main { background: white; border-radius: 20px; box-shadow: 0 8px 40px rgba(0,0,0,.12); overflow: hidden; height: 75vh; display: flex; flex-direction: column; }
        .chat-main-header { background: linear-gradient(135deg, #1a56db, #1044a3); color: white; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 1rem; }
        .chat-main-messages { flex: 1; overflow-y: auto; padding: 1.5rem; background: #f8fafc; }
        .chat-main-input { padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background: white; }
        .chat-message { display: flex; gap: .75rem; margin-bottom: 1.25rem; animation: msgIn .3s ease; }
        @keyframes msgIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .chat-message.user { flex-direction: row-reverse; }
        .msg-avatar { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
        .msg-avatar.bot { background: #1a56db; color: white; }
        .msg-avatar.user { background: #059669; color: white; }
        .msg-content { max-width: 72%; }
        .msg-bubble-main { padding: 1rem 1.25rem; border-radius: 18px; font-size: .9rem; line-height: 1.6; }
        .chat-message.bot .msg-bubble-main { background: white; border: 1px solid #e5e7eb; border-bottom-left-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
        .chat-message.user .msg-bubble-main { background: #1a56db; color: white; border-bottom-right-radius: 4px; }
        .msg-meta { font-size: .72rem; color: #9ca3af; margin-top: .35rem; }
        .chat-message.user .msg-meta { text-align: right; }
        .source-tag { display: inline-flex; align-items: center; gap: .3rem; background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; font-size: .72rem; padding: .2rem .6rem; border-radius: 50px; margin-top: .5rem; }
        .feedback-btns { display: flex; gap: .5rem; margin-top: .75rem; }
        .feedback-btn { background: none; border: 1px solid #e5e7eb; border-radius: 50px; padding: .25rem .75rem; font-size: .75rem; cursor: pointer; transition: all .2s; display: flex; align-items: center; gap: .3rem; color: #6b7280; }
        .feedback-btn:hover { background: #f3f4f6; }
        .feedback-btn.helpful { color: #059669; border-color: #059669; }
        .feedback-btn.flag { color: #dc2626; border-color: #dc2626; }
        .disclaimer-banner { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: .75rem 1rem; font-size: .82rem; color: #92400e; margin-bottom: 1rem; }
        .suggestion-chips { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
        .chip { background: white; border: 1px solid #e5e7eb; border-radius: 50px; padding: .35rem .9rem; font-size: .8rem; cursor: pointer; transition: all .2s; white-space: nowrap; color: #374151; }
        .chip:hover { background: #eff6ff; border-color: #1a56db; color: #1a56db; }
        .input-area { display: flex; gap: .75rem; align-items: flex-end; }
        #messageInput { border-radius: 24px; padding: .75rem 1.25rem; resize: none; max-height: 120px; font-size: .9rem; border-color: #d1d5db; }
        #messageInput:focus { border-color: #1a56db; box-shadow: 0 0 0 3px rgba(26,86,219,.1); }
        .send-btn { width: 48px; height: 48px; border-radius: 50%; border: none; background: #1a56db; color: white; font-size: 1.1rem; cursor: pointer; transition: all .2s; flex-shrink: 0; }
        .send-btn:hover { background: #1044a3; transform: scale(1.05); }
        .sidebar-panel { background: white; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,.08); padding: 1.25rem; margin-bottom: 1rem; }
        .sidebar-panel h6 { font-weight: 700; margin-bottom: .75rem; font-size: .9rem; }
        .topic-item { display: flex; align-items: center; gap: .5rem; padding: .5rem; border-radius: 8px; cursor: pointer; font-size: .85rem; color: #374151; transition: all .2s; }
        .topic-item:hover { background: #eff6ff; color: #1a56db; }
        .stats-row { display: flex; gap: .5rem; margin-bottom: .5rem; }
        .stats-badge { flex: 1; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: .4rem; text-align: center; }
        .stats-badge .num { font-size: 1.1rem; font-weight: 700; color: #1a56db; }
        .stats-badge .lbl { font-size: .65rem; color: #6b7280; }
    </style>
</head>
<body>
<?php include '../../includes/navbar.php'; ?>

<div class="chatbot-page">
    <div class="container-fluid px-4">
        <div class="chat-container">
            <div class="text-center mb-4">
                <h2 class="fw-bold">
                    <i class="fas fa-robot text-primary me-2"></i>
                    PBO Legal Assistant
                </h2>
                <p class="text-muted">AI-powered answers about the PBO Act 2013 — trained exclusively on official materials</p>
            </div>
            
            <div class="row g-4">
                <!-- Main Chat -->
                <div class="col-lg-8">
                    <div class="chat-main">
                        <!-- Header -->
                        <div class="chat-main-header">
                            <div class="msg-avatar bot" style="width:48px;height:48px;font-size:1.4rem;">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="flex-grow-1">
                                <strong>PBO Legal Assistant</strong>
                                <div style="font-size:.8rem;opacity:.8;">
                                    <span id="statusDot" style="display:inline-block;width:8px;height:8px;background:#4ade80;border-radius:50%;margin-right:5px;"></span>
                                    Online | Trained on PBO Act 2013
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <!-- Language Toggle -->
                                <div class="btn-group btn-group-sm">
                                    <button id="btnEn" onclick="setLang('en')" class="btn btn-light active" title="English">EN</button>
                                    <button id="btnSw" onclick="setLang('sw')" class="btn btn-outline-light" title="Kiswahili">SW</button>
                                </div>
                                <!-- Clear Chat -->
                                <button onclick="clearChat()" class="btn btn-sm btn-outline-light" title="Clear conversation">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Messages -->
                        <div class="chat-main-messages" id="chatMessages">
                            <!-- Disclaimer -->
                            <div class="disclaimer-banner">
                                <strong><i class="fas fa-exclamation-triangle me-1"></i>Important Notice:</strong>
                                This AI assistant provides general information about the PBO Act 2013 based only on approved official materials. 
                                <strong>Responses do not constitute legal advice.</strong> 
                                For specific legal matters, consult a qualified advocate.
                            </div>
                            
                            <!-- Welcome Message -->
                            <div class="chat-message bot" id="welcomeMsg">
                                <div class="msg-avatar bot"><i class="fas fa-robot"></i></div>
                                <div class="msg-content">
                                    <div class="msg-bubble-main">
                                        <p><strong>Habari! Hello!</strong> 👋</p>
                                        <p>I'm the PBO Legal Assistant, here to help you understand the <strong>Public Benefit Organizations Act 2013</strong>.</p>
                                        <p>I can help you with:</p>
                                        <ul style="margin-bottom:.5rem;">
                                            <li>Registration requirements and procedures</li>
                                            <li>Compliance obligations and deadlines</li>
                                            <li>Governance and management standards</li>
                                            <li>Rights of PBOs under the Act</li>
                                            <li>Financial reporting requirements</li>
                                        </ul>
                                        <p class="mb-0"><em>What would you like to know?</em></p>
                                    </div>
                                    <div class="msg-meta"><i class="fas fa-clock me-1"></i>Just now · PBO Act 2013 Knowledge Base</div>
                                </div>
                            </div>
                            
                            <!-- Suggestion Chips -->
                            <div class="suggestion-chips" id="suggestionChips">
                                <span class="chip" onclick="askQuestion('How do I register a PBO in Kenya?')">
                                    <i class="fas fa-registered me-1 text-primary"></i>How to register a PBO?
                                </span>
                                <span class="chip" onclick="askQuestion('What are the annual compliance requirements?')">
                                    <i class="fas fa-calendar me-1 text-success"></i>Annual compliance
                                </span>
                                <span class="chip" onclick="askQuestion('What governance structures are required?')">
                                    <i class="fas fa-sitemap me-1 text-info"></i>Governance requirements
                                </span>
                                <span class="chip" onclick="askQuestion('What are the penalties for non-compliance?')">
                                    <i class="fas fa-gavel me-1 text-danger"></i>Non-compliance penalties
                                </span>
                                <span class="chip" onclick="askQuestion('Jinsi ya kusajili PBO nchini Kenya?')">
                                    <i class="fas fa-language me-1 text-warning"></i>Maswali kwa Kiswahili
                                </span>
                            </div>
                        </div>
                        
                        <!-- Input Area -->
                        <div class="chat-main-input">
                            <div class="input-area">
                                <textarea id="messageInput" 
                                          class="form-control" 
                                          rows="1"
                                          placeholder="Ask about the PBO Act 2013..."
                                          onkeydown="handleKeyDown(event)"
                                          oninput="autoResize(this)"
                                          maxlength="500"></textarea>
                                <button class="send-btn" onclick="sendMessage()" id="sendBtn" title="Send message">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Press Enter to send · Shift+Enter for new line
                                </small>
                                <small class="text-muted" id="charCount">0/500</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Session Stats -->
                    <div class="sidebar-panel">
                        <h6><i class="fas fa-chart-bar me-2 text-primary"></i>Session Stats</h6>
                        <div class="stats-row">
                            <div class="stats-badge">
                                <div class="num" id="msgCount">0</div>
                                <div class="lbl">Messages</div>
                            </div>
                            <div class="stats-badge">
                                <div class="num" id="accuracyRate">—</div>
                                <div class="lbl">Avg Confidence</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Topic Browser -->
                    <div class="sidebar-panel">
                        <h6><i class="fas fa-compass me-2 text-primary"></i>Browse Topics</h6>
                        <?php
                        $topics = [
                            ['icon'=>'registered','color'=>'primary','label'=>'PBO Registration','q'=>'What are the registration requirements for a PBO?'],
                            ['icon'=>'clipboard-check','color'=>'success','label'=>'Compliance Requirements','q'=>'What are the compliance requirements for registered PBOs?'],
                            ['icon'=>'users','color'=>'info','label'=>'Governance Standards','q'=>'What governance structures are required by the PBO Act?'],
                            ['icon'=>'dollar-sign','color'=>'warning','label'=>'Financial Management','q'=>'What financial management requirements apply to PBOs?'],
                            ['icon'=>'file-alt','color'=>'secondary','label'=>'Annual Returns','q'=>'How do I file annual returns as a PBO?'],
                            ['icon'=>'shield-alt','color'=>'danger','label'=>'PBO Rights','q'=>'What rights do PBOs have under the PBO Act 2013?'],
                            ['icon'=>'ban','color'=>'dark','label'=>'Deregistration','q'=>'What can lead to deregistration of a PBO?'],
                            ['icon'=>'balance-scale','color'=>'primary','label'=>'Dispute Resolution','q'=>'How are disputes resolved under the PBO Act?'],
                        ];
                        foreach ($topics as $t): ?>
                        <div class="topic-item" onclick="askQuestion('<?= htmlspecialchars($t['q']) ?>')">
                            <i class="fas fa-<?= $t['icon'] ?> text-<?= $t['color'] ?>"></i>
                            <span><?= $t['label'] ?></span>
                            <i class="fas fa-chevron-right ms-auto" style="font-size:.7rem;opacity:.5;"></i>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- About -->
                    <div class="sidebar-panel" style="background:#fffbeb;border:1px solid #fde68a;">
                        <h6 style="color:#92400e;"><i class="fas fa-info-circle me-2"></i>About This Assistant</h6>
                        <ul style="font-size:.8rem;color:#78350f;padding-left:1.25rem;margin:0;">
                            <li class="mb-1">Trained exclusively on PBO Act 2013 and official regulations</li>
                            <li class="mb-1">Responses based only on approved content</li>
                            <li class="mb-1">Available in English and Kiswahili</li>
                            <li class="mb-1">Flag inaccurate answers to improve accuracy</li>
                            <li class="mb-0"><strong>Does NOT constitute legal advice</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flag Response Modal -->
<div class="modal fade" id="flagModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-flag me-2"></i>Flag Inaccurate Response
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Help us improve accuracy by describing why this response is incorrect.</p>
                <div class="mb-3">
                    <label class="form-label fw-600">Issue Type</label>
                    <select class="form-select" id="flagType">
                        <option value="incorrect">Factually incorrect</option>
                        <option value="incomplete">Incomplete answer</option>
                        <option value="outdated">Outdated information</option>
                        <option value="misunderstood">Misunderstood question</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Your Notes <small class="text-muted">(optional)</small></label>
                    <textarea class="form-control" id="flagNote" rows="3" placeholder="What was wrong with the response?"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitFlag()">
                    <i class="fas fa-flag me-2"></i>Submit Flag
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ─── Chatbot JavaScript ─────────────────────────────────────
let chatLang = 'en';
let messageCount = 0;
let currentFlagId = null;
let sessionId = 'sess_' + Math.random().toString(36).substr(2, 9);

function setLang(lang) {
    chatLang = lang;
    document.getElementById('btnEn').className = lang === 'en' ? 'btn btn-light active' : 'btn btn-outline-light';
    document.getElementById('btnSw').className = lang === 'sw' ? 'btn btn-light active' : 'btn btn-outline-light';
    document.getElementById('messageInput').placeholder = 
        lang === 'sw' ? 'Uliza kuhusu Sheria ya PBO 2013...' : 'Ask about the PBO Act 2013...';
}

function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    document.getElementById('charCount').textContent = el.value.length + '/500';
}

function askQuestion(question) {
    document.getElementById('messageInput').value = question;
    sendMessage();
    document.getElementById('suggestionChips').style.display = 'none';
}

async function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    if (!message) return;
    
    // Clear input
    input.value = '';
    input.style.height = 'auto';
    document.getElementById('charCount').textContent = '0/500';
    
    // Add user message
    appendMessage('user', message);
    messageCount++;
    document.getElementById('msgCount').textContent = messageCount;
    
    // Show typing indicator
    const typingId = 'typing_' + Date.now();
    appendTyping(typingId);
    
    // Disable send button
    document.getElementById('sendBtn').disabled = true;
    
    try {
        const response = await fetch('../../api/chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: message,
                lang: chatLang,
                session_id: sessionId
            })
        });
        
        const data = await response.json();
        
        // Remove typing indicator
        document.getElementById(typingId)?.remove();
        
        if (data.success) {
            appendBotMessage(data.response, data.source, data.confidence, data.conversation_id);
        } else {
            appendBotMessage(
                chatLang === 'sw' 
                    ? 'Samahani, sijapata jibu. Tafadhali uliza tena au tumia lugha tofauti.'
                    : 'I\'m sorry, I couldn\'t find a specific answer to that question. Please try rephrasing or browse our Knowledge Hub for more information.',
                null, 0, null
            );
        }
        
    } catch (err) {
        document.getElementById(typingId)?.remove();
        appendBotMessage('Connection error. Please check your internet and try again.', null, 0, null);
    } finally {
        document.getElementById('sendBtn').disabled = false;
    }
}

function appendMessage(type, content) {
    const messages = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString('en-KE', { hour: '2-digit', minute: '2-digit' });
    
    const div = document.createElement('div');
    div.className = `chat-message ${type}`;
    div.innerHTML = `
        <div class="msg-avatar ${type}">${type === 'bot' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user"></i>'}</div>
        <div class="msg-content">
            <div class="msg-bubble-main">${escapeHtml(content)}</div>
            <div class="msg-meta"><i class="fas fa-clock me-1"></i>${time}</div>
        </div>`;
    messages.appendChild(div);
    scrollToBottom();
}

function appendBotMessage(content, source, confidence, conversationId) {
    const messages = document.getElementById('chatMessages');
    const time = new Date().toLocaleTimeString('en-KE', { hour: '2-digit', minute: '2-digit' });
    const confidenceText = confidence ? `${Math.round(confidence * 100)}% confidence` : '';
    
    const div = document.createElement('div');
    div.className = 'chat-message bot';
    div.innerHTML = `
        <div class="msg-avatar bot"><i class="fas fa-robot"></i></div>
        <div class="msg-content">
            <div class="msg-bubble-main">${formatBotResponse(content)}</div>
            ${source ? `<div class="source-tag"><i class="fas fa-book me-1"></i>${escapeHtml(source)}</div>` : ''}
            <div class="msg-meta">
                <i class="fas fa-clock me-1"></i>${time}
                ${confidenceText ? ` · <i class="fas fa-chart-bar me-1"></i>${confidenceText}` : ''}
            </div>
            ${conversationId ? `
            <div class="feedback-btns">
                <button class="feedback-btn helpful" onclick="sendFeedback(${conversationId}, 'helpful', this)">
                    <i class="fas fa-thumbs-up"></i> Helpful
                </button>
                <button class="feedback-btn" onclick="sendFeedback(${conversationId}, 'not_helpful', this)">
                    <i class="fas fa-thumbs-down"></i> Not Helpful
                </button>
                <button class="feedback-btn flag" onclick="openFlagModal(${conversationId})">
                    <i class="fas fa-flag"></i> Flag
                </button>
            </div>` : ''}
        </div>`;
    messages.appendChild(div);
    scrollToBottom();
    messageCount++;
    document.getElementById('msgCount').textContent = messageCount;
}

function appendTyping(id) {
    const messages = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.id = id;
    div.className = 'chat-message bot';
    div.innerHTML = `
        <div class="msg-avatar bot"><i class="fas fa-robot"></i></div>
        <div class="msg-content">
            <div class="msg-bubble-main">
                <div class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            </div>
            <div class="msg-meta">PBO Assistant is typing...</div>
        </div>`;
    messages.appendChild(div);
    scrollToBottom();
}

function formatBotResponse(text) {
    // Convert markdown-like formatting to HTML
    return text
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>')
        .replace(/^(.+)$/, '<p>$1</p>');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

function scrollToBottom() {
    const messages = document.getElementById('chatMessages');
    messages.scrollTop = messages.scrollHeight;
}

async function sendFeedback(conversationId, type, btn) {
    btn.parentElement.querySelectorAll('.feedback-btn').forEach(b => b.disabled = true);
    try {
        await fetch('../../api/chatbot.php', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ conversation_id: conversationId, feedback: type })
        });
        btn.style.background = type === 'helpful' ? '#d1fae5' : '#fee2e2';
    } catch (e) {}
}

function openFlagModal(conversationId) {
    currentFlagId = conversationId;
    new bootstrap.Modal(document.getElementById('flagModal')).show();
}

async function submitFlag() {
    if (!currentFlagId) return;
    const type = document.getElementById('flagType').value;
    const note = document.getElementById('flagNote').value;
    try {
        await fetch('../../api/chatbot.php', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                conversation_id: currentFlagId,
                feedback: 'flagged',
                flag_type: type,
                feedback_note: note
            })
        });
        bootstrap.Modal.getInstance(document.getElementById('flagModal')).hide();
        alert('Thank you for your feedback! Our team will review this response.');
    } catch (e) { alert('Error submitting flag. Please try again.'); }
}

function clearChat() {
    if (!confirm('Clear this conversation?')) return;
    const messages = document.getElementById('chatMessages');
    const welcome = document.getElementById('welcomeMsg');
    const chips = document.getElementById('suggestionChips');
    const disclaimer = messages.querySelector('.disclaimer-banner');
    
    // Keep only welcome, disclaimer, and chips
    messages.innerHTML = '';
    if (disclaimer) messages.appendChild(disclaimer);
    messages.appendChild(welcome);
    chips.style.display = 'flex';
    messages.appendChild(chips);
    
    messageCount = 0;
    document.getElementById('msgCount').textContent = '0';
    document.getElementById('accuracyRate').textContent = '—';
    sessionId = 'sess_' + Math.random().toString(36).substr(2, 9);
}
</script>
</body>
</html>