<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) . ' — Aegis AI Enterprise' : 'Aegis AI — Enterprise Support & Business Automation Platform'; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Aegis Light Bluish Theme -->
    <link href="<?php echo base_url('assets/css/aegis_theme.css'); ?>" rel="stylesheet">
</head>
<body>
<div class="app-container">
    <!-- Sidebar Navigation -->
    <?php $this->load->view('layouts/sidebar'); ?>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Top Navigation Bar -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small"><i class="bi bi-shield-check text-primary me-1"></i> Aegis Cloud Zero-Trust Verified</span>
                <span class="badge bg-light text-primary border"><i class="bi bi-cpu me-1"></i> LangGraph Multi-Agent Active</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-light btn-sm position-relative rounded-circle p-2" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="width: 320px;">
                        <li class="dropdown-header fw-bold">Enterprise Notifications</li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item py-2" href="<?php echo base_url('tickets/view/1'); ?>">
                                <div class="small fw-semibold">WireGuard Handshake Update</div>
                                <div class="text-muted small text-truncate">Agent Sarah Jenkins posted diagnostic update.</div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?php echo base_url('admin/tickets'); ?>">
                                <div class="small fw-semibold text-warning"><i class="bi bi-exclamation-triangle me-1"></i> Approval Queued</div>
                                <div class="text-muted small text-truncate">Ticket TICK-8016 requires Security Admin sign-off.</div>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- User Profile Menu -->
                <div class="dropdown">
                    <button class="btn btn-light btn-sm d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown">
                        <div class="user-avatar-sm" style="width: 28px; height: 28px; font-size: 12px;">
                            <?php echo strtoupper(substr($this->session->userdata('full_name') ?: 'U', 0, 1)); ?>
                        </div>
                        <span class="fw-semibold small d-none d-md-inline"><?php echo htmlspecialchars($this->session->userdata('full_name') ?: 'Guest'); ?></span>
                        <i class="bi bi-chevron-down small text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li>
                            <div class="px-3 py-1">
                                <div class="fw-bold small"><?php echo htmlspecialchars($this->session->userdata('full_name')); ?></div>
                                <div class="text-muted" style="font-size: 11px;"><?php echo htmlspecialchars($this->session->userdata('email')); ?></div>
                                <span class="badge bg-primary-subtle text-primary mt-1"><?php echo htmlspecialchars($this->session->userdata('role_name')); ?></span>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item small" href="<?php echo base_url('profile'); ?>"><i class="bi bi-person me-2"></i>My Profile & Devices</a></li>
                        <?php if ($this->rbac->is_admin()): ?>
                        <li><a class="dropdown-item small" href="<?php echo base_url('admin'); ?>"><i class="bi bi-speedometer2 me-2"></i>Admin Console</a></li>
                        <li><a class="dropdown-item small" href="<?php echo base_url('admin/logs'); ?>"><i class="bi bi-journal-code me-2"></i>AI Agent Traces</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item small text-danger" href="<?php echo base_url('auth/logout'); ?>"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Flash Alert Messages -->
        <div class="px-4 pt-3">
            <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 py-2" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div><?php echo $this->session->flashdata('success'); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 py-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?php echo $this->session->flashdata('error'); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Main Body -->
        <main class="content-body">
