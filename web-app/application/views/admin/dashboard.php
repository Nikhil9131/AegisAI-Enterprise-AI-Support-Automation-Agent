<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: #0f172a;">Enterprise AI & Operations Dashboard</h3>
        <p class="text-muted mb-0">Unified analytics across multi-agent workflows, ticket automation, and system guardrails.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo base_url('admin/logs'); ?>" class="btn btn-aegis-outline">
            <i class="bi bi-terminal me-1"></i> Live Agent Traces
        </a>
        <a href="<?php echo base_url('admin/tickets'); ?>" class="btn btn-aegis-primary">
            <i class="bi bi-inbox me-1"></i> Review Ticket Queue
        </a>
    </div>
</div>

<!-- 8 Core Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Employees</span>
                <div class="stat-icon primary"><i class="bi bi-people"></i></div>
            </div>
            <div class="stat-value"><?php echo $total_users; ?></div>
            <div class="stat-footer text-muted"><i class="bi bi-building"></i> Directory synced</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Total Tickets</span>
                <div class="stat-icon info"><i class="bi bi-ticket-perforated"></i></div>
            </div>
            <div class="stat-value"><?php echo $ticket_stats['total']; ?></div>
            <div class="stat-footer text-primary"><i class="bi bi-graph-up"></i> All historical requests</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Open Tickets</span>
                <div class="stat-icon warning"><i class="bi bi-clock-history"></i></div>
            </div>
            <div class="stat-value text-warning"><?php echo $ticket_stats['open']; ?></div>
            <div class="stat-footer text-muted"><i class="bi bi-hourglass-split"></i> Awaiting resolution</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Resolved Tickets</span>
                <div class="stat-icon success"><i class="bi bi-check-circle"></i></div>
            </div>
            <div class="stat-value text-success"><?php echo $ticket_stats['resolved']; ?></div>
            <div class="stat-footer text-success"><i class="bi bi-shield-check"></i> <?php echo round(($ticket_stats['resolved'] / ($ticket_stats['total'] ?: 1)) * 100); ?>% resolved rate</div>
        </div>
    </div>

    <!-- Row 2 Metrics -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">AI Resolved Tickets</span>
                <div class="stat-icon primary"><i class="bi bi-robot"></i></div>
            </div>
            <div class="stat-value text-primary"><?php echo $ai_resolved; ?></div>
            <div class="stat-footer text-primary"><i class="bi bi-stars"></i> <?php echo $ai_resolution_rate; ?>% autonomous resolution</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">AI Escalation Rate</span>
                <div class="stat-icon danger"><i class="bi bi-arrow-up-right-circle"></i></div>
            </div>
            <div class="stat-value text-danger"><?php echo $escalation_rate; ?>%</div>
            <div class="stat-footer text-muted"><i class="bi bi-person-check"></i> Routed to human agents</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Avg AI Response Time</span>
                <div class="stat-icon info"><i class="bi bi-lightning-charge"></i></div>
            </div>
            <div class="stat-value"><?php echo $ai_analytics['avg_latency_ms']; ?> ms</div>
            <div class="stat-footer text-muted"><i class="bi bi-cpu"></i> High-speed inference</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Indexed KB Docs</span>
                <div class="stat-icon success"><i class="bi bi-database-check"></i></div>
            </div>
            <div class="stat-value"><?php echo $ai_analytics['indexed_docs']; ?></div>
            <div class="stat-footer text-success"><i class="bi bi-layers"></i> Dense vector embeddings</div>
        </div>
    </div>
</div>

<!-- Pending Human-in-the-Loop Approvals Banner if any -->
<?php if (!empty($pending_approvals)): ?>
    <div class="aegis-card p-3 mb-4 border border-warning" style="background: #fffbeb;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-exclamation text-warning fs-4"></i>
                <div>
                    <h6 class="fw-bold mb-0 text-dark"><?php echo count($pending_approvals); ?> Sensitive Actions Awaiting Human Sign-off</h6>
                    <span class="small text-muted">Privileged actions proposed by AI Agents require explicit authorization before execution.</span>
                </div>
            </div>
            <a href="<?php echo base_url('admin/tickets'); ?>" class="btn btn-sm btn-warning fw-semibold">Review Pending Queue &rarr;</a>
        </div>
    </div>
