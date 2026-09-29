<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: #0f172a;">Support Requests</h3>
        <p class="text-muted mb-0"><?php echo $is_agent_or_admin ? 'Enterprise support queue and incident tracking' : 'Track and manage your submitted support tickets'; ?></p>
    </div>
    <a href="<?php echo base_url('tickets/create'); ?>" class="btn btn-aegis-primary">
        <i class="bi bi-plus-lg me-1"></i> Submit New Request
    </a>
</div>

<!-- Filters Bar -->
<div class="aegis-card p-3 mb-4">
    <form method="GET" action="<?php echo base_url('tickets'); ?>" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" class="form-control border-start-0" placeholder="Search ticket #, subject..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
            </div>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="OPEN" <?php echo (($filters['status'] ?? '') === 'OPEN') ? 'selected' : ''; ?>>Open</option>
                <option value="IN_PROGRESS" <?php echo (($filters['status'] ?? '') === 'IN_PROGRESS') ? 'selected' : ''; ?>>In Progress</option>
                <option value="WAITING_FOR_USER" <?php echo (($filters['status'] ?? '') === 'WAITING_FOR_USER') ? 'selected' : ''; ?>>Waiting for User</option>
                <option value="ESCALATED" <?php echo (($filters['status'] ?? '') === 'ESCALATED') ? 'selected' : ''; ?>>Escalated</option>
                <option value="RESOLVED" <?php echo (($filters['status'] ?? '') === 'RESOLVED') ? 'selected' : ''; ?>>Resolved</option>
                <option value="CLOSED" <?php echo (($filters['status'] ?? '') === 'CLOSED') ? 'selected' : ''; ?>>Closed</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Priorities</option>
                <option value="LOW" <?php echo (($filters['priority'] ?? '') === 'LOW') ? 'selected' : ''; ?>>Low</option>
                <option value="MEDIUM" <?php echo (($filters['priority'] ?? '') === 'MEDIUM') ? 'selected' : ''; ?>>Medium</option>
                <option value="HIGH" <?php echo (($filters['priority'] ?? '') === 'HIGH') ? 'selected' : ''; ?>>High</option>
                <option value="CRITICAL" <?php echo (($filters['priority'] ?? '') === 'CRITICAL') ? 'selected' : ''; ?>>Critical</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="Network & VPN" <?php echo (($filters['category'] ?? '') === 'Network & VPN') ? 'selected' : ''; ?>>Network & VPN</option>
                <option value="Hardware & Devices" <?php echo (($filters['category'] ?? '') === 'Hardware & Devices') ? 'selected' : ''; ?>>Hardware & Devices</option>
                <option value="IT Security" <?php echo (($filters['category'] ?? '') === 'IT Security') ? 'selected' : ''; ?>>IT Security</option>
                <option value="Developer Tools" <?php echo (($filters['category'] ?? '') === 'Developer Tools') ? 'selected' : ''; ?>>Developer Tools</option>
                <option value="HR Policies" <?php echo (($filters['category'] ?? '') === 'HR Policies') ? 'selected' : ''; ?>>HR Policies</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-aegis-primary w-100">Filter</button>
            <a href="<?php echo base_url('tickets'); ?>" class="btn btn-sm btn-light border" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
</div>

<!-- Tickets Table -->
<div class="aegis-card">
    <div class="table-responsive">
        <table class="table aegis-table">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Requester</th>
                    <th>Title & Summary</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>AI Confidence</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No matching support tickets found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td>
                                <a href="<?php echo base_url('tickets/view/' . $t->id); ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                    <?php echo htmlspecialchars($t->ticket_number); ?>
                                </a>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark"><?php echo htmlspecialchars($t->requester_name ?: 'System'); ?></div>
                                <div class="text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($t->requester_dept ?: 'Enterprise'); ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark text-truncate" style="max-width: 260px;">
                                    <?php echo htmlspecialchars($t->title); ?>
                                </div>
                                <?php if (!empty($t->ai_summary)): ?>
                                    <div class="text-muted text-truncate" style="max-width: 260px; font-size: 11.5px;">
                                        <i class="bi bi-robot text-primary me-1"></i><?php echo htmlspecialchars($t->ai_summary); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($t->category); ?></span></td>
                            <td><span class="badge-priority <?php echo $t->priority; ?>"><?php echo $t->priority; ?></span></td>
                            <td><span class="badge-status <?php echo $t->status; ?>"><?php echo str_replace('_', ' ', $t->status); ?></span></td>
                            <td>
                                <?php if ($t->ai_confidence): ?>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="small fw-bold text-primary"><?php echo number_format($t->ai_confidence, 1); ?>%</span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted text-nowrap"><?php echo date('M d, Y', strtotime($t->created_at)); ?></td>
                            <td>
                                <a href="<?php echo base_url('tickets/view/' . $t->id); ?>" class="btn btn-sm btn-outline-primary py-1 px-2" title="View details">
                                    <i class="bi bi-arrow-right-short fs-6"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
