<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: #0f172a;">User & Role Management</h3>
        <p class="text-muted mb-0">Manage enterprise employee access, assign support desk agents, and enforce role boundaries.</p>
    </div>
</div>

<div class="aegis-card">
    <div class="table-responsive">
        <table class="table aegis-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Department</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="font-monospace text-muted">#<?php echo $u->id; ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar-sm" style="width: 32px; height: 32px; font-size: 13px;">
                                    <?php echo strtoupper(substr($u->full_name, 0, 1)); ?>
                                </div>
                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($u->full_name); ?></span>
                            </div>
                        </td>
                        <td class="small text-muted"><?php echo htmlspecialchars($u->email); ?></td>
                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($u->department ?? 'General'); ?></span></td>
                        <td>
                            <span class="badge <?php echo ($u->role_name === 'ADMIN') ? 'bg-primary' : (($u->role_name === 'AGENT') ? 'bg-info text-white' : 'bg-secondary'); ?>">
                                <?php echo htmlspecialchars($u->role_name); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo ($u->status === 'ACTIVE') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> border">
                                <?php echo htmlspecialchars($u->status); ?>
                            </span>
                        </td>
                        <td class="small text-muted"><?php echo $u->last_login ? date('M d, H:i', strtotime($u->last_login)) : 'Never'; ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $u->id; ?>">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Edit User Modal -->
                    <div class="modal fade" id="editUserModal<?php echo $u->id; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="<?php echo base_url('admin/update_user_role'); ?>" method="POST">
                                    <input type="hidden" name="user_id" value="<?php echo $u->id; ?>">
                                    <div class="modal-header">
                                        <h6 class="modal-title fw-bold">Modify Permissions: <?php echo htmlspecialchars($u->full_name); ?></h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Assigned Role</label>
                                            <select name="role_id" class="form-select">
                                                <?php foreach ($roles as $r): ?>
                                                    <option value="<?php echo $r->id; ?>" <?php echo ($u->role_id == $r->id) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($r->name); ?> &bull; <?php echo htmlspecialchars($r->description); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Account Status</label>
                                            <select name="status" class="form-select">
                                                <option value="ACTIVE" <?php echo ($u->status === 'ACTIVE') ? 'selected' : ''; ?>>ACTIVE</option>
                                                <option value="INACTIVE" <?php echo ($u->status === 'INACTIVE') ? 'selected' : ''; ?>>INACTIVE</option>
                                                <option value="SUSPENDED" <?php echo ($u->status === 'SUSPENDED') ? 'selected' : ''; ?>>SUSPENDED</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-sm btn-aegis-primary">Update User</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
