<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="hrm-auth-card mx-auto">
            <!-- Card Brand Header -->
            <div class="hrm-auth-header">
                <div class="hrm-brand-badge">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="fw-bold mb-1" style="letter-spacing: -0.02em;">HRMate</h3>
                <p class="text-white-50 small mb-0">Human Resource &amp; Workforce Portal</p>
            </div>

            <!-- Card Form Body -->
            <div class="hrm-auth-body-content">
                <!-- Flash Alerts inside Auth card -->
                <?php require dirname(__DIR__) . '/partials/alerts.php'; ?>

                <?php if (!empty($dbError)): ?>
                    <div class="alert alert-warning small mb-4" role="alert">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-database-exclamation fs-5 me-2 text-warning"></i>
                            <strong>Database Setup Required</strong>
                        </div>
                        <p class="mb-2">Could not connect to MySQL: <code><?= e($dbError) ?></code></p>
                        <p class="mb-0 text-muted">
                            Start Apache &amp; MySQL in XAMPP, then import <code>database/schema.sql</code> into phpMyAdmin or MySQL.
                        </p>
                    </div>
                <?php elseif (!$hasAdmin): ?>
                    <div class="alert alert-info small mb-4" role="alert">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bi bi-info-circle-fill fs-5 me-2 text-primary"></i>
                            <strong>Setup Step: Initialize First Admin</strong>
                        </div>
                        <p class="mb-2">No administrator account has been provisioned in the database yet.</p>
                        <p class="mb-1">Run this command in your project terminal to securely create your administrator account:</p>
                        <div class="p-2 bg-dark text-light rounded font-monospace small mb-2">
                            php bin/create_admin.php
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?= url('login') ?>" method="POST" autocomplete="off" novalidate>
                    <!-- CSRF Protection Field -->
                    <?= csrf_field() ?>

                    <!-- Identity Input (Username or Email) -->
                    <div class="mb-3">
                        <label for="login" class="hrm-form-label">Username or Corporate Email</label>
                        <div class="hrm-input-group">
                            <i class="bi bi-person-badge hrm-input-icon"></i>
                            <input 
                                type="text" 
                                id="login" 
                                name="login" 
                                class="hrm-form-control" 
                                placeholder="e.g. admin or john.doe@company.org" 
                                value="<?= old('login') ?>" 
                                required 
                                autofocus
                            >
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="hrm-form-label mb-0">Password</label>
                            <a href="<?= url('forgot-password') ?>" class="small text-decoration-none" style="color: #4b8200; font-weight: 600;">Forgot Password?</a>
                        </div>
                        <div class="hrm-input-group position-relative">
                            <i class="bi bi-lock-fill hrm-input-icon"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="hrm-form-control" 
                                placeholder="Enter your account password" 
                                required
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

                    <!-- Submit Button -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="hrm-btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to HRMS
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
