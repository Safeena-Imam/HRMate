<footer class="mt-auto py-3 px-4 bg-white border-top text-muted small">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
        <div>
            &copy; <?= date('Y') ?> <strong><?= e(env('APP_NAME', 'Apex HRMS')) ?></strong>. Enterprise Human Resource Management.
        </div>
        <div class="d-flex align-items-center gap-3">
            <span>Security: AES/BCrypt Encrypted</span>
            <span>&bull;</span>
            <span>Version: 1.0.0 (Part 1 Architecture)</span>
        </div>
    </div>
</footer>
