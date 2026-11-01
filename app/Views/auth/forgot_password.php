<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="hrm-auth-card mx-auto">
            <!-- Card Brand Header -->
            <div class="hrm-auth-header">
                <div class="hrm-brand-badge">
                    <i class="bi bi-key-fill"></i>
                </div>
                <h3 class="fw-bold mb-1" style="letter-spacing: -0.02em;">Password Recovery</h3>
                <p class="text-white-50 small mb-0">Enter your email to receive recovery instructions</p>
            </div>

            <!-- Card Form Body -->
            <div class="hrm-auth-body-content">
                <!-- Flash Alerts -->
                <?php require dirname(__DIR__) . '/partials/alerts.php'; ?>

                <p class="text-secondary small mb-4">
                    Enter the corporate email address associated with your employee account. We'll send you a secure link to reset your password.
                </p>

                <form action="<?= url('forgot-password') ?>" method="POST" autocomplete="off" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label for="email" class="hrm-form-label">Corporate Email Address</label>
                        <div class="hrm-input-group">
                            <i class="bi bi-envelope-fill hrm-input-icon"></i>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="hrm-form-control" 
                                placeholder="e.g. employee@company.org" 
                                required 
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="hrm-btn-primary">
                            <i class="bi bi-send-fill me-2"></i>Send Reset Link
                        </button>
                    </div>

                    <div class="text-center pt-2 border-top">
                        <a href="<?= url('login') ?>" class="text-decoration-none small text-secondary fw-semibold">
                            <i class="bi bi-arrow-left me-1"></i>Back to Sign In
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

