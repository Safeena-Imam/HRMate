<?php
$totalEmployees = count($allEmployees ?? []);
?>

<!-- =============================================================
     STAFF DIRECTORY HEADER & TOOLBAR
     ============================================================= -->
<div class="hrm-page-header mb-4">
    <div class="d-flex flex-column flex-md-row md-align-items-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small fw-semibold">
                    Workforce Management
                </span>
                <span class="text-muted small">&bull;</span>
                <span class="text-muted small"><strong id="employeeFilterCount"><?= $totalEmployees ?></strong> Registered Staff</span>
            </div>
            <h3 class="fw-bold text-dark mb-1">Staff Directory</h3>
            <p class="text-secondary mb-0 small">
                Search, filter, manage, and export company employee records.
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Export & Print Actions -->
            <button type="button" class="btn btn-outline-success btn-sm px-3" id="btnExportEmployeesCsv" style="height: 38px; border-radius: 8px;" title="Export roster to CSV">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="btnPrintEmployees" style="height: 38px; border-radius: 8px;" title="Print Staff Roster">
                <i class="bi bi-printer me-1"></i> Print
            </button>

            <?php if (has_role('admin') || has_permission('employees.manage')): ?>
            <button type="button" class="btn hrm-btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#addEmployeeModal" style="width: auto; height: 38px;">
                <i class="bi bi-person-plus-fill me-1"></i> Add Employee
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- =============================================================
     SEARCH & FILTER BAR
     ============================================================= -->
<div class="hrm-panel mb-4">
    <div class="p-3">
        <div class="row g-2 align-items-center">
            <!-- Search Input -->
            <div class="col-12 col-md-5">
                <div class="hrm-search-box w-100" style="max-width: 100%;">
                    <i class="bi bi-search"></i>
                    <input type="text" id="employeeSearchInput" class="form-control" placeholder="Search by name, email, or employee code...">
                </div>
            </div>

            <!-- Department Filter -->
            <div class="col-6 col-md-4">
                <select id="employeeDeptFilter" class="form-select form-select-sm" style="height: 40px; border-radius: 8px;">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= e($dept['name']) ?>"><?= e($dept['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-6 col-md-3">
                <select id="employeeStatusFilter" class="form-select form-select-sm" style="height: 40px; border-radius: 8px;">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="on_leave">On Leave</option>
                    <option value="inactive">Inactive</option>
                    <option value="terminated">Terminated</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- =============================================================
     STAFF DIRECTORY TABLE
     ============================================================= -->
