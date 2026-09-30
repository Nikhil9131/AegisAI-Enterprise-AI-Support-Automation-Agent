<div class="row g-3">
    <!-- Left Chat History Sidebar -->
    <div class="col-lg-3 d-none d-lg-block">
        <div class="aegis-card h-100 mb-0">
            <div class="aegis-card-header">
                <span class="fw-bold small text-muted"><i class="bi bi-chat-left-text me-1 text-primary"></i> Investigations</span>
                <a href="<?php echo base_url('assistant'); ?>" class="btn btn-sm btn-aegis-primary py-0 px-2 small" title="New Session">
                    <i class="bi bi-plus"></i> New
                </a>
            </div>
            <div class="p-2" style="max-height: calc(100vh - 230px); overflow-y: auto;">
                <div class="list-group list-group-flush">
                    <?php foreach ($conversations as $c): ?>
                        <a href="<?php echo base_url('assistant/index/' . $c->id); ?>" class="list-group-item list-group-item-action py-2 px-3 rounded-2 mb-1 border-0 small <?php echo ($c->id == $conversation->id) ? 'bg-primary-subtle text-primary fw-semibold' : 'text-secondary'; ?>">
                            <div class="text-truncate"><?php echo htmlspecialchars($c->title); ?></div>
                            <span class="text-muted" style="font-size: 10px;"><?php echo date('M d, H:i', strtotime($c->updated_at)); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Device Quick Info Widget -->
            <div class="p-3 border-top bg-light rounded-bottom small">
                <div class="fw-bold text-dark mb-1"><i class="bi bi-laptop text-primary me-1"></i> Active Device</div>
                <?php if (!empty($my_devices)): ?>
                    <div class="text-truncate fw-semibold text-secondary"><?php echo htmlspecialchars($my_devices[0]->device_name); ?></div>
                    <div class="text-muted" style="font-size: 11px;">OS: <?php echo htmlspecialchars($my_devices[0]->os_version); ?></div>
                    <span class="badge bg-success-subtle text-success border mt-1" style="font-size: 10px;">Zero-Trust Compliant</span>
                <?php else: ?>
                    <div class="text-muted">Standard Employee Profile</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Chat Container -->
    <div class="col-lg-9">
        <div class="chat-container">
            <!-- Chat Topbar -->
            <div class="chat-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-icon-box" style="width: 32px; height: 32px; font-size: 16px;">
                        <i class="bi bi-cpu-fill"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Aegis Multi-Agent Orchestrator</h6>
                        <span class="text-muted" style="font-size: 11.5px;">LangGraph Supervisor &bull; Qdrant RAG &bull; Read-Only SQL &bull; Multimodal</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="aiServiceStatusBadge" class="badge bg-primary-subtle text-primary border" title="Checking AI Microservice status..."><i class="bi bi-circle-half me-1" style="font-size: 7px;"></i> Initializing Service...</span>
                    <button class="btn btn-sm btn-light border" onclick="clearChat()" title="Clear Current View"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </div>

            <!-- Chat Message Thread -->
            <div class="chat-body" id="chatThread">
                <!-- Welcome Banner & Prompt Pills -->
                <div class="text-center py-3">
                    <div class="d-inline-flex p-3 bg-primary-subtle text-primary rounded-circle mb-2">
                        <i class="bi bi-shield-check fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">How can Aegis AI assist your workday?</h5>
                    <p class="text-muted small mb-3">Ask about IT policies, analyze VPN issues, query ticket data, or upload error screenshots.</p>
                    
                    <!-- Quick Example Prompt Pills -->
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary bg-white rounded-pill px-3 shadow-sm text-start" onclick="sendQuickPrompt('My laptop cannot connect to the company VPN. What should I do?')">
                            <i class="bi bi-wifi me-1"></i> "My laptop cannot connect to the company VPN. What should I do?"
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary bg-white rounded-pill px-3 shadow-sm text-start" onclick="sendQuickPrompt('What is the company\'s laptop replacement policy?')">
                            <i class="bi bi-laptop me-1"></i> "What is the company's laptop replacement policy?"
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary bg-white rounded-pill px-3 shadow-sm text-start" onclick="sendQuickPrompt('How many open tickets are there and what is the highest priority issue?')">
                            <i class="bi bi-database me-1"></i> "How many open tickets are there?"
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary bg-white rounded-pill px-3 shadow-sm text-start" onclick="sendQuickPrompt('Check Rahul\'s open tickets and create a high-priority ticket for his VPN issue.')">
                            <i class="bi bi-ticket-detailed me-1"></i> "Check Rahul's open tickets and create high-priority ticket."
                        </button>
                    </div>
                </div>

                <!-- Existing Messages from Database -->
                <?php foreach ($messages as $msg): ?>
                    <?php $meta = json_decode($msg->metadata ?: '{}', true); ?>
                    <div class="chat-message <?php echo strtolower($msg->sender); ?>">
                        <div class="chat-bubble">
                            <?php if ($msg->sender === 'ASSISTANT'): ?>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="agent-badge-pill">
                                        <i class="bi bi-robot"></i> <?php echo htmlspecialchars($meta['agent_selected'] ?? 'Supervisor -> RAG Agent'); ?>
                                    </span>
                                    <div class="text-muted" style="font-size: 11px;">
                                        <i class="bi bi-stopwatch"></i> <?php echo $meta['latency_ms'] ?? 420; ?>ms &bull; <?php echo $meta['model'] ?? 'gemini-1.5-pro'; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- If image attachment exists -->
                            <?php if (!empty($meta['image_url'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo htmlspecialchars($meta['image_url']); ?>" alt="Error Screenshot" class="img-fluid rounded border" style="max-height: 220px;">
                                </div>
                            <?php endif; ?>

                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($msg->message)); ?>
                            </div>

                            <!-- Citations box if present -->
                            <?php if (!empty($meta['citations'])): ?>
                                <div class="sources-box">
                                    <div class="fw-bold text-muted mb-1"><i class="bi bi-bookmark-check me-1"></i> Verified Sources & Citations:</div>
                                    <?php foreach ($meta['citations'] as $cite): ?>
                                        <div class="citation-chip">
                                            <i class="bi bi-file-text"></i>
                                            <?php 
                                                $c_title = is_array($cite) ? ($cite['title'] ?? ($cite['document_name'] ?? 'Policy')) : $cite;
                                                $c_page = is_array($cite) ? ($cite['page'] ?? ($cite['page_number'] ?? '')) : '';
                                                $c_sec = is_array($cite) ? ($cite['section'] ?? '') : '';
                                                echo htmlspecialchars($c_title) . ($c_page ? " (p. $c_page)" : "") . ($c_sec ? " &bull; $c_sec" : "");
                                            ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Feedback & Action Bar for Assistant -->
                            <?php if ($msg->sender === 'ASSISTANT'): ?>
                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                    <div class="d-flex align-items-center gap-1">
                                        <button class="btn btn-sm btn-link text-muted p-0 me-2" onclick="rateFeedback(<?php echo $msg->id; ?>, 'HELPFUL', this)" title="Helpful answer">
                                            <i class="bi bi-hand-thumbs-up"></i>
                                        </button>
                                        <button class="btn btn-sm btn-link text-muted p-0" onclick="rateFeedback(<?php echo $msg->id; ?>, 'NOT_HELPFUL', this)" title="Not helpful">
                                            <i class="bi bi-hand-thumbs-down"></i>
                                        </button>
                                    </div>
                                    <a href="<?php echo base_url('tickets/create?prefill=' . urlencode(substr($msg->message, 0, 150))); ?>" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11.5px;">
                                        <i class="bi bi-plus-circle me-1"></i> Create Support Ticket
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Input Bar -->
            <div class="chat-input-bar">
                <form id="chatForm" onsubmit="handleChatSubmit(event)">
                    <input type="hidden" id="conversationId" value="<?php echo $conversation->id; ?>">
                    <div class="input-group">
                        <!-- Image Upload Button -->
                        <button type="button" class="btn btn-light border" onclick="document.getElementById('screenshotInput').click()" title="Attach Error Screenshot for Multimodal Diagnosis">
                            <i class="bi bi-camera text-primary"></i>
                        </button>
                        <input type="file" id="screenshotInput" accept="image/*" style="display: none;" onchange="handleImageUpload(event)">

                        <input type="text" id="queryInput" class="form-control ps-3" placeholder="Ask Aegis AI or upload an error screenshot..." autocomplete="off">
                        
                        <button type="submit" id="sendBtn" class="btn btn-aegis-primary px-4">
                            <i class="bi bi-send-fill me-1"></i> Send
                        </button>
                    </div>
                </form>
                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                    <span class="text-muted" style="font-size: 11px;">
                        <i class="bi bi-lock-fill text-muted me-1"></i> Grounded Enterprise RAG &bull; Strict Zero-Trust Guardrails
                    </span>
                    <span class="text-muted" style="font-size: 11px;" id="typingIndicator" style="display: none;"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chat Dynamic Logic -->
<script>
const CONV_ID = <?php echo $conversation->id; ?>;

function sendQuickPrompt(promptText) {
    document.getElementById('queryInput').value = promptText;
    document.getElementById('chatForm').dispatchEvent(new Event('submit'));
}

async function handleChatSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('queryInput');
    const query = input.value.trim();
    if (!query) return;

    // Append User Message to Thread
    appendUserBubble(query);
    input.value = '';

    // Show Typing State
    const typingId = appendLoadingBubble();
    const sendBtn = document.getElementById('sendBtn');
    sendBtn.disabled = true;

    try {
        const formData = new FormData();
        formData.append('conversation_id', CONV_ID);
        formData.append('query', query);

        const res = await fetch('<?php echo base_url("assistant/send_message"); ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        let data;
        const resText = await res.text();
        try {
            data = JSON.parse(resText);
        } catch (jsonErr) {
            throw new Error(`Server returned status ${res.status}: ${resText.substring(0, 100)}`);
        }

        removeBubble(typingId);

        if (data.success) {
            appendAssistantBubble(data.answer, data.metadata, data.message_id, data.log_id);
        } else {
            appendAssistantBubble("System Notice: " + (data.error || "Unable to retrieve response from AI engine."), { agent_selected: 'System Notice', latency_ms: 0 });
        }
    } catch (err) {
        removeBubble(typingId);
        const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
        let noticeText = "Service Notice: ";
        if (isLocalhost) {
            noticeText += "Could not reach FastAPI service on port 8001. Please ensure Python AI microservice is active or rely on the embedded RAG engine.";
        } else {
            noticeText += "Connection timed out or cloud service is warming up. Please try again in a few moments, or check AI_SERVICE_URL.";
        }
        appendAssistantBubble(noticeText, { agent_selected: 'System Notice', latency_ms: 0, model: 'aegis-core' });
    } finally {
        sendBtn.disabled = false;
        scrollToBottom();
    }
}

async function handleImageUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    const typingId = appendLoadingBubble("Analyzing screenshot via Multimodal Vision AI model...");
    const formData = new FormData();
    formData.append('conversation_id', CONV_ID);
    formData.append('screenshot', file);

    try {
        const res = await fetch('<?php echo base_url("assistant/upload_screenshot"); ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        removeBubble(typingId);

        if (data.success) {
            appendAssistantBubble(data.answer, data.metadata, data.message_id, null, data.image_url);
        } else {
            appendAssistantBubble("Error uploading image: " + (data.error || "Failed"), { agent_selected: 'Vision Agent' });
        }
    } catch (err) {
        removeBubble(typingId);
        appendAssistantBubble("Failed to upload screenshot to multimodal pipeline.", { agent_selected: 'Vision Agent' });
    }
    e.target.value = '';
    scrollToBottom();
}

function appendUserBubble(text) {
    const thread = document.getElementById('chatThread');
    const bubble = document.createElement('div');
    bubble.className = 'chat-message user';
    bubble.innerHTML = `<div class="chat-bubble"><div class="message-content">${escapeHtml(text)}</div></div>`;
    thread.appendChild(bubble);
    scrollToBottom();
}

function appendAssistantBubble(text, meta = {}, msgId = null, logId = null, imageUrl = null) {
    const thread = document.getElementById('chatThread');
    const bubble = document.createElement('div');
    bubble.className = 'chat-message assistant';

    let citationsHtml = '';
    if (meta.citations && meta.citations.length > 0) {
        citationsHtml = '<div class="sources-box"><div class="fw-bold text-muted mb-1"><i class="bi bi-bookmark-check me-1"></i> Verified Sources & Citations:</div>';
        meta.citations.forEach(c => {
            const title = typeof c === 'object' ? (c.title || c.document_name || 'Policy Doc') : c;
            const page = typeof c === 'object' && c.page ? ` (p. ${c.page})` : '';
            const sec = typeof c === 'object' && c.section ? ` &bull; ${c.section}` : '';
            citationsHtml += `<div class="citation-chip"><i class="bi bi-file-text"></i> ${escapeHtml(title)}${page}${sec}</div>`;
        });
        citationsHtml += '</div>';
    }

    let imageHtml = imageUrl ? `<div class="mb-2"><img src="${imageUrl}" class="img-fluid rounded border" style="max-height: 220px;"></div>` : '';

    let feedbackBar = '';
    if (msgId) {
        feedbackBar = `
        <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
            <div class="d-flex align-items-center gap-1">
                <button class="btn btn-sm btn-link text-muted p-0 me-2" onclick="rateFeedback(${msgId}, 'HELPFUL', this, ${logId || 0})" title="Helpful">
                    <i class="bi bi-hand-thumbs-up"></i>
                </button>
                <button class="btn btn-sm btn-link text-muted p-0" onclick="rateFeedback(${msgId}, 'NOT_HELPFUL', this, ${logId || 0})" title="Not helpful">
                    <i class="bi bi-hand-thumbs-down"></i>
                </button>
            </div>
            <a href="<?php echo base_url('tickets/create?prefill='); ?>${encodeURIComponent(text.substring(0, 150))}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11.5px;">
                <i class="bi bi-plus-circle me-1"></i> Create Support Ticket
            </a>
        </div>`;
    }

    bubble.innerHTML = `
        <div class="chat-bubble">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="agent-badge-pill">
                    <i class="bi bi-robot"></i> ${escapeHtml(meta.agent_selected || 'Supervisor -> RAG Agent')}
                </span>
                <div class="text-muted" style="font-size: 11px;">
                    <i class="bi bi-stopwatch"></i> ${meta.latency_ms !== undefined && meta.latency_ms !== null ? meta.latency_ms : 180}ms &bull; ${escapeHtml(meta.model || 'gemini-1.5-pro')}
                </div>
            </div>
            ${imageHtml}
            <div class="message-content">${formatMarkdown(text)}</div>
            ${citationsHtml}
            ${feedbackBar}
        </div>
    `;
    thread.appendChild(bubble);
    scrollToBottom();
}

function appendLoadingBubble(label = "Aegis LangGraph Supervisor orchestrating agents...") {
    const id = 'loading_' + Date.now();
    const thread = document.getElementById('chatThread');
    const bubble = document.createElement('div');
    bubble.id = id;
    bubble.className = 'chat-message assistant';
    bubble.innerHTML = `
        <div class="chat-bubble bg-white border">
            <div class="d-flex align-items-center gap-2 text-primary small">
                <div class="spinner-border spinner-border-sm" role="status"></div>
                <span class="fw-semibold">${label}</span>
            </div>
        </div>
    `;
    thread.appendChild(bubble);
    scrollToBottom();
    return id;
}

function removeBubble(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

function scrollToBottom() {
    const thread = document.getElementById('chatThread');
    thread.scrollTop = thread.scrollHeight;
}

async function rateFeedback(msgId, rating, btn, logId = 0) {
    btn.parentElement.innerHTML = `<span class="badge bg-light text-primary border" style="font-size: 10px;"><i class="bi bi-check2"></i> Feedback Recorded</span>`;
    const fd = new FormData();
    fd.append('message_id', msgId);
    fd.append('feedback', rating);
    if (logId) fd.append('log_id', logId);

    await fetch('<?php echo base_url("assistant/feedback"); ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
}

function clearChat() {
    window.location.href = '<?php echo base_url("assistant"); ?>';
}

function escapeHtml(str) {
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function formatMarkdown(text) {
    // Basic format: newlines, bold, list bullets
    let html = escapeHtml(text);
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
    html = html.replace(/`([^`]+)`/g, '<code class="bg-light px-1 py-0.5 rounded border">$1</code>');
    html = html.replace(/\n/g, '<br>');
    return html;
}

async function checkAiServiceStatus() {
    const badge = document.getElementById('aiServiceStatusBadge');
    if (!badge) return;
    try {
        const res = await fetch('<?php echo base_url("assistant/service_status"); ?>', {
            headers: { 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.connected) {
            badge.className = 'badge bg-success-subtle text-success border';
            badge.innerHTML = '<i class="bi bi-circle-fill me-1" style="font-size: 7px;"></i> AI Microservice Online';
            badge.title = 'FastAPI Microservice online at ' + (data.url || 'port 8001');
        } else {
            badge.className = 'badge bg-primary-subtle text-primary border';
            badge.innerHTML = '<i class="bi bi-shield-check me-1"></i> Standalone RAG Active';
            badge.title = 'Embedded Enterprise Policy Engine is active with zero downtime.';
        }
    } catch (e) {
        badge.className = 'badge bg-primary-subtle text-primary border';
        badge.innerHTML = '<i class="bi bi-shield-check me-1"></i> Standalone RAG Active';
        badge.title = 'Embedded Enterprise Policy Engine is active.';
    }
}
document.addEventListener('DOMContentLoaded', checkAiServiceStatus);
</script>
