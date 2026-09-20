<?php
/**
 * Login View with Quick Role Switcher Demo buttons
 */
?>
<div class="card shadow-lg border-0 rounded-4 overflow-hidden">
    <div class="card-header bg-dark text-white p-4 text-center border-0">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary rounded-circle p-3 mb-2 shadow">
            <i class="bi bi-shield-check text-white fs-2"></i>
        </div>
        <h4 class="fw-bold mb-0"><?= htmlspecialchars(Setting::get('company_name', 'AMC Portal')) ?></h4>
        <p class="text-white-50 small mb-0">Field Service & Contract Management</p>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="<?= BASE_URL ?>/login" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= Request::csrfToken() ?>">

            <div class="mb-3">
                <label for="email" class="form-label small fw-bold text-muted">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="name@company.com" required autocomplete="email">
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="password" class="form-label small fw-bold text-muted">Password</label>
                    <a href="<?= BASE_URL ?>/forgot-password" class="text-xs text-primary text-decoration-none">Forgot?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control border-start-0 border-end-0" id="password" name="password" placeholder="••••••••" required>
                    <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword" title="Show / Hide Password" style="border-color: #dee2e6;">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Security CAPTCHA Widget -->
            <div class="mb-3">
                <label for="captcha" class="form-label small fw-bold text-muted d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-shield-check me-1 text-primary"></i> Security CAPTCHA</span>
                    <span class="text-xs text-muted">Case-insensitive</span>
                </label>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="bg-light rounded p-1 border flex-grow-1 text-center overflow-hidden" style="min-height: 44px; display: flex; align-items: center; justify-content: center;">
                        <img id="captchaImg" src="<?= BASE_URL ?>/captcha?t=<?= time() ?>" alt="CAPTCHA Code" style="cursor: pointer; max-height: 38px;" title="Click to refresh CAPTCHA">
                    </div>
                    <button type="button" id="btnRefreshCaptcha" class="btn btn-outline-secondary btn-sm px-3 py-2" title="Generate new CAPTCHA">
                        <i class="bi bi-arrow-clockwise fs-6"></i>
                    </button>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-key"></i></span>
                    <input type="text" class="form-control border-start-0 text-uppercase fw-bold" id="captcha" name="captcha" placeholder="Type the 5 characters above" maxlength="6" required autocomplete="off">
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg fw-bold shadow-sm">
                    <i class="bi bi-box-arrow-in-right me-1"></i> <span id="submitText">Sign In to Portal</span>
                </button>
            </div>
        </form>

        <div class="border-top pt-3 mt-3 text-center">
            <span class="text-muted text-xs text-uppercase fw-bold d-block mb-2">⚡ 1-Click Demo Login as:</span>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <button type="button" class="btn btn-sm btn-outline-dark demo-btn" data-email="admin@amc.local" data-pass="password123" data-role="Admin">
                    <i class="bi bi-shield-lock me-1"></i> Admin
                </button>
                <button type="button" class="btn btn-sm btn-outline-info demo-btn" data-email="support@amc.local" data-pass="password123" data-role="Support">
                    <i class="bi bi-headset me-1"></i> Support
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning demo-btn" data-email="rahul.engineer@amc.local" data-pass="password123" data-role="Field Engineer">
                    <i class="bi bi-tools me-1"></i> Engineer
                </button>
                <button type="button" class="btn btn-sm btn-outline-success demo-btn" data-email="ithead@techcorp.com" data-pass="password123" data-role="Customer">
                    <i class="bi bi-building me-1"></i> Customer
                </button>
            </div>
            <small class="text-muted text-xs mt-2 d-block">Auto-fills credentials + CAPTCHA & logs in instantly</small>
        </div>

        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>/install" class="text-xs text-muted text-decoration-none"><i class="bi bi-gear-wide-connected me-1"></i>System Setup & Database Installer</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var emailField = document.getElementById('email');
    var passField = document.getElementById('password');
    var captchaField = document.getElementById('captcha');
    var captchaImg = document.getElementById('captchaImg');
    var btnRefresh = document.getElementById('btnRefreshCaptcha');
    var form = document.getElementById('loginForm');
    var submitBtn = document.getElementById('submitBtn');
    var submitText = document.getElementById('submitText');
    var togglePassword = document.getElementById('togglePassword');
    var togglePasswordIcon = document.getElementById('togglePasswordIcon');

    // 1. Password Visibility Eye Toggle
    if (togglePassword && passField && togglePasswordIcon) {
        togglePassword.addEventListener('click', function() {
            var currentType = passField.getAttribute('type');
            if (currentType === 'password') {
                passField.setAttribute('type', 'text');
                togglePasswordIcon.className = 'bi bi-eye-slash';
            } else {
                passField.setAttribute('type', 'password');
                togglePasswordIcon.className = 'bi bi-eye';
            }
        });
    }

    // 2. Refresh CAPTCHA
    function refreshCaptcha() {
        if (captchaImg) {
            captchaImg.src = '<?= BASE_URL ?>/captcha?t=' + new Date().getTime();
        }
        if (captchaField) {
            captchaField.value = '';
            captchaField.focus();
        }
    }

    if (btnRefresh) {
        btnRefresh.addEventListener('click', refreshCaptcha);
    }
    if (captchaImg) {
        captchaImg.addEventListener('click', refreshCaptcha);
    }

    // 3. 1-Click Demo Login Handlers
    document.querySelectorAll('.demo-btn').forEach(function(btn) {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            var email = this.getAttribute('data-email');
            var pass = this.getAttribute('data-pass');
            var role = this.getAttribute('data-role');

            emailField.value = email;
            passField.value = pass;

            if (submitText) {
                submitText.textContent = 'Logging in as ' + role + '...';
            }
            if (submitBtn) {
                submitBtn.classList.add('disabled');
            }

            // Fetch current captcha code to automatically satisfy verification
            try {
                var res = await fetch('<?= BASE_URL ?>/captcha-code');
                var data = await res.json();
                if (data && data.code) {
                    captchaField.value = data.code;
                }
            } catch (err) {
                console.warn('Auto captcha retrieval:', err);
            }

            // Submit form
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    });
});
</script>