<div class="hrm-panel">
    <div class="table-responsive">
        <table class="table hrm-table table-hover align-middle mb-0" id="employeeDirectoryTable">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Employee Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Reporting Manager</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="employeeTableBody">
                <?php if (empty($allEmployees)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-people fs-2 d-block mb-2 text-secondary"></i>
                            No employee records found. Click "Add Employee" to create your first record.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($allEmployees as $emp): 
                        $fullName = $emp['first_name'] . ' ' . $emp['last_name'];
                        $initials = strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1));
                        $jsonPayload = htmlspecialchars(json_encode($emp), ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr class="js-employee-row" 
                            data-name="<?= e($fullName) ?>" 
                            data-code="<?= e($emp['employee_code']) ?>" 
                            data-email="<?= e($emp['email']) ?>" 
                            data-department="<?= e($emp['department_name']) ?>" 
                            data-status="<?= e($emp['employment_status']) ?>">
                            <td>
                                <span class="badge bg-light text-secondary font-monospace border">
                                    <?= e($emp['employee_code']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="hrm-avatar-circle">
                                        <?= e($initials) ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark"><?= e($fullName) ?></div>
                                        <small class="text-muted"><?= e($emp['email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= e($emp['department_name']) ?>
                                </span>
                            </td>
                            <td class="text-secondary small">
                                <?= e($emp['designation_title']) ?>
                            </td>
                            <td>
                                <?php if (!empty($emp['manager_name'])): ?>
                                    <span class="small text-dark">
                                        <i class="bi bi-arrow-return-right text-muted me-1"></i><?= e($emp['manager_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small fst-italic">Executive / None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-status <?= e($emp['employment_status']) ?>">
                                    <span class="rounded-circle p-1" style="background-color: currentColor; width: 6px; height: 6px;"></span>
                                    <?= e(str_replace('_', ' ', $emp['employment_status'])) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary js-view-employee px-3" data-employee='<?= $jsonPayload ?>'>
                                    <i class="bi bi-eye me-1"></i> View Profile
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <!-- Hidden Row for No Search Results -->
                    <tr id="employeeNoResults" style="display: none;">
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-search fs-3 d-block mb-2 text-secondary"></i>
                            No employees match your search criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- =============================================================
     MODAL: ADD NEW EMPLOYEE
     ============================================================= -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header bg-light border-bottom px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="addEmployeeModalLabel">
                        <i class="bi bi-person-plus-fill text-teal me-1" style="color: var(--hrm-accent);"></i>
                        Register New Staff Member
                    </h5>
                    <small class="text-muted">Enter employee information to add them to the company directory.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('employees/create') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required placeholder="e.g. Alexander">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required placeholder="e.g. Hayes">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Work Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required placeholder="alexander.hayes@company.org">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+1 (555) 019-9999">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department...</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= e($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Designation / Job Title <span class="text-danger">*</span></label>
                            <select name="designation_id" class="form-select" required>
                                <option value="">Select Designation...</option>
                                <?php foreach ($designations as $desig): ?>
                                    <option value="<?= $desig['id'] ?>"><?= e($desig['title']) ?> (<?= e($desig['department_name']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Reporting Supervisor / Manager</label>
                            <select name="manager_id" class="form-select">
                                <option value="">None (Executive / Direct)</option>
                                <?php foreach ($allEmployees as $emp): ?>
                                    <option value="<?= $emp['id'] ?>"><?= e($emp['first_name'] . ' ' . $emp['last_name']) ?> &bull; <?= e($emp['designation_title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-dark">Hire Date</label>
                            <input type="date" name="hire_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-dark">Employment Status</label>
                            <select name="employment_status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="on_leave">On Leave</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn hrm-btn-primary btn-sm px-4" style="width: auto;">
                        <i class="bi bi-check2-circle me-1"></i> Save Employee Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =============================================================
     MODAL: VIEW EMPLOYEE PROFILE DETAILS
     ============================================================= -->
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <h5 class="modal-title fw-bold text-dark" id="viewEmployeeModalLabel">Staff Profile Overview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="hrm-avatar-circle hrm-avatar-lg" id="modalEmpAvatar">
                        EM
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0" id="modalEmpName">Employee Name</h5>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge bg-light text-secondary font-monospace border" id="modalEmpCode">EMP-0000</span>
                            <span class="badge-status active" id="modalEmpStatus">ACTIVE</span>
                        </div>
                    </div>
                </div>

                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="bi bi-envelope me-2"></i>Email Address</span>
                        <span class="fw-semibold text-dark" id="modalEmpEmail">---</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="bi bi-telephone me-2"></i>Phone Number</span>
                        <span class="fw-semibold text-dark" id="modalEmpPhone">---</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="bi bi-building me-2"></i>Department</span>
                        <span class="fw-semibold text-dark" id="modalEmpDept">---</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="bi bi-award me-2"></i>Designation / Title</span>
                        <span class="fw-semibold text-dark" id="modalEmpDesig">---</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="bi bi-person-badge me-2"></i>Reports To</span>
                        <span class="fw-semibold text-dark" id="modalEmpManager">---</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="bi bi-calendar3 me-2"></i>Hire Date</span>
                        <span class="fw-semibold text-dark" id="modalEmpHireDate">---</span>
                    </li>
                </ul>
            </div>
            <div class="modal-footer border-top px-4 py-3 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
