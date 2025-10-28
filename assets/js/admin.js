/**
 * Admin Dashboard JavaScript
 * Charts, tables, and admin-specific functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize charts if Chart.js is loaded
    if (typeof Chart !== 'undefined') {
        initCharts();
    }
    
    // Data tables functionality
    initDataTables();
    
    // Confirm delete
    initConfirmDelete();
    
    // Sidebar toggle for mobile
    initSidebarToggle();
    
    // File upload preview
    initFileUpload();
});

/**
 * Initialize Charts
 */
function initCharts() {
    // Revenue chart
    const revenueChart = document.getElementById('revenueChart');
    if (revenueChart) {
        createLineChart(revenueChart, {
            labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6'],
            data: [12000000, 19000000, 15000000, 25000000, 22000000, 30000000],
            label: 'Doanh thu (VNĐ)'
        });
    }
    
    // User growth chart
    const userChart = document.getElementById('userGrowthChart');
    if (userChart) {
        createBarChart(userChart, {
            labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6'],
            data: [150, 200, 180, 250, 300, 380],
            label: 'Người dùng mới'
        });
    }
}

function createLineChart(canvas, config) {
    new Chart(canvas, {
        type: 'line',
        data: {
            labels: config.labels,
            datasets: [{
                label: config.label,
                data: config.data,
                borderColor: '#0056d2',
                backgroundColor: 'rgba(0, 86, 210, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createBarChart(canvas, config) {
    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: config.labels,
            datasets: [{
                label: config.label,
                data: config.data,
                backgroundColor: '#0056d2'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/**
 * Data Tables
 */
function initDataTables() {
    // Sort table columns
    document.querySelectorAll('.data-table th[data-sort]').forEach(th => {
        th.style.cursor = 'pointer';
        th.addEventListener('click', function() {
            sortTable(this);
        });
    });
    
    // Search in tables
    const searchInputs = document.querySelectorAll('[data-table-search]');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            filterTable(this);
        });
    });
}

function sortTable(header) {
    const table = header.closest('table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const index = Array.from(header.parentNode.children).indexOf(header);
    const isAscending = header.classList.contains('sort-asc');
    
    rows.sort((a, b) => {
        const aText = a.children[index].textContent.trim();
        const bText = b.children[index].textContent.trim();
        
        if (!isNaN(aText) && !isNaN(bText)) {
            return isAscending ? bText - aText : aText - bText;
        }
        
        return isAscending 
            ? bText.localeCompare(aText) 
            : aText.localeCompare(bText);
    });
    
    // Update DOM
    rows.forEach(row => tbody.appendChild(row));
    
    // Toggle sort class
    header.parentNode.querySelectorAll('th').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
    });
    header.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
}

function filterTable(input) {
    const filter = input.value.toLowerCase();
    const table = document.querySelector(input.dataset.tableSearch);
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

/**
 * Confirm Delete
 */
function initConfirmDelete() {
    document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.dataset.confirmDelete || 'Bạn có chắc chắn muốn xóa?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Sidebar Toggle
 */
function initSidebarToggle() {
    const toggleBtn = document.querySelector('.btn-toggle-sidebar');
    const sidebar = document.getElementById('adminSidebar');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed');
        });
    }
}

/**
 * File Upload Preview
 */
function initFileUpload() {
    document.querySelectorAll('input[type="file"][data-preview]').forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(input.dataset.preview);
                    if (preview) {
                        preview.innerHTML = `<img src="${e.target.result}" style="max-width: 400px; border-radius: 8px;">`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });
}

/**
 * Bulk Actions
 */
function initBulkActions() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });
    }
    
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });
}

function updateBulkActions() {
    const checked = document.querySelectorAll('.bulk-checkbox:checked').length;
    const bulkActions = document.getElementById('bulkActions');
    
    if (bulkActions) {
        bulkActions.style.display = checked > 0 ? 'block' : 'none';
        bulkActions.querySelector('.selected-count').textContent = checked;
    }
}
