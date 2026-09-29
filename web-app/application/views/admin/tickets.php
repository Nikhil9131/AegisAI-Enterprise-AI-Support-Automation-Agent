<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: #0f172a;">Ticket Queue & Human Approvals</h3>
        <p class="text-muted mb-0">Triage incoming tickets, review AI suggestions, and authorize privileged workflows.</p>
    </div>
</div>

<!-- Pending Human-In-The-Loop Queue -->
<div class="aegis-card mb-4 border border-warning" style="background: #fffdfa;">
    <div class="aegis-card-header bg-warning bg-opacity-10">
        <h5 class="aegis-card-title text-dark">
            <i class="bi bi-shield-exclamation text-warning"></i> Human-In-The-Loop Approval Queue
            <span class="badge bg-warning text-dark ms-2"><?php echo count($pending_approvals); ?> Pending Sign-off</span>
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table aegis-table mb-0">
            <thead>
                <tr>
                    <th>Action Proposed</th>
                    <th>Entity Type</th>
                    <th>Target ID</th>
                    <th>Requester</th>
                    <th>Audit Details</th>
                    <th>Approval Controls</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pending_approvals)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted small">No pending sensitive operations requiring human approval at this moment.</td></tr>
                <?php else: ?>
                    <?php foreach ($pending_approvals as $p): ?>
                        <tr>
                            <td><span class="badge bg-danger-subtle text-danger font-monospace"><?php echo htmlspecialchars($p->action); ?></span></td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p->entity_type); ?></span></td>
                            <td class="font-monospace fw-semibold text-primary"><?php echo htmlspecialchars($p->entity_id); ?></td>
                            <td>
                                <div class="fw-semibold small"><?php echo htmlspecialchars($p->user_name); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($p->user_email); ?></small>
                            </td>
                            <td class="small text-muted" style="max-width: 280px;"><?php echo htmlspecialchars($p->details); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <form action="<?php echo base_url('tickets/approve_action/' . str_replace('TICK-', '', $p->entity_id)); ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="decision" value="APPROVE">
                                        <button type="submit" class="btn btn-sm btn-success py-1 px-2 fw-semibold">
                                            <i class="bi bi-check-lg"></i> Authorize
                                        </button>
                                    </form>
                                    <form action="<?php echo base_url('tickets/approve_action/' . str_replace('TICK-', '', $p->entity_id)); ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="decision" value="REJECT">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 fw-semibold">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- All Enterprise Tickets Table -->
<div class="aegis-card">
    <div class="aegis-card-header">
        <h5 class="aegis-card-title"><i class="bi bi-inboxes text-primary"></i> All Enterprise Tickets</h5>
    </div>
    <div class="table-responsive">
        <table class="table aegis-table">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Requester</th>
                    <th>Title & AI Summary</th>
                    <th>Assigned Agent</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Approval Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                    <tr>
                        <td>
                            <a href="<?php echo base_url('tickets/view/' . $t->id); ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                <?php echo htmlspecialchars($t->ticket_number); ?>
                            </a>
                        </td>
                        <td>
                            <div class="small fw-semibold"><?php echo htmlspecialchars($t->requester_name); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($t->requester_dept); ?></small>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($t->title); ?></div>
                            <?php if ($t->ai_suggested_resolution): ?>
                                <small class="text-muted text-truncate d-block" style="max-width: 250px;">
                                    <i class="bi bi-stars text-primary me-1"></i><?php echo htmlspecialchars($t->ai_suggested_resolution); ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="small fw-semibold text-secondary">
                                <?php echo htmlspecialchars($t->agent_name ?: 'Unassigned'); ?>
                            </span>
                        </td>
                        <td><span class="badge-priority <?php echo $t->priority; ?>"><?php echo $t->priority; ?></span></td>
                        <td><span class="badge-status <?php echo $t->status; ?>"><?php echo str_replace('_', ' ', $t->status); ?></span></td>
                        <td>
                            <?php if ($t->requires_approval): ?>
                                <span class="badge <?php echo ($t->approval_status === 'APPROVED') ? 'bg-success' : (($t->approval_status === 'REJECTED') ? 'bg-danger' : 'bg-warning text-dark'); ?>">
                                    <?php echo $t->approval_status ?: 'PENDING'; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo base_url('tickets/view/' . $t->id); ?>" class="btn btn-sm btn-outline-primary py-1 px-2">
                                <i class="bi bi-eye"></i> Triage
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
