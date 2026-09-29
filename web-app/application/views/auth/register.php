<form action="/auth/process_register" method="POST">
    <div class="mb-3">
        <label for="full_name" class="form-label small fw-semibold">Full Legal Name</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control border-start-0 ps-0" id="full_name" name="full_name" value="<?php echo set_value('full_name'); ?>" required placeholder="e.g. John Doe">
        </div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold">Corporate Email</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="<?php echo set_value('email'); ?>" required placeholder="name@aegis.enterprise">
        </div>
    </div>

    <div class="mb-3">
        <label for="department" class="form-label small fw-semibold">Department</label>
        <select class="form-select" id="department" name="department" required>
            <option value="Engineering">Engineering</option>
            <option value="Product Management">Product Management</option>
            <option value="IT Operations">IT Operations</option>
            <option value="Human Resources">Human Resources</option>
            <option value="Finance">Finance</option>
            <option value="Legal & Compliance">Legal & Compliance</option>
            <option value="Marketing">Marketing</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label small fw-semibold">Password (min 8 chars)</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required placeholder="Choose a secure password">
        </div>
    </div>

    <div class="mb-4">
        <label for="password_confirm" class="form-label small fw-semibold">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-shield-check"></i></span>
            <input type="password" class="form-control border-start-0 ps-0" id="password_confirm" name="password_confirm" required placeholder="Re-enter password">
        </div>
    </div>

    <button type="submit" class="btn btn-aegis-primary w-100 py-2 mb-3 fw-semibold">
        Create Enterprise Account
    </button>
</form>

<div class="text-center mt-3 pt-3 border-top">
    <span class="small text-muted">Already registered?</span>
    <a href="/auth/login" class="small fw-semibold text-primary text-decoration-none ms-1">Sign In</a>
</div>
