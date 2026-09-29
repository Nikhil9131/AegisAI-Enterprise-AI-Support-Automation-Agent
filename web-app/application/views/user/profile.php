<div class="row g-4">
    <!-- Left Column: User Profile Card -->
    <div class="col-lg-4">
        <div class="aegis-card text-center p-4">
            <div class="d-inline-flex align-items-center justify-content-center brand-icon-box mx-auto mb-3" style="width: 72px; height: 72px; font-size: 32px; border-radius: 50%;">
                <?php echo strtoupper(substr($user->full_name, 0, 1)); ?>
            </div>
            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($user->full_name); ?></h5>
            <p class="text-muted small mb-2"><?php echo htmlspecialchars($user->email); ?></p>
            <div class="d-flex justify-content-center gap-2 mb-3">
                <span class="badge bg-primary-subtle text-primary border"><?php echo htmlspecialchars($user->role_name); ?></span>
                <span class="badge bg-success-subtle text-success border">Active Staff</span>
            </div>
            <hr>
            <div class="text-start small">
                <div class="mb-2"><strong>Employee Code:</strong> <span class="text-muted"><?php echo htmlspecialchars($employee->employee_code ?? 'EMP-' . (1000 + $user->id)); ?></span></div>
                <div class="mb-2"><strong>Department:</strong> <span class="text-muted"><?php echo htmlspecialchars($user->department ?? 'Engineering'); ?></span></div>
                <div class="mb-2"><strong>Job Title:</strong> <span class="text-muted"><?php echo htmlspecialchars($employee->job_title ?? 'Software Engineer'); ?></span></div>
                <div class="mb-2"><strong>Office Location:</strong> <span class="text-muted"><?php echo htmlspecialchars($employee->office_location ?? 'Headquarters'); ?></span></div>
                <div><strong>Reporting Manager:</strong> <span class="text-muted"><?php echo htmlspecialchars($employee->manager_name ?? 'Alexander Pierce'); ?></span></div>
            </div>
        </div>

        <!-- Security / Password Update Card -->
        <div class="aegis-card p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-shield-lock text-primary me-1"></i> Update Password</h6>
            <form action="<?php echo base_url('profile/update_password'); ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">New Password</label>
                    <input type="password" name="new_password" class="form-control form-control-sm" required minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control form-control-sm" required minlength="8">
                </div>
                <button type="submit" class="btn btn-sm btn-aegis-primary w-100">Update Credentials</button>
            </form>
        </div>
    </div>

    <!-- Right Column: Registered Corporate Hardware Devices -->
    <div class="col-lg-8">
        <div class="aegis-card">
            <div class="aegis-card-header">
                <h5 class="aegis-card-title"><i class="bi bi-laptop text-primary"></i> Registered Corporate Devices & Endpoints</h5>
                <span class="badge bg-primary-subtle text-primary"><?php echo count($devices); ?> Devices Linked</span>
            </div>
            <div class="aegis-card-body p-0">
                <?php if (empty($devices)): ?>
                    <div class="p-4 text-center text-muted">No corporate hardware devices currently bound to this user identity.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table aegis-table mb-0">
                            <thead>
                                <tr>
                                    <th>Device Name</th>
                                    <th>Type</th>
                                    <th>OS & Build</th>
                                    <th>Serial #</th>
                                    <th>VPN State</th>
                                    <th>Compliance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($devices as $d): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark"><?php echo htmlspecialchars($d->device_name); ?></div>
                                            <small class="text-muted font-monospace"><?php echo htmlspecialchars($d->ip_address ?? '10.240.x.x'); ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($d->device_type); ?></span></td>
                                        <td class="small text-muted"><?php echo htmlspecialchars($d->os_version); ?></td>
                                        <td class="font-monospace small text-primary"><?php echo htmlspecialchars($d->serial_number); ?></td>
                                        <td>
                                            <?php if ($d->vpn_access_enabled): ?>
                                                <span class="badge bg-success-subtle text-success border"><i class="bi bi-shield-check me-1"></i> Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border">Suspended</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo ($d->compliance_status === 'COMPLIANT') ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?> border">
                                                <?php echo htmlspecialchars($d->compliance_status); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Identity & Role Permissions Overview -->
        <div class="aegis-card p-4">
            <h6 class="fw-bold mb-2"><i class="bi bi-key text-primary me-1"></i> Identity Provider & RBAC Scope</h6>
            <p class="small text-muted mb-3">Your enterprise account permissions are enforced server-side and synced with corporate Okta SSO.</p>
            <div class="row g-2 small">
                <div class="col-sm-6">
                    <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                        <span>AI Assistant Access</span>
                        <span class="badge bg-success">Authorized</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                        <span>Enterprise RAG Search</span>
                        <span class="badge bg-success">Authorized</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                        <span>Ticket Creation & Management</span>
                        <span class="badge bg-success">Authorized</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                        <span>Admin Console & Traces</span>
                        <span class="badge <?php echo $this->rbac->is_admin() ? 'bg-primary' : 'bg-secondary'; ?>">
                            <?php echo $this->rbac->is_admin() ? 'Granted' : 'Admin Only'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
