<form action="<?php echo base_url('auth/process_forgot_password'); ?>" method="POST">
    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold">Corporate Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" required placeholder="name@aegis.enterprise">
        </div>
        <div class="form-text small text-muted">We will dispatch an automated SSO password reset challenge to your verified inbox.</div>
    </div>

    <button type="submit" class="btn btn-aegis-primary w-100 py-2 mb-3 fw-semibold">
        Send Reset Instructions
    </button>
</form>

<div class="text-center mt-3 pt-3 border-top">
    <a href="<?php echo base_url('auth/login'); ?>" class="small fw-semibold text-primary text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i> Back to Sign In
    </a>
</div>
