<!-- =============================================================
     DEPARTMENTS & ROLES HEADER
     ============================================================= -->
<div class="hrm-page-header mb-4">
    <div class="d-flex flex-column flex-md-row md-align-items-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small fw-semibold">
                    Organizational Structure
                </span>
                <span class="text-muted small">&bull;</span>
                <span class="text-muted small"><?= count($departments ?? []) ?> Active Divisions</span>
            </div>
            <h3 class="fw-bold text-dark mb-1">Departments &amp; Roles</h3>
            <p class="text-secondary mb-0 small">
                Browse corporate departments, headcount allocation, and designated position tiers.
            </p>
        </div>
        <div>
            <a href="<?= url('employees') ?>" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-people me-1"></i> Staff Directory
            </a>
        </div>
    </div>
</div>

<?php if (!empty($selectedDepartment)): ?>
    <!-- =============================================================
         SELECTED DEPARTMENT STAFF DRILL-DOWN VIEW
         ============================================================= -->
    <div class="hrm-panel mb-4 shadow-sm border-2" style="border-color: #101828 !important;">
        <!-- Department Detail Banner -->
        <div class="hrm-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3" style="background-color: #f8fafc;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-dark font-monospace"><?= e($selectedDepartment['code']) ?></span>
                    <h5 class="fw-bold text-dark mb-0"><?= e($selectedDepartment['name']) ?></h5>
                    <span class="badge bg-success-subtle text-success small">Active Division</span>
                </div>
                <small class="text-muted">
                    <?= e($selectedDepartment['description'] ?? 'Corporate Department Division') ?>
                </small>
            </div>
            <div>
                <!-- BACK BUTTON TO RETURN TO ALL DEPARTMENTS -->
                <a href="<?= url('departments') ?>" class="btn btn-dark btn-sm fw-semibold px-3" style="background-color: #101828; color: #fff;">
                    <i class="bi bi-arrow-left me-1" style="color: #9DE500;"></i> Back to All Departments
                </a>
            </div>
        </div>

        <!-- Assigned Staff Table -->
        <div class="table-responsive">
            <table class="table hrm-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Role / Designation</th>
                        <th>Official Email</th>
                        <th>Contact Phone</th>
                        <th>Joining Date</th>
                        <th>Current Status</th>
                        <th class="text-end">Quick Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($deptEmployees)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-people fs-2 text-muted d-block mb-2"></i>
                                No employees are currently assigned to <strong><?= e($selectedDepartment['name']) ?></strong>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($deptEmployees as $emp): 
                            $initials = strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1));
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="hrm-avatar-circle" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                            <?= e($initials) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark small"><?= e($emp['first_name'] . ' ' . $emp['last_name']) ?></div>
                                            <small class="text-muted font-monospace" style="font-size: 0.72rem;"><?= e($emp['employee_code']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border small">
                                        <?= e($emp['designation_title'] ?? 'Staff') ?>
                                    </span>
                                </td>
                                <td class="small text-secondary"><?= e($emp['email']) ?></td>
                                <td class="small text-muted font-monospace"><?= e($emp['phone'] ?? 'N/A') ?></td>
                                <td class="small text-muted"><?= !empty($emp['hire_date']) ? date('M d, Y', strtotime($emp['hire_date'])) : '—' ?></td>
                                <td>
                                    <span class="badge-status <?= e($emp['employment_status']) ?>">
                                        <?= strtoupper(e(str_replace('_', ' ', $emp['employment_status']))) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= url('attendance') ?>?employee_id=<?= (int)$emp['id'] ?>" class="btn btn-sm btn-outline-dark" title="View Monthly Attendance Sheet">
                                        <i class="bi bi-calendar-check me-1"></i> Attendance
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center text-muted small">
            <span>Showing <strong><?= count($deptEmployees) ?></strong> staff member(s) enrolled in <?= e($selectedDepartment['name']) ?>.</span>
            <a href="<?= url('departments') ?>" class="text-decoration-none fw-semibold text-dark">
                &larr; Switch Department
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- =============================================================
     DEPARTMENT CARDS GRID
     ============================================================= -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold text-dark mb-0">
        <i class="bi bi-diagram-3-fill me-1" style="color: var(--hrm-accent, #9DE500);"></i>
        <?= !empty($selectedDepartment) ? 'All Corporate Departments' : 'Select a Department to View Staff' ?>
    </h6>
    <span class="text-muted small">Click any card to inspect enrolled members</span>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($departments as $dept): 
        $count = (int)($dept['employee_count'] ?? 0);
        $isSelected = (!empty($selectedDeptId) && (int)$dept['id'] === (int)$selectedDeptId);
    ?>
        <div class="col-12 col-md-6 col-xl-4">
            <a href="<?= url('departments') ?>?dept_id=<?= (int)$dept['id'] ?>" class="hrm-dept-card text-decoration-none d-flex flex-column justify-content-between <?= $isSelected ? 'border-primary shadow-sm' : '' ?>" style="<?= $isSelected ? 'border-width: 2px !important; background-color: #f8fafc;' : '' ?>" title="Click to view employees in <?= e($dept['name']) ?>">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-light text-dark font-monospace border px-2 py-1"><?= e($dept['code']) ?></span>
                        <?php if ($isSelected): ?>
                            <span class="badge bg-primary text-white small">Viewing Now</span>
                        <?php else: ?>
                            <span class="badge bg-success-subtle text-success small">Active</span>
                        <?php endif; ?>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?= e($dept['name']) ?></h5>
                    <p class="text-secondary small mb-3" style="min-height: 38px;">
                        <?= e($dept['description'] ?? 'Operational organizational unit.') ?>
                    </p>
                </div>
                <div class="pt-3 border-top d-flex justify-content-between align-items-center small text-muted">
                    <span class="d-flex align-items-center gap-1">
                        <i class="bi bi-people text-muted"></i>
                        <span>Staff:</span>
                        <strong class="text-dark font-monospace"><?= $count ?> <?= $count === 1 ? 'Member' : 'Members' ?></strong>
                    </span>
                    <span class="fw-semibold text-dark hrm-card-action">
                        View Staff <i class="bi bi-arrow-right ms-1"></i>
                    </span>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
