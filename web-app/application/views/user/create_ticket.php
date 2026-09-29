<div class="row g-4">
    <!-- Left: Ticket Creation Form -->
    <div class="col-lg-7">
        <div class="aegis-card">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-pencil-square text-primary"></i> Submit New Support Request</h5>
            </div>
            <div class="aegis-card-body">
                <form action="<?php echo base_url('tickets/store'); ?>" method="POST" id="ticketForm">
                    <div class="mb-3">
                        <label for="title" class="form-label small fw-semibold">Subject / Issue Summary <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="e.g. WireGuard VPN gateway handshake timeout on macOS" value="<?php echo htmlspecialchars($prefill ?? ''); ?>" oninput="triggerAiCopilot()">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="category" class="form-label small fw-semibold">Category</label>
                            <select class="form-select" id="category" name="category" onchange="triggerAiCopilot()">
                                <option value="Network & VPN">Network & VPN</option>
                                <option value="Hardware & Devices">Hardware & Devices</option>
                                <option value="IT Security">IT Security</option>
                                <option value="Developer Tools">Developer Tools</option>
                                <option value="HR Policies">HR Policies</option>
                                <option value="Workplace Tools">Workplace Tools</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="priority" class="form-label small fw-semibold">Initial Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="LOW">Low &bull; Minor inconvenience</option>
                                <option value="MEDIUM" selected>Medium &bull; Standard request</option>
                                <option value="HIGH">High &bull; Work blocked</option>
                                <option value="CRITICAL">Critical &bull; Production outage</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label small fw-semibold">Detailed Description & Error Messages <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="6" required placeholder="Please describe what occurred, exact error codes, and steps taken so far..." oninput="triggerAiCopilot()"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Affected Registered Device (Optional)</label>
                        <select class="form-select" name="device_id">
                            <option value="">-- No specific device / Not applicable --</option>
                            <?php foreach ($my_devices as $dev): ?>
                                <option value="<?php echo $dev->id; ?>"><?php echo htmlspecialchars($dev->device_name . ' (' . $dev->os_version . ' - ' . $dev->serial_number . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <a href="<?php echo base_url('tickets'); ?>" class="btn btn-aegis-outline">Cancel</a>
                        <button type="submit" class="btn btn-aegis-primary px-4">
                            <i class="bi bi-shield-check me-1"></i> Submit Ticket for AI Triage
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right: Real-time AI Copilot & Pre-Triage Advisor -->
    <div class="col-lg-5">
        <div class="aegis-card" style="border-left: 4px solid var(--aegis-primary);">
            <div class="aegis-card-header bg-primary-subtle border-0">
                <span class="fw-bold small text-primary"><i class="bi bi-stars me-1"></i> Aegis AI Instant Copilot</span>
                <span class="badge bg-white text-primary border" id="aiStatusBadge">Listening...</span>
            </div>
            <div class="aegis-card-body" id="copilotBody">
                <div class="text-center py-4 text-muted" id="copilotEmptyState">
                    <i class="bi bi-robot fs-2 text-primary d-block mb-2"></i>
                    <p class="small mb-1 fw-semibold text-dark">Smart Ticket Triage Active</p>
                    <p class="small mb-0 text-muted">Type your issue summary and description. Aegis AI will automatically analyze urgency, classify department, and search for immediate self-service solutions.</p>
                </div>

                <div id="copilotResults" style="display: none;">
                    <div class="mb-3">
                        <div class="text-muted small fw-bold text-uppercase mb-1">AI Classification & Confidence</div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark border" id="copilotCategory">Network & VPN</span>
                            <span class="badge bg-primary-subtle text-primary border" id="copilotConfidence">94% Confidence</span>
                        </div>
                    </div>

                    <div class="mb-3 p-3 rounded-2 bg-light border">
                        <div class="fw-bold text-dark small mb-1"><i class="bi bi-lightbulb text-warning me-1"></i> Suggested Resolution:</div>
                        <div class="small text-secondary" id="copilotSuggestion">Analyzing...</div>
                    </div>

                    <div class="p-2 border rounded-2 bg-white">
                        <div class="small text-muted mb-1"><i class="bi bi-shield-check text-success me-1"></i> Does this solve your issue without filing a ticket?</div>
                        <button type="button" class="btn btn-sm btn-outline-success w-100 py-1" onclick="alert('Glad we could help! Redirecting to knowledge base.'); window.location.href='<?php echo base_url('knowledge'); ?>';">
                            <i class="bi bi-check2-circle me-1"></i> Yes, Issue Resolved!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let debounceTimer;

function triggerAiCopilot() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(runCopilotAnalysis, 600);
}

async function runCopilotAnalysis() {
    const title = document.getElementById('title').value.trim();
    const desc = document.getElementById('description').value.trim();

    if (title.length < 5) return;

    document.getElementById('aiStatusBadge').innerText = 'Analyzing...';
    document.getElementById('copilotEmptyState').style.display = 'none';
    document.getElementById('copilotResults').style.display = 'block';

    try {
        const fd = new FormData();
        fd.append('title', title);
        fd.append('description', desc);

        const res = await fetch('<?php echo base_url("tickets/ajax_suggest"); ?>', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        document.getElementById('aiStatusBadge').innerText = 'Analyzed';
        document.getElementById('copilotCategory').innerText = data.category || 'IT Support';
        document.getElementById('copilotConfidence').innerText = (data.confidence || 92) + '% Confidence';
        document.getElementById('copilotSuggestion').innerText = data.suggested_resolution || 'Review enterprise IT policies for standard procedures.';
    } catch (e) {
        document.getElementById('aiStatusBadge').innerText = 'Ready';
    }
}
</script>
