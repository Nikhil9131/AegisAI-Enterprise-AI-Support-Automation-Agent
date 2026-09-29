<form action="/auth/process_login" method="POST">
    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold">Corporate Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="<?php echo set_value('email', 'admin@aegis.enterprise'); ?>" required placeholder="name@aegis.enterprise">
        </div>
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label small fw-semibold mb-0">Password</label>
            <a href="/auth/forgot_password" class="small text-decoration-none text-primary">Forgot password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" value="Admin@123" required placeholder="Enter your password">
        </div>
    </div>

    <div class="mb-4 form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember" checked>
        <label class="form-check-label small text-muted" for="remember">Keep me signed in on this workstation</label>
    </div>

    <button type="submit" class="btn btn-aegis-primary w-100 py-2 mb-3 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Enterprise Workspace
    </button>
</form>

<div class="mt-4 pt-3 border-top">
    <div class="text-center text-muted small fw-semibold mb-2">QUICK DEMO ROLES (1-CLICK)</div>
    <div class="d-grid gap-2">
        <button type="button" class="btn btn-sm btn-outline-primary d-flex justify-content-between align-items-center" onclick="fillCreds('admin@aegis.enterprise', 'Admin@123')">
            <span><i class="bi bi-shield-shaded me-1"></i> System Administrator</span>
            <span class="badge bg-primary">ADMIN</span>
        </button>
        <button type="button" class="btn btn-sm btn-outline-info d-flex justify-content-between align-items-center text-dark" onclick="fillCreds('agent.sarah@aegis.enterprise', 'Agent@123')">
            <span><i class="bi bi-headset me-1"></i> Support Agent (Sarah)</span>
            <span class="badge bg-info text-white">AGENT</span>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary d-flex justify-content-between align-items-center" onclick="fillCreds('rahul.sharma@aegis.enterprise', 'User@123')">
            <span><i class="bi bi-person me-1"></i> Employee (Rahul Sharma)</span>
            <span class="badge bg-secondary">EMPLOYEE</span>
        </button>
    </div>
</div>

<div class="text-center mt-4">
    <span class="small text-muted">Don't have an account?</span>
    <a href="/auth/register" class="small fw-semibold text-primary text-decoration-none ms-1">Register new user</a>
</div>

<script>
function fillCreds(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
