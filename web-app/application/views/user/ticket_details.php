<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="<?php echo base_url('tickets'); ?>" class="small text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Back to Support Queue
        </a>
        <div class="d-flex align-items-center gap-2 mt-1">
            <span class="fs-4 fw-bold font-monospace text-primary"><?php echo htmlspecialchars($ticket->ticket_number); ?></span>
            <span class="badge-status <?php echo $ticket->status; ?>"><?php echo str_replace('_', ' ', $ticket->status); ?></span>
            <span class="badge-priority <?php echo $ticket->priority; ?>"><?php echo $ticket->priority; ?></span>
        </div>
    </div>

    <!-- Agent Triage Controls if Agent or Admin -->
    <?php if ($is_agent_or_admin): ?>
        <button class="btn btn-sm btn-aegis-outline" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
            <i class="bi bi-sliders me-1"></i> Update Status / Assign
        </button>
    <?php endif; ?>
</div>

<!-- Human-In-The-Loop Approval Banner if Pending -->
<?php if ($ticket->requires_approval && $ticket->approval_status === 'PENDING'): ?>
    <div class="alert alert-warning border border-warning d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 shadow-sm">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-shield-exclamation fs-3 text-warning"></i>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Human-In-The-Loop Sign-off Required</h6>
                <p class="small text-muted mb-0">Sensitive Action Detected: <strong><?php echo htmlspecialchars($ticket->approval_action ?? 'PRIVILEGED_ACTION'); ?></strong>. Requires Admin/Support Lead confirmation before executing.</p>
            </div>
        </div>
        <?php if ($is_agent_or_admin): ?>
            <div class="d-flex gap-2">
                <form action="<?php echo base_url('tickets/approve_action/' . $ticket->id); ?>" method="POST" class="d-inline">
                    <input type="hidden" name="decision" value="APPROVE">
                    <button type="submit" class="btn btn-sm btn-success fw-semibold"><i class="bi bi-check-lg me-1"></i> Approve Action</button>
                </form>
                <form action="<?php echo base_url('tickets/approve_action/' . $ticket->id); ?>" method="POST" class="d-inline">
                    <input type="hidden" name="decision" value="REJECT">
                    <button type="submit" class="btn btn-sm btn-outline-danger fw-semibold"><i class="bi bi-x-lg me-1"></i> Reject</button>
                </form>
            </div>
        <?php else: ?>
            <span class="badge bg-warning text-dark px-3 py-2">Pending Manager Approval</span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column: Ticket Description and Comment Thread -->
    <div class="col-lg-8">
        <!-- Main Issue Description -->
        <div class="aegis-card mb-4">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><?php echo htmlspecialchars($ticket->title); ?></h5>
                <span class="small text-muted"><i class="bi bi-clock me-1"></i> Submitted <?php echo date('M d, Y H:i', strtotime($ticket->created_at)); ?></span>
            </div>
            <div class="aegis-card-body">
                <div class="p-3 bg-light rounded-2 text-dark mb-3" style="line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($ticket->description)); ?>
                </div>

                <!-- AI Analysis Card -->
                <?php if (!empty($ticket->ai_summary) || !empty($ticket->ai_suggested_resolution)): ?>
                    <div class="card border border-primary border-opacity-25 rounded-3 mb-0" style="background-color: #f8fbff;">
                        <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between pt-3 pb-0">
                            <span class="badge bg-primary text-white"><i class="bi bi-stars me-1"></i> AI-Generated Automated Triage</span>
                            <?php if ($ticket->ai_confidence): ?>
                                <span class="badge bg-primary-subtle text-primary border"><?php echo number_format($ticket->ai_confidence, 1); ?>% Confidence</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($ticket->ai_summary)): ?>
                                <div class="mb-2">
                                    <div class="fw-bold small text-muted text-uppercase" style="font-size: 11px;">Issue Summary:</div>
                                    <div class="small text-dark"><?php echo htmlspecialchars($ticket->ai_summary); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($ticket->ai_suggested_resolution)): ?>
                                <div>
                                    <div class="fw-bold small text-muted text-uppercase" style="font-size: 11px;">Grounded Suggested Resolution:</div>
                                    <div class="small text-secondary"><?php echo nl2br(htmlspecialchars($ticket->ai_suggested_resolution)); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity & Comments Stream -->
        <div class="aegis-card">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-chat-square-text text-primary"></i> Conversation & Triage Updates</h5>
                <span class="badge bg-light text-muted border"><?php echo count($comments); ?> Entries</span>
            </div>
            <div class="aegis-card-body">
                <?php if (empty($comments)): ?>
                    <div class="text-center py-4 text-muted small">No comments logged on this ticket yet.</div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <?php foreach ($comments as $c): ?>
                            <div class="p-3 rounded-2 border <?php echo $c->is_ai_generated ? 'bg-primary-subtle border-primary-subtle' : ($c->is_internal ? 'bg-warning-subtle border-warning-subtle' : 'bg-light'); ?>">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($c->is_ai_generated): ?>
                                            <span class="badge bg-primary"><i class="bi bi-robot me-1"></i> Aegis AI Agent</span>
                                        <?php else: ?>
                                            <span class="fw-semibold small"><?php echo htmlspecialchars($c->full_name ?: 'System'); ?></span>
                                            <span class="badge bg-secondary" style="font-size: 10px;"><?php echo htmlspecialchars($c->role_name ?: 'USER'); ?></span>
                                        <?php endif; ?>
                                        <?php if ($c->is_internal): ?>
                                            <span class="badge bg-warning text-dark" style="font-size: 10px;"><i class="bi bi-eye-slash me-1"></i> Internal Staff Note</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-muted" style="font-size: 11px;"><?php echo date('M d, Y H:i', strtotime($c->created_at)); ?></span>
                                </div>
                                <div class="small text-dark" style="line-height: 1.55;">
                                    <?php echo nl2br(htmlspecialchars($c->comment)); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Add Comment Form -->
                <form action="<?php echo base_url('tickets/add_comment/' . $ticket->id); ?>" method="POST">
                    <div class="mb-3">
                        <label for="comment" class="form-label small fw-semibold">Add Response / Diagnostic Update</label>
                        <textarea class="form-control" name="comment" rows="3" required placeholder="Type your response to the employee or support team..."></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <?php if ($is_agent_or_admin): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="is_internal">
                                <label class="form-check-label small text-muted" for="is_internal">
                                    <i class="bi bi-lock me-1"></i> Internal Staff Note (hidden from employee)
                                </label>
                            </div>
                        <?php else: ?>
                            <div></div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-aegis-primary btn-sm px-3">
                            <i class="bi bi-send me-1"></i> Post Comment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Ticket Meta & Audit Timeline -->
    <div class="col-lg-4">
        <!-- Ticket Meta Card -->
        <div class="aegis-card">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-info-circle text-primary"></i> Ticket Information</h5>
            </div>
            <div class="aegis-card-body p-0">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between py-2 px-3">
                        <span class="text-muted">Requester:</span>
                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($ticket->requester_name); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 px-3">
                        <span class="text-muted">Department:</span>
                        <span class="text-dark"><?php echo htmlspecialchars($ticket->requester_dept ?: 'Enterprise'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 px-3">
                        <span class="text-muted">Assigned Agent:</span>
                        <span class="fw-semibold text-primary"><?php echo htmlspecialchars($ticket->agent_name ?: 'Unassigned (Queue)'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 px-3">
                        <span class="text-muted">Category:</span>
                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($ticket->category); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between py-2 px-3">
                        <span class="text-muted">Last Updated:</span>
                        <span class="text-muted"><?php echo date('M d, H:i', strtotime($ticket->updated_at)); ?></span>
                    </li>
                    <?php if ($ticket->resolved_at): ?>
                        <li class="list-group-item d-flex justify-content-between py-2 px-3 bg-success-subtle">
                            <span class="text-success fw-semibold">Resolved At:</span>
                            <span class="text-success"><?php echo date('M d, Y H:i', strtotime($ticket->resolved_at)); ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Audit History Timeline -->
        <div class="aegis-card">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-clock-history text-primary"></i> Lifecycle Audit</h5>
            </div>
            <div class="aegis-card-body p-3">
                <?php if (empty($history)): ?>
                    <div class="text-muted small">No lifecycle events recorded.</div>
                <?php else: ?>
                    <div class="timeline small">
                        <?php foreach ($history as $h): ?>
                            <div class="border-start border-2 ps-3 pb-3 position-relative" style="border-color: #cbd5e1 !important;">
                                <i class="bi bi-circle-fill position-absolute text-primary" style="left: -5px; top: 0; font-size: 8px;"></i>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($h->action); ?></div>
                                <div class="text-muted" style="font-size: 11px;">
                                    <?php echo htmlspecialchars($h->full_name ?: 'System'); ?> &bull; <?php echo date('M d, H:i', strtotime($h->created_at)); ?>
                                </div>
                                <?php if ($h->new_value): ?>
                                    <div class="text-secondary mt-1" style="font-size: 11px;">
                                        &rarr; <?php echo htmlspecialchars($h->new_value); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Update Status / Assign (for Agents & Admins) -->
<?php if ($is_agent_or_admin): ?>
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo base_url('tickets/update_status/' . $ticket->id); ?>" method="POST">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Update Ticket Triage & Status</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="OPEN" <?php echo ($ticket->status === 'OPEN') ? 'selected' : ''; ?>>Open</option>
                            <option value="IN_PROGRESS" <?php echo ($ticket->status === 'IN_PROGRESS') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="WAITING_FOR_USER" <?php echo ($ticket->status === 'WAITING_FOR_USER') ? 'selected' : ''; ?>>Waiting for User</option>
                            <option value="ESCALATED" <?php echo ($ticket->status === 'ESCALATED') ? 'selected' : ''; ?>>Escalated</option>
                            <option value="RESOLVED" <?php echo ($ticket->status === 'RESOLVED') ? 'selected' : ''; ?>>Resolved</option>
                            <option value="CLOSED" <?php echo ($ticket->status === 'CLOSED') ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="LOW" <?php echo ($ticket->priority === 'LOW') ? 'selected' : ''; ?>>Low</option>
                            <option value="MEDIUM" <?php echo ($ticket->priority === 'MEDIUM') ? 'selected' : ''; ?>>Medium</option>
                            <option value="HIGH" <?php echo ($ticket->priority === 'HIGH') ? 'selected' : ''; ?>>High</option>
                            <option value="CRITICAL" <?php echo ($ticket->priority === 'CRITICAL') ? 'selected' : ''; ?>>Critical</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assigned Agent</label>
                        <select name="assigned_agent_id" class="form-select">
                            <option value="">-- Unassigned --</option>
                            <option value="2" <?php echo ($ticket->assigned_agent_id == 2) ? 'selected' : ''; ?>>Sarah Jenkins (Support Lead)</option>
                            <option value="3" <?php echo ($ticket->assigned_agent_id == 3) ? 'selected' : ''; ?>>Marcus Brody (Infrastructure)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-aegis-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
