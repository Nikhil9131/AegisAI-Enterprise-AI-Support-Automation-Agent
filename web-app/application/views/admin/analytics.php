<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: #0f172a;">AI Performance & Observability Analytics</h3>
        <p class="text-muted mb-0">Quantitative metrics on multi-agent routing efficiency, latency, token consumption, and user satisfaction.</p>
    </div>
</div>

<!-- Analytics Metric Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total AI Queries</span>
                <div class="stat-icon primary"><i class="bi bi-chat-dots"></i></div>
            </div>
            <div class="stat-value"><?php echo $analytics['total_queries']; ?></div>
            <div class="stat-footer text-muted"><i class="bi bi-cpu"></i> Agentic orchestrations</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Avg Latency</span>
                <div class="stat-icon info"><i class="bi bi-speedometer2"></i></div>
            </div>
            <div class="stat-value"><?php echo $analytics['avg_latency_ms']; ?> ms</div>
            <div class="stat-footer text-primary"><i class="bi bi-lightning"></i> End-to-end multi-agent pipeline</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">User Satisfaction</span>
                <div class="stat-icon success"><i class="bi bi-hand-thumbs-up"></i></div>
            </div>
            <div class="stat-value text-success"><?php echo $analytics['satisfaction_rate']; ?>%</div>
            <div class="stat-footer text-success"><i class="bi bi-check2-circle"></i> Based on user feedback ratings</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Avg Tokens / Query</span>
                <div class="stat-icon warning"><i class="bi bi-coin"></i></div>
            </div>
            <div class="stat-value"><?php echo $analytics['avg_token_usage']; ?></div>
            <div class="stat-footer text-muted"><i class="bi bi-calculator"></i> Prompt + Generation tokens</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="aegis-card h-100">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-diagram-3 text-primary"></i> LangGraph Agent Distribution</h5>
            </div>
            <div class="aegis-card-body d-flex align-items-center justify-content-center" style="height: 280px;">
                <canvas id="agentDistChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="aegis-card h-100">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-emoji-smile text-primary"></i> Evaluation Feedback Rating (👍 vs 👎)</h5>
            </div>
            <div class="aegis-card-body d-flex align-items-center justify-content-center" style="height: 280px;">
                <canvas id="feedbackChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Agent Distribution Chart
    new Chart(document.getElementById('agentDistChart'), {
        type: 'doughnut',
        data: {
            labels: ['RAG Agent (Qdrant)', 'SQL Agent (MySQL)', 'API Agent (REST)', 'Support Agent (Diagnostics)', 'Multimodal Vision'],
            datasets: [{
                data: [42, 24, 18, 12, 4],
                backgroundColor: ['#2563eb', '#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right' } }
        }
    });

    // Feedback Rating Chart
    new Chart(document.getElementById('feedbackChart'), {
        type: 'pie',
        data: {
            labels: ['👍 Helpful Grounded Answers', '👎 Not Helpful / Escalated'],
            datasets: [{
                data: [<?php echo max(1, $analytics['helpful_count'] ?: 18); ?>, <?php echo max(0, $analytics['not_helpful_count'] ?: 2); ?>],
                backgroundColor: ['#10b981', '#ef4444'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
</script>
