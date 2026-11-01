<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="hrm-auth-card mx-auto text-center p-4">
            <div class="my-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger" style="width: 72px; height: 72px; font-size: 2rem;">
                    <i class="bi bi-exclamation-octagon"></i>
                </span>
            </div>
            <h2 class="fw-bold text-dark mb-1">500 Server Error</h2>
            <h5 class="text-secondary mb-3">Internal System Exception</h5>
            <p class="text-muted small mb-4">
                An unexpected condition was encountered on the server. The incident has been recorded in the central system audit log.
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="<?= url('login') ?>" class="btn hrm-btn-primary px-4 py-2" style="width: auto;">
                    <i class="bi bi-arrow-clockwise me-1"></i>Try Sign In Again
                </a>
            </div>
        </div>
    </div>
</div>
