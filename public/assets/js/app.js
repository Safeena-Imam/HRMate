/**
 * Apex HRMS - Master Frontend Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar Toggle — works on both mobile and desktop
    const toggleBtns = document.querySelectorAll('.js-sidebar-toggle, #hrmSidebarToggle');
    const sidebar    = document.getElementById('hrmSidebar');
    const backdrop   = document.getElementById('hrmSidebarBackdrop');
    const wrapper    = document.querySelector('.hrm-wrapper');

    function isDesktop() {
        return window.innerWidth >= 992;
    }

    if (sidebar && wrapper) {
        toggleBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (isDesktop()) {
                    // Desktop: toggle icon-only collapsed state
                    wrapper.classList.toggle('sidebar-collapsed');
                    const isCollapsed = wrapper.classList.contains('sidebar-collapsed');
                    const collapseBtn = document.getElementById('sidebarCollapseBtn');
                    if (collapseBtn) {
                        collapseBtn.setAttribute('title', isCollapsed ? 'Expand sidebar' : 'Collapse sidebar');
                    }
                } else {
                    // Mobile: slide sidebar in/out with backdrop
                    sidebar.classList.toggle('show');
                    if (backdrop) backdrop.classList.toggle('show');
                }
            });
        });

        // Uncollapse when clicking the header in collapsed mode on desktop
        const sidebarHeader = document.querySelector('.hrm-sidebar-header');
        if (sidebarHeader) {
            sidebarHeader.addEventListener('click', (e) => {
                if (isDesktop() && wrapper.classList.contains('sidebar-collapsed')) {
                    e.preventDefault();
                    wrapper.classList.remove('sidebar-collapsed');
                    const collapseBtn = document.getElementById('sidebarCollapseBtn');
                    if (collapseBtn) {
                        collapseBtn.setAttribute('title', 'Collapse sidebar');
                    }
                }
            });
        }
    }

    if (backdrop) {
        backdrop.addEventListener('click', () => {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
        });
    }

    // 2. Password Visibility Toggle
    const passwordToggles = document.querySelectorAll('.js-password-toggle');
    passwordToggles.forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.closest('.hrm-input-group').querySelector('input');
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }
        });
    });

    // 3. Auto-dismiss alerts after 6 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            try {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            } catch (e) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 6500);
    });

    // 4. Initialize Dashboard Attendance Chart (Chart.js Preview)
    const attendanceChartEl = document.getElementById('attendanceAnalyticsChart');
    if (attendanceChartEl && typeof Chart !== 'undefined') {
        const ctx = attendanceChartEl.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                datasets: [
                    {
                        label: 'Present',
                        data: [94, 96, 92, 95, 91],
                        backgroundColor: '#0d9488',
                        borderRadius: 4
                    },
                    {
                        label: 'On Leave',
                        data: [4, 3, 5, 3, 6],
                        backgroundColor: '#f59e0b',
                        borderRadius: 4
                    },
                    {
                        label: 'Late / Partial',
                        data: [2, 1, 3, 2, 3],
                        backgroundColor: '#64748b',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            font: { family: "'Inter', sans-serif", size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: ${context.raw}%`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(val) { return val + '%'; }
                        }
                    }
                }
            }
        });
    }

    // 5. Dynamic Tab Navigation & Synchronized Sidebar
    const tabButtons = document.querySelectorAll('.hrm-tab-btn[data-target]');
    const tabPanes = document.querySelectorAll('.hrm-tab-pane');
    const sidebarTabLinks = document.querySelectorAll('.hrm-sidebar-nav a[data-tab-target]');

    function switchTab(tabKey, updateUrl = true) {
        if (!tabKey) return;
        const targetPane = document.getElementById('tab-' + tabKey);
        if (!targetPane) return;

        // Update tab buttons
        tabButtons.forEach(btn => {
            if (btn.getAttribute('data-target') === 'tab-' + tabKey) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Update tab panes
        tabPanes.forEach(pane => {
            if (pane.id === 'tab-' + tabKey) {
                pane.classList.add('active');
            } else {
                pane.classList.remove('active');
            }
        });

        // Update sidebar links
        sidebarTabLinks.forEach(link => {
            if (link.getAttribute('data-tab-target') === tabKey) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });

        // Update browser URL query without reload
        if (updateUrl && window.history && window.history.pushState) {
            const url = new URL(window.location);
            url.searchParams.set('tab', tabKey);
            window.history.pushState({ tab: tabKey }, '', url);
        }
    }

    // Tab button click
    tabButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = btn.getAttribute('data-target');
            const tabKey = targetId.replace('tab-', '');
            switchTab(tabKey);
        });
    });

    // Close mobile drawer when any sidebar link is clicked
    const allSidebarNavLinks = document.querySelectorAll('.hrm-sidebar-nav a');
    allSidebarNavLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (sidebar && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                if (backdrop) backdrop.classList.remove('show');
            }
        });
    });

    // Handle Browser Popstate (Back/Forward navigation)
    window.addEventListener('popstate', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'overview';
        switchTab(tab, false);
    });

    // Initialize initial tab from URL if present
    const initialParams = new URLSearchParams(window.location.search);
    const initialTab = initialParams.get('tab');
    if (initialTab && document.getElementById('tab-' + initialTab)) {
        switchTab(initialTab, false);
    }

    // 6. Live Employee Search and Multi-Filtering
    const empSearch = document.getElementById('employeeSearchInput');
    const empDeptFilter = document.getElementById('employeeDeptFilter');
    const empStatusFilter = document.getElementById('employeeStatusFilter');
    const empRows = document.querySelectorAll('.js-employee-row');
    const empCountEl = document.getElementById('employeeFilterCount');

    function filterEmployees() {
        if (!empRows.length) return;
        const query = (empSearch ? empSearch.value : '').toLowerCase().trim();
        const selectedDept = (empDeptFilter ? empDeptFilter.value : '').toLowerCase().trim();
        const selectedStatus = (empStatusFilter ? empStatusFilter.value : '').toLowerCase().trim();

        let visibleCount = 0;

        empRows.forEach(row => {
            const name = (row.getAttribute('data-name') || '').toLowerCase();
            const code = (row.getAttribute('data-code') || '').toLowerCase();
            const email = (row.getAttribute('data-email') || '').toLowerCase();
            const dept = (row.getAttribute('data-department') || '').toLowerCase();
            const status = (row.getAttribute('data-status') || '').toLowerCase();

            const matchesQuery = !query || name.includes(query) || code.includes(query) || email.includes(query);
            const matchesDept = !selectedDept || dept === selectedDept;
            const matchesStatus = !selectedStatus || status === selectedStatus;

            if (matchesQuery && matchesDept && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (empCountEl) {
            empCountEl.textContent = visibleCount;
        }

        const noResultsRow = document.getElementById('employeeNoResults');
        if (noResultsRow) {
            noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
        }
    }

    if (empSearch) empSearch.addEventListener('input', filterEmployees);
    if (empDeptFilter) empDeptFilter.addEventListener('change', filterEmployees);
    if (empStatusFilter) empStatusFilter.addEventListener('change', filterEmployees);

    // Auto-filter by URL parameter (e.g. from Department Cards)
    const urlParams = new URLSearchParams(window.location.search);
    const deptParam = urlParams.get('dept');
    if (deptParam && empDeptFilter) {
        empDeptFilter.value = deptParam;
        filterEmployees();
    }

    // 7. Employee Profile View Modal Loader
    const viewButtons = document.querySelectorAll('.js-view-employee');
    const viewModalEl = document.getElementById('viewEmployeeModal');

    viewButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            try {
                const data = JSON.parse(btn.getAttribute('data-employee'));
                if (!data) return;

                const nameEl = document.getElementById('modalEmpName');
                const codeEl = document.getElementById('modalEmpCode');
                const emailEl = document.getElementById('modalEmpEmail');
                const phoneEl = document.getElementById('modalEmpPhone');
                const deptEl = document.getElementById('modalEmpDept');
                const desigEl = document.getElementById('modalEmpDesig');
                const managerEl = document.getElementById('modalEmpManager');
                const hireEl = document.getElementById('modalEmpHireDate');
                const statusEl = document.getElementById('modalEmpStatus');
                const avatarEl = document.getElementById('modalEmpAvatar');

                const fullName = (data.first_name || '') + ' ' + (data.last_name || '');
                const initials = ((data.first_name ? data.first_name[0] : '') + (data.last_name ? data.last_name[0] : '')).toUpperCase();

                if (nameEl) nameEl.textContent = fullName;
                if (codeEl) codeEl.textContent = data.employee_code || '---';
                if (emailEl) emailEl.textContent = data.email || '---';
                if (phoneEl) phoneEl.textContent = data.phone || 'Not provided';
                if (deptEl) deptEl.textContent = data.department_name || '---';
                if (desigEl) desigEl.textContent = data.designation_title || '---';
                if (managerEl) managerEl.textContent = data.manager_name || 'None (Direct Report / Executive)';
                if (hireEl) hireEl.textContent = data.hire_date || '---';
                if (avatarEl) avatarEl.textContent = initials || 'EM';

                if (statusEl) {
                    const statusText = (data.employment_status || 'active').replace('_', ' ');
                    statusEl.textContent = statusText.toUpperCase();
                    statusEl.className = 'badge-status ' + (data.employment_status || 'active');
                }

                if (viewModalEl && typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getOrCreateInstance(viewModalEl);
                    modal.show();
                }
            } catch (err) {
                console.error("Failed to parse employee payload", err);
            }
        });
    });

    // 8. Audit Log Live Search
    const auditSearch = document.getElementById('auditLogSearchInput');
    const auditRows = document.querySelectorAll('.js-audit-row');
    if (auditSearch && auditRows.length) {
        auditSearch.addEventListener('input', () => {
            const q = auditSearch.value.toLowerCase().trim();
            auditRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
        });
    }

    // 9. Floating Toast Notification Helper
    window.showHrmToast = function(message, type = 'success') {
        let container = document.getElementById('hrmToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'hrmToastContainer';
            container.className = 'hrm-toast-container';
            document.body.appendChild(container);
        }

        const icons = {
            success: 'bi-check-circle-fill',
            info: 'bi-info-circle-fill',
            warning: 'bi-exclamation-triangle-fill',
            danger: 'bi-x-circle-fill'
        };

        const toast = document.createElement('div');
        toast.className = `hrm-toast ${type}`;
        toast.innerHTML = `
            <div class="hrm-toast-icon">
                <i class="bi ${icons[type] || icons.info}"></i>
            </div>
            <div class="hrm-toast-message">${message}</div>
            <button type="button" class="hrm-toast-close" aria-label="Close">
                <i class="bi bi-x"></i>
            </button>
        `;

        const closeBtn = toast.querySelector('.hrm-toast-close');
        closeBtn.addEventListener('click', () => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px) scale(0.95)';
            setTimeout(() => toast.remove(), 250);
        });

        container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px) scale(0.95)';
                setTimeout(() => toast.remove(), 250);
            }
        }, 4500);
    };

    // 10. Staff Directory CSV Export
    const btnExportCsv = document.getElementById('btnExportEmployeesCsv');
    if (btnExportCsv) {
        btnExportCsv.addEventListener('click', () => {
            const rows = document.querySelectorAll('#employeeDirectoryTable tbody tr.js-employee-row');
            if (!rows.length) {
                showHrmToast('No employee records available to export.', 'warning');
                return;
            }

            const csvHeaders = ['Employee Code', 'Full Name', 'Email', 'Department', 'Designation', 'Reporting Manager', 'Status'];
            const csvRows = [csvHeaders.join(',')];

            rows.forEach(row => {
                // If filtered out, skip or include visible only
                if (row.style.display === 'none') return;

                const code = `"${(row.getAttribute('data-code') || '').replace(/"/g, '""')}"`;
                const name = `"${(row.getAttribute('data-name') || '').replace(/"/g, '""')}"`;
                const email = `"${(row.getAttribute('data-email') || '').replace(/"/g, '""')}"`;
                const dept = `"${(row.getAttribute('data-department') || '').replace(/"/g, '""')}"`;
                
                const desigEl = row.querySelector('td:nth-child(4)');
                const desig = `"${(desigEl ? desigEl.textContent.trim() : '').replace(/"/g, '""')}"`;

                const mgrEl = row.querySelector('td:nth-child(5)');
                const manager = `"${(mgrEl ? mgrEl.textContent.trim().replace(/\s+/g, ' ') : '').replace(/"/g, '""')}"`;

                const status = `"${(row.getAttribute('data-status') || '').replace(/"/g, '""')}"`;

                csvRows.push([code, name, email, dept, desig, manager, status].join(','));
            });

            const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + encodeURIComponent(csvRows.join('\r\n'));
            const downloadLink = document.createElement('a');
            const today = new Date().toISOString().split('T')[0];
            downloadLink.setAttribute('href', csvContent);
            downloadLink.setAttribute('download', `Apex_HRMS_Staff_Roster_${today}.csv`);
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);

            showHrmToast(`Exported ${csvRows.length - 1} employee records to CSV!`, 'success');
        });
    }

    // 11. Staff Directory Print Handler
    const btnPrint = document.getElementById('btnPrintEmployees');
    if (btnPrint) {
        btnPrint.addEventListener('click', () => {
            window.print();
        });
    }

    // 12. Interactive Employee Portal Actions (Punch & Leave)
    const btnPunchToggle = document.getElementById('jsBtnPunchToggle');
    if (btnPunchToggle) {
        let isClockedIn = true;
        btnPunchToggle.addEventListener('click', () => {
            const now = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            if (isClockedIn) {
                isClockedIn = false;
                btnPunchToggle.className = 'btn btn-outline-success btn-sm px-3 py-2';
                btnPunchToggle.innerHTML = '<i class="bi bi-box-arrow-in-right me-1"></i> Clock In';
                showHrmToast(`Shift clocked out today at ${now}. Have a great evening!`, 'info');
            } else {
                isClockedIn = true;
                btnPunchToggle.className = 'btn btn-outline-danger btn-sm px-3 py-2';
                btnPunchToggle.innerHTML = '<i class="bi bi-box-arrow-right me-1"></i> Clock Out';
                showHrmToast(`Welcome back! Clocked in successfully at ${now}.`, 'success');
            }
        });
    }

    const leaveForm = document.getElementById('jsApplyLeaveForm');
    if (leaveForm) {
        leaveForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const modalEl = document.getElementById('applyLeaveModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
            leaveForm.reset();
            showHrmToast('Time-off request submitted to Marcus Sterling (CTO) for review!', 'success');
        });
    }

    // 13. Smooth Counter Animation on Metric Values
    const metricElements = document.querySelectorAll('.hrm-metric-value');
    metricElements.forEach(el => {
        const text = el.textContent.trim();
        // Match numbers or decimals
        const match = text.match(/^([\d,]+(?:\.\d+)?)(.*)$/);
        if (match) {
            const rawNum = parseFloat(match[1].replace(/,/g, ''));
            const suffix = match[2] || '';
            if (!isNaN(rawNum) && rawNum > 0 && rawNum <= 10000) {
                const duration = 750;
                const start = 0;
                const startTime = performance.now();
                const isDecimal = match[1].includes('.');

                function updateCounter(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    // Ease out quadratic
                    const easeOut = 1 - (1 - progress) * (1 - progress);
                    const currentVal = start + (rawNum - start) * easeOut;

                    el.textContent = (isDecimal ? currentVal.toFixed(1) : Math.round(currentVal).toLocaleString()) + suffix;

                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    } else {
                        el.textContent = text;
                    }
                }
                requestAnimationFrame(updateCounter);
            }
        }
    });
});

