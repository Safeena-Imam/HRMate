<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="HRMate - Enterprise Human Resource and Workforce Management System">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($pageTitle ?? 'Dashboard') ?> &bull; <?= e(env('APP_NAME', 'HRMate')) ?></title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= time() ?>">
</head>
<body>
    <div class="hrm-wrapper">
        <!-- Reusable Sidebar Navigation -->
        <?php require dirname(__DIR__) . '/partials/sidebar.php'; ?>

        <!-- Main Body Wrapper -->
        <div class="hrm-main">
            <!-- Reusable Top Navigation Bar -->
            <?php require dirname(__DIR__) . '/partials/topbar.php'; ?>

            <!-- Breadcrumb / Context Bar -->
            <?php
            $activeNav  = $activeNav ?? 'dashboard';
            $pageTitle  = $pageTitle ?? 'Dashboard';
            $navLabels  = [
                'dashboard'  => ['icon' => 'bi-grid-1x2-fill',       'label' => 'Executive Overview'],
                'employees'  => ['icon' => 'bi-person-lines-fill',    'label' => 'Staff Directory'],
                'departments'=> ['icon' => 'bi-diagram-3-fill',       'label' => 'Departments & Roles'],
                'attendance' => ['icon' => 'bi-calendar-check-fill',  'label' => 'Attendance & Leaves'],
                'payroll'    => ['icon' => 'bi-cash-stack',           'label' => 'Payroll & Finance'],
                'audit'      => ['icon' => 'bi-shield-check',         'label' => 'Security Audit Logs'],
            ];
            $current = $navLabels[$activeNav] ?? ['icon' => 'bi-grid-1x2-fill', 'label' => $pageTitle];
            ?>
            <div class="hrm-breadcrumb-bar">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="<?= url('dashboard') ?>" class="text-decoration-none">
                                <i class="bi bi-house-fill me-1" style="font-size: 0.75rem;"></i>Home
                            </a>
                        </li>
                        <?php if ($activeNav !== 'dashboard'): ?>
                        <li class="breadcrumb-item active" aria-current="page">
                            <i class="bi <?= $current['icon'] ?> me-1" style="font-size: 0.75rem;"></i>
                            <?= htmlspecialchars($current['label']) ?>
                        </li>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>

            <!-- Page Main Content Area -->
            <main class="hrm-content-container">
                <!-- Flash Notification Alerts -->
                <?php require dirname(__DIR__) . '/partials/alerts.php'; ?>

                <!-- Dynamic Page Content -->
                <?= $content ?? '' ?>
            </main>

            <!-- Reusable Footer -->
            <?php require dirname(__DIR__) . '/partials/footer.php'; ?>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js 4+ for Analytics Graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <!-- Application Logic -->
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
