<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="hrm-auth-card mx-auto">
            <!-- Card Brand Header -->
            <div class="hrm-auth-header">
                <div class="hrm-brand-badge">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h3 class="fw-bold mb-1" style="letter-spacing: -0.02em;">Set New Password</h3>
                <p class="text-white-50 small mb-0">Choose a secure password for your account</p>
            </div>

            <!-- Card Form Body -->
            <div class="hrm-auth-body-content">
                <!-- Flash Alerts -->
                <?php require dirname(__DIR__) . '/partials/alerts.php'; ?>

                <div class="alert alert-light border small mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-person-check-fill text-success fs-5"></i>
                    <div>
                        Resetting password for: <br>
                        <strong class="text-dark"><?= e($email ?? '') ?></strong>
                    </div>
                </div>

                <form action="<?= url('reset-password') ?>" method="POST" autocomplete="off" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= e($token ?? '') ?>">

                    <!-- New Password -->
                    <div class="mb-3">
                        <label for="password" class="hrm-form-label">New Password</label>
                        <div class="hrm-input-group position-relative">
                            <i class="bi bi-lock-fill hrm-input-icon"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="hrm-form-control" 
                                placeholder="At least 6 characters" 
                                required 
                                autofocus
                            >
                            <button 
                                type="button" 
                                class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted js-password-toggle me-2" 
                                style="z-index: 5; text-decoration: none;"
                                tabindex="-1"
                                aria-label="Toggle password visibility"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="hrm-form-label">Confirm New Password</label>
                        <div class="hrm-input-group position-relative">
                            <i class="bi bi-lock-check-fill hrm-input-icon"></i>
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                class="hrm-form-control" 
                                placeholder="Re-enter password" 
                                required
                            >
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="hrm-btn-primary">
                            <i class="bi bi-check2-circle me-2"></i>Update Password &amp; Sign In
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