<?php endif; ?>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="aegis-card h-100">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-pie-chart text-primary"></i> Tickets by Status</h5>
            </div>
            <div class="aegis-card-body d-flex align-items-center justify-content-center" style="height: 280px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="aegis-card h-100">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-bar-chart-line text-primary"></i> Tickets by Category</h5>
            </div>
            <div class="aegis-card-body d-flex align-items-center justify-content-center" style="height: 280px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="aegis-card h-100">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-shield-check text-primary"></i> Autonomous AI vs Human Resolution</h5>
            </div>
            <div class="aegis-card-body d-flex align-items-center justify-content-center" style="height: 280px;">
                <canvas id="resolutionChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="aegis-card h-100">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-activity text-primary"></i> Ticket Priorities Breakdown</h5>
            </div>
            <div class="aegis-card-body d-flex align-items-center justify-content-center" style="height: 280px;">
                <canvas id="priorityChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Live Multi-Agent Execution Trace Table -->
<div class="aegis-card">
    <div class="aegis-card-header">
        <h5 class="aegis-card-title"><i class="bi bi-terminal text-primary"></i> Recent Multi-Agent Execution Traces</h5>
        <a href="<?php echo base_url('admin/logs'); ?>" class="btn btn-sm btn-link text-decoration-none">View All Traces</a>
    </div>
    <div class="table-responsive">
        <table class="table aegis-table">
            <thead>
                <tr>
                    <th>User Query</th>
                    <th>Supervisor Routing</th>
                    <th>Tools Executed</th>
                    <th>Latency</th>
                    <th>Model</th>
                    <th>Status</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_logs)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No agent execution traces recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recent_logs as $l): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark text-truncate" style="max-width: 250px;">
                                    <?php echo htmlspecialchars($l->query); ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border font-monospace" style="font-size: 11px;">
                                    <?php echo htmlspecialchars($l->agent_selected); ?>
                                </span>
                            </td>
                            <td>
                                <div class="small text-muted font-monospace text-truncate" style="max-width: 180px;">
                                    <?php echo htmlspecialchars($l->tools_used ?: '[]'); ?>
                                </div>
                            </td>
                            <td class="small fw-semibold"><?php echo $l->latency_ms; ?> ms</td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($l->model); ?></span></td>
                            <td>
                                <span class="badge <?php echo $l->success ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> border">
                                    <?php echo $l->success ? 'SUCCESS' : 'FAILED'; ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?php echo date('M d, H:i', strtotime($l->created_at)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart Initialization Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Status Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Open', 'In Progress', 'Resolved', 'Closed', 'Escalated', 'Waiting User'],
            datasets: [{
                data: [
                    <?php echo $ticket_stats['open']; ?>,
                    <?php echo $ticket_stats['in_progress']; ?>,
                    <?php echo $ticket_stats['resolved']; ?>,
                    <?php echo $ticket_stats['closed']; ?>,
                    <?php echo $ticket_stats['escalated']; ?>,
                    1
                ],
                backgroundColor: ['#0284c7', '#d97706', '#059669', '#64748b', '#dc2626', '#7c3aed'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right' } }
        }
    });

    // 2. Category Chart
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: ['Network & VPN', 'Hardware & Devices', 'IT Security', 'Developer Tools', 'HR Policies'],
            datasets: [{
                label: 'Tickets',
                data: [5, 4, 5, 4, 2],
                backgroundColor: '#3b82f6',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // 3. Resolution Chart (AI vs Human)
    new Chart(document.getElementById('resolutionChart'), {
        type: 'pie',
        data: {
            labels: ['AI Autonomous Grounded Resolution', 'Escalated to Human Engineers'],
            datasets: [{
                data: [<?php echo $ai_resolved; ?>, <?php echo max(1, $ticket_stats['resolved'] - $ai_resolved); ?>],
                backgroundColor: ['#2563eb', '#94a3b8'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 4. Priority Chart
    new Chart(document.getElementById('priorityChart'), {
        type: 'bar',
        data: {
            labels: ['Low', 'Medium', 'High', 'Critical'],
            datasets: [{
                label: 'Volume',
                data: [5, 6, 6, 3],
                backgroundColor: ['#64748b', '#0284c7', '#ea580c', '#dc2626'],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
