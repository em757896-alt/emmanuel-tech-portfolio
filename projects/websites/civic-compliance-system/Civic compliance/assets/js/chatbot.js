(function() {
    'use strict';

    const chatContainer = document.getElementById('chatMessages');
    const chatInput     = document.getElementById('chatInput');
    const chatSendBtn   = document.getElementById('chatSendBtn');
    const chatToggle    = document.getElementById('chatToggle');
    const chatWidget    = document.getElementById('chatWidget');
    const chatMinimize  = document.getElementById('chatMinimize');
    const quickBtns     = document.querySelectorAll('.quick-btn');

    if (!chatContainer || !chatInput) return;

    let isOpen   = chatWidget && chatWidget.classList.contains('open');
    let isProcessing = false;

    // ── Toggle Chat ──
    if (chatToggle) {
        chatToggle.addEventListener('click', function() {
            chatWidget.classList.toggle('open');
            isOpen = chatWidget.classList.contains('open');
            if (isOpen) chatInput.focus();
        });
    }

    if (chatMinimize) {
        chatMinimize.addEventListener('click', function() {
            chatWidget.classList.remove('open');
            isOpen = false;
        });
    }

    // ── Send Message ──
    function sendMessage(text) {
        if (isProcessing || !text.trim()) return;

        isProcessing = true;
        addMessage(text, 'user');
        chatInput.value = '';

        var typingId = showTyping();

        fetch('/api/chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        })
        .then(function(resp) { return resp.json(); })
        .then(function(data) {
            removeTyping(typingId);
            if (data.response) {
                addMessage(data.response, 'bot');
            } else if (data.error) {
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            }
        })
        .catch(function() {
            removeTyping(typingId);
            addMessage('Connection error. Please check your internet and try again.', 'bot');
        })
        .finally(function() {
            isProcessing = false;
        });
    }

    // ── Add Message ──
    function addMessage(text, sender) {
        var div = document.createElement('div');
        div.className = 'chat-msg chat-' + sender;
        div.innerHTML = '<div class="msg-bubble">' + escapeHtml(text) + '</div>';
        chatContainer.appendChild(div);
        scrollToBottom();
    }

    // ── Typing Indicator ──
    function showTyping() {
        var div = document.createElement('div');
        div.className = 'chat-msg chat-bot typing-indicator';
        div.id = 'typing-' + Date.now();
        div.innerHTML = '<div class="msg-bubble"><span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span></div>';
        chatContainer.appendChild(div);
        scrollToBottom();
        return div.id;
    }

    function removeTyping(id) {
        var el = document.getElementById(id);
        if (el) el.remove();
    }

    function scrollToBottom() {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // ── Send on Enter ──
    if (chatInput) {
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage(chatInput.value);
            }
        });
    }

    if (chatSendBtn) {
        chatSendBtn.addEventListener('click', function() {
            sendMessage(chatInput.value);
        });
    }

    // ── Quick Action Buttons ──
    quickBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var text = this.getAttribute('data-question') || this.textContent.trim();
            sendMessage(text);
        });
    });

    // ── Feedback Buttons ──
    document.addEventListener('click', function(e) {
        var feedbackBtn = e.target.closest('.feedback-btn');
        if (!feedbackBtn) return;

        var value = feedbackBtn.getAttribute('data-value');
        var lastBotMsg = chatContainer.querySelector('.chat-bot:last-of-type .msg-bubble');
        if (lastBotMsg) {
            lastBotMsg.innerHTML += ' <span style="font-size:0.75rem;color:#9ca3af;display:block;margin-top:4px">' +
                (value === 'positive' ? '&#x1f44d; Helpful' : '&#x1f44e; Not helpful') + '</span>';
            feedbackBtn.closest('.feedback-row')?.remove();
        }
    });

})();
