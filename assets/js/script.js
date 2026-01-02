/**
 * Sistem MCU - Custom JavaScript
 */

// Document Ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Initialize form validations
    initFormValidations();
    
    // Auto-dismiss alerts
    autoDismissAlerts();
    
    // Initialize data tables
    initDataTables();
    
    // Initialize date pickers
    initDatePickers();
    
    // Initialize file previews
    initFilePreviews();
});

/**
 * Initialize Bootstrap tooltips
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize form validations
 */
function initFormValidations() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Auto-dismiss alerts after 5 seconds
 */
function autoDismissAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

/**
 * Initialize DataTables
 */
function initDataTables() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
            },
            "pageLength": 25,
            "responsive": true
        });
    }
}

/**
 * Initialize date pickers
 */
function initDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Set max date to today for birth dates
        if (input.name.includes('tanggal_lahir') || input.name.includes('birth')) {
            const today = new Date().toISOString().split('T')[0];
            input.max = today;
        }
        
        // Set min date to today for future dates
        if (input.name.includes('tanggal_mcu') || input.name.includes('future')) {
            const today = new Date().toISOString().split('T')[0];
            input.min = today;
        }
    });
}

/**
 * Initialize file previews
 */
function initFilePreviews() {
    const fileInputs = document.querySelectorAll('input[type="file"][accept^="image"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewId = this.getAttribute('data-preview');
            
            if (file && previewId) {
                const preview = document.getElementById(previewId);
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <div class="image-preview-container">
                            <img src="${e.target.result}" class="img-fluid rounded" 
                                 style="max-height: 200px;">
                            <div class="mt-2">
                                <small class="text-muted">Preview: ${file.name}</small>
                            </div>
                        </div>
                    `;
                };
                
                reader.readAsDataURL(file);
            }
        });
    });
}

/**
 * Confirm before action
 */
function confirmAction(message = 'Apakah Anda yakin?') {
    return confirm(message);
}

/**
 * Print specific element
 */
function printElement(elementId) {
    const printContent = document.getElementById(elementId).innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    window.location.reload();
}

/**
 * Export table to Excel
 */
function exportTableToExcel(tableId, filename = '') {
    const table = document.getElementById(tableId);
    let csv = [];
    
    // Get headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push(th.textContent);
    });
    csv.push(headers.join(','));
    
    // Get rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push(`"${td.textContent}"`);
        });
        csv.push(row.join(','));
    });
    
    // Create download link
    const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', filename || `export-${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Toggle password visibility
 */
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.querySelector(`[data-toggle="${inputId}"] i`);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/**
 * Calculate age from birth date
 */
function calculateAge(birthDateInputId, ageInputId) {
    const birthDate = new Date(document.getElementById(birthDateInputId).value);
    const today = new Date();
    
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    document.getElementById(ageInputId).value = age;
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

/**
 * Show loading spinner
 */
function showLoading() {
    const spinner = document.createElement('div');
    spinner.id = 'loading-spinner';
    spinner.innerHTML = `
        <div class="spinner-overlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    document.body.appendChild(spinner);
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) {
        spinner.remove();
    }
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Teks berhasil disalin!');
    }).catch(err => {
        console.error('Gagal menyalin teks: ', err);
    });
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Add loading spinner CSS
const spinnerStyle = document.createElement('style');
spinnerStyle.textContent = `
    .spinner-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    .image-preview-container {
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 8px;
        background: #f8f9fa;
        text-align: center;
    }
`;
document.head.appendChild(spinnerStyle);