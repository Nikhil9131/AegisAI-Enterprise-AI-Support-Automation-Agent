<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: #0f172a;">System Settings & AI Model Configuration</h3>
        <p class="text-muted mb-0">Manage LLM provider abstraction (OpenAI, Gemini, Anthropic), vector database endpoints, and guardrails.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="aegis-card">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-sliders text-primary"></i> AI Model & Microservice Parameters</h5>
            </div>
            <div class="aegis-card-body">
                <form action="<?php echo base_url('admin/save_settings'); ?>" method="POST">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Active LLM Provider</label>
                            <select name="llm_provider" class="form-select">
                                <option value="gemini" <?php echo ($env['LLM_PROVIDER'] === 'gemini') ? 'selected' : ''; ?>>Google Gemini (Gemini 1.5 Pro / Flash)</option>
                                <option value="openai" <?php echo ($env['LLM_PROVIDER'] === 'openai') ? 'selected' : ''; ?>>OpenAI (GPT-4o / GPT-4 Turbo)</option>
                                <option value="anthropic" <?php echo ($env['LLM_PROVIDER'] === 'anthropic') ? 'selected' : ''; ?>>Anthropic Claude (Claude 3.5 Sonnet)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Default Orchestrator Model</label>
                            <input type="text" name="default_model" class="form-control" value="<?php echo htmlspecialchars($env['DEFAULT_MODEL']); ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Qdrant Vector Database URL</label>
                            <input type="text" name="qdrant_url" class="form-control font-monospace" value="<?php echo htmlspecialchars($env['QDRANT_URL']); ?>">
                            <div class="form-text small text-muted">Use ":memory:" or local path for embedded, or "http://localhost:6333" for Docker.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">AI Confidence Escalation Threshold</label>
                            <input type="number" step="0.01" min="0" max="1" name="confidence_threshold" class="form-control" value="<?php echo htmlspecialchars($env['AI_CONFIDENCE_THRESHOLD']); ?>">
                            <div class="form-text small text-muted">Responses below this threshold trigger ticket escalation to human agents.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">FastAPI AI Microservice Endpoint</label>
                        <input type="text" name="ai_service_url" class="form-control font-monospace" value="<?php echo htmlspecialchars($env['AI_SERVICE_URL']); ?>">
                    </div>

                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="hitl_switch" name="enable_hitl" value="true" <?php echo ($env['ENABLE_HUMAN_IN_THE_LOOP'] === 'true') ? 'checked' : ''; ?>>
                        <label class="form-check-label small fw-semibold" for="hitl_switch">
                            Mandatory Human-in-the-Loop Verification for Destructive / Sensitive Actions
                        </label>
                        <div class="form-text small text-muted">When enabled, operations such as elevated permissions, hardware orders, and ticket closures require human approval.</div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-aegis-primary px-4">Save Configuration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Security & Guardrails Info Box -->
        <div class="aegis-card p-4">
            <h6 class="fw-bold mb-2"><i class="bi bi-shield-check text-primary me-1"></i> Enterprise Guardrails Active</h6>
            <ul class="list-unstyled small text-muted mb-0">
                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Prompt Injection Guard:</strong> Retrieved documents treated strictly as inert data passages.</li>
                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>SQL Injection Blocker:</strong> Read-only SELECT validation; DROP, ALTER, DELETE forbidden.</li>
                <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>API Allowlist:</strong> Multi-agent system restricted to approved internal endpoints.</li>
                <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Audit Logging:</strong> All supervisor routings and approvals logged to disk and database.</li>
            </ul>
        </div>
    </div>
</div>
