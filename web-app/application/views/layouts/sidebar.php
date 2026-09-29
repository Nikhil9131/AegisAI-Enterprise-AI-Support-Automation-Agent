<aside class="sidebar">
    <!-- Brand Header -->
    <div class="brand-header">
        <a href="<?php echo base_url('dashboard'); ?>" class="brand-logo">
            <div class="brand-icon-box">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <div>
                <div class="brand-text">AEGIS <span class="text-primary">AI</span></div>
            </div>
        </a>
        <span class="brand-badge ms-auto">ENTERPRISE</span>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <?php $current_uri = $this->uri->uri_string(); ?>

        <div class="nav-section-title">Workspace</div>
        <a href="<?php echo base_url('dashboard'); ?>" class="sidebar-item <?php echo ($current_uri == 'dashboard' || $current_uri == '') ? 'active' : ''; ?>">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>
        <a href="<?php echo base_url('assistant'); ?>" class="sidebar-item <?php echo (strpos($current_uri, 'assistant') === 0) ? 'active' : ''; ?>">
            <i class="bi bi-stars text-primary"></i> AI Assistant
        </a>
        <a href="<?php echo base_url('tickets'); ?>" class="sidebar-item <?php echo ($current_uri == 'tickets' || $current_uri == 'tickets/my') ? 'active' : ''; ?>">
            <i class="bi bi-ticket-perforated"></i> My Tickets
        </a>
        <a href="<?php echo base_url('tickets/create'); ?>" class="sidebar-item <?php echo ($current_uri == 'tickets/create') ? 'active' : ''; ?>">
            <i class="bi bi-plus-circle"></i> Create Ticket
        </a>

        <div class="nav-section-title">Knowledge & Docs</div>
        <a href="<?php echo base_url('knowledge'); ?>" class="sidebar-item <?php echo ($current_uri == 'knowledge') ? 'active' : ''; ?>">
            <i class="bi bi-book"></i> Knowledge Base
        </a>
        <a href="<?php echo base_url('knowledge/search'); ?>" class="sidebar-item <?php echo ($current_uri == 'knowledge/search') ? 'active' : ''; ?>">
            <i class="bi bi-search"></i> Semantic Search
        </a>

        <div class="nav-section-title">Account</div>
        <a href="<?php echo base_url('profile'); ?>" class="sidebar-item <?php echo ($current_uri == 'profile') ? 'active' : ''; ?>">
            <i class="bi bi-person-badge"></i> Profile & Devices
        </a>

        <?php if ($this->rbac->is_admin()): ?>
        <div class="nav-section-title">Administration</div>
        <a href="<?php echo base_url('admin'); ?>" class="sidebar-item <?php echo ($current_uri == 'admin') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i> Admin Dashboard
        </a>
        <a href="<?php echo base_url('admin/users'); ?>" class="sidebar-item <?php echo ($current_uri == 'admin/users') ? 'active' : ''; ?>">
            <i class="bi bi-people"></i> User Management
        </a>
        <a href="<?php echo base_url('admin/tickets'); ?>" class="sidebar-item <?php echo ($current_uri == 'admin/tickets') ? 'active' : ''; ?>">
            <i class="bi bi-inboxes"></i> Ticket Management
        </a>
        <a href="<?php echo base_url('admin/knowledge'); ?>" class="sidebar-item <?php echo ($current_uri == 'admin/knowledge') ? 'active' : ''; ?>">
            <i class="bi bi-journal-bookmark"></i> KB Management
        </a>
        <a href="<?php echo base_url('admin/documents'); ?>" class="sidebar-item <?php echo ($current_uri == 'admin/documents') ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-arrow-up"></i> Document Vault
        </a>
        <a href="<?php echo base_url('admin/analytics'); ?>" class="sidebar-item <?php echo ($current_uri == 'admin/analytics') ? 'active' : ''; ?>">
            <i class="bi bi-graph-up-arrow"></i> AI Analytics
        </a>
        <a href="<?php echo base_url('admin/logs'); ?>" class="sidebar-item <?php echo ($current_uri == 'admin/logs') ? 'active' : ''; ?>">
            <i class="bi bi-terminal"></i> Agent Logs & Traces
        </a>
        <a href="<?php echo base_url('admin/settings'); ?>" class="sidebar-item <?php echo ($current_uri == 'admin/settings') ? 'active' : ''; ?>">
            <i class="bi bi-sliders"></i> System Settings
        </a>
        <?php endif; ?>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="user-avatar-sm">
            <?php echo strtoupper(substr($this->session->userdata('full_name') ?: 'U', 0, 1)); ?>
        </div>
        <div class="user-info-text">
            <div class="user-name-label"><?php echo htmlspecialchars($this->session->userdata('full_name') ?: 'User'); ?></div>
            <div class="user-role-label"><?php echo htmlspecialchars($this->session->userdata('role_name') ?: 'EMPLOYEE'); ?></div>
        </div>
        <a href="<?php echo base_url('auth/logout'); ?>" class="text-muted text-decoration-none" title="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </a>
    </div>
</aside>
