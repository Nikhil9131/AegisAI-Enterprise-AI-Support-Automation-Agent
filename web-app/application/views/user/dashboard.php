<div class="row mb-4">
    <div class="col-md-8">
        <h3 class="fw-bold mb-1" style="color: #0f172a;">Welcome back, <?php echo htmlspecialchars($user->full_name); ?></h3>
        <p class="text-muted mb-0">Here is your enterprise technical overview, device compliance, and active support tickets.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="<?php echo base_url('assistant'); ?>" class="btn btn-aegis-primary">
            <i class="bi bi-stars me-1"></i> Ask AI Assistant
        </a>
        <a href="<?php echo base_url('tickets/create'); ?>" class="btn btn-aegis-outline">
            <i class="bi bi-plus-lg me-1"></i> New Ticket
        </a>
    </div>
</div>

<!-- Stat Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">My Total Tickets</span>
                <div class="stat-icon primary"><i class="bi bi-ticket-detailed"></i></div>
            </div>
            <div class="stat-value"><?php echo $ticket_stats['total']; ?></div>
            <div class="stat-footer text-muted">
                <i class="bi bi-archive"></i> Lifetime recorded requests
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Open Tickets</span>
                <div class="stat-icon info"><i class="bi bi-clock-history"></i></div>
            </div>
            <div class="stat-value text-primary"><?php echo $ticket_stats['open']; ?></div>
            <div class="stat-footer text-primary">
                <i class="bi bi-arrow-up-right"></i> Awaiting resolution
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">In Progress</span>
                <div class="stat-icon warning"><i class="bi bi-gear-wide-connected"></i></div>
            </div>
            <div class="stat-value text-warning"><?php echo $ticket_stats['in_progress']; ?></div>
            <div class="stat-footer text-muted">
                <i class="bi bi-person-check"></i> Actively being handled
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">Resolved</span>
                <div class="stat-icon success"><i class="bi bi-check-circle"></i></div>
            </div>
            <div class="stat-value text-success"><?php echo $ticket_stats['resolved']; ?></div>
            <div class="stat-footer text-success">
                <i class="bi bi-shield-check"></i> Closed successfully
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Active Support Tickets Table -->
    <div class="col-lg-8">
        <div class="aegis-card">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-ticket-perforated text-primary"></i> Recent Support Requests</h5>
                <a href="<?php echo base_url('tickets'); ?>" class="btn btn-sm btn-link text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table aegis-table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($my_tickets)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No active support tickets found. Everything is running smoothly!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($my_tickets as $t): ?>
                                <tr>
                                    <td><span class="fw-semibold text-primary font-monospace"><?php echo htmlspecialchars($t->ticket_number); ?></span></td>
                                    <td>
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 240px;"><?php echo htmlspecialchars($t->title); ?></div>
                                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($t->created_at)); ?></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($t->category); ?></span></td>
                                    <td><span class="badge-priority <?php echo $t->priority; ?>"><?php echo $t->priority; ?></span></td>
                                    <td><span class="badge-status <?php echo $t->status; ?>"><?php echo str_replace('_', ' ', $t->status); ?></span></td>
                                    <td>
                                        <a href="<?php echo base_url('tickets/view/' . $t->id); ?>" class="btn btn-sm btn-outline-primary py-1 px-2">
                                            <i class="bi bi-eye"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick AI Assistant Banner -->
        <div class="card border-0 p-4 rounded-3 text-white" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%); box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-white bg-opacity-20 rounded-circle text-white fs-3">
                    <i class="bi bi-robot"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Need immediate IT or business assistance?</h5>
                    <p class="mb-0 small text-white text-opacity-75">Aegis AI scans verified corporate policies, your registered devices, and IT playbooks in real-time.</p>
                </div>
                <div class="ms-auto">
                    <a href="<?php echo base_url('assistant'); ?>" class="btn btn-light fw-semibold text-primary px-3 text-nowrap">
                        Launch Chat <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Devices & Quick Resources -->
    <div class="col-lg-4">
        <!-- Assigned Corporate Devices -->
        <div class="aegis-card">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-laptop text-primary"></i> Assigned Devices</h5>
                <span class="badge bg-primary-subtle text-primary"><?php echo count($my_devices); ?> Registered</span>
            </div>
            <div class="aegis-card-body p-0">
                <?php if (empty($my_devices)): ?>
                    <div class="p-4 text-center text-muted small">No corporate devices currently registered to your identity.</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($my_devices as $dev): ?>
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="fw-semibold small"><?php echo htmlspecialchars($dev->device_name); ?></div>
                                    <span class="badge <?php echo ($dev->compliance_status === 'COMPLIANT') ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?> border">
                                        <?php echo $dev->compliance_status; ?>
                                    </span>
                                </div>
                                <div class="text-muted" style="font-size: 11.5px;">
                                    <div><i class="bi bi-hdd-network me-1"></i> <?php echo htmlspecialchars($dev->os_version); ?></div>
                                    <div><i class="bi bi-upc-scan me-1"></i> Serial: <span class="font-monospace"><?php echo htmlspecialchars($dev->serial_number); ?></span></div>
                                    <div><i class="bi bi-shield-lock me-1"></i> VPN Access: <?php echo ($dev->vpn_access_enabled) ? '<span class="text-success fw-semibold">Enabled</span>' : '<span class="text-danger fw-semibold">Disabled</span>'; ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Featured Knowledge Articles -->
        <div class="aegis-card">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-bookmark-check text-primary"></i> IT Policies & Guides</h5>
                <a href="<?php echo base_url('knowledge'); ?>" class="btn btn-sm btn-link text-decoration-none">Explore</a>
            </div>
            <div class="aegis-card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($recent_docs as $doc): ?>
                        <li class="list-group-item p-3">
                            <a href="<?php echo base_url('knowledge/search?q=' . urlencode($doc->title)); ?>" class="text-decoration-none text-dark d-flex align-items-center justify-content-between">
                                <span class="small fw-semibold text-truncate"><?php echo htmlspecialchars($doc->title); ?></span>
                                <i class="bi bi-chevron-right text-muted small"></i>
                            </a>
                            <div class="d-flex gap-2 mt-1">
                                <span class="badge bg-light text-muted border" style="font-size: 10px;"><?php echo $doc->category_name; ?></span>
                                <span class="badge bg-primary-subtle text-primary" style="font-size: 10px;"><?php echo $doc->chunk_count; ?> chunks indexed</span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
