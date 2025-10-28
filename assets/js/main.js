/**
 * Main JavaScript File
 * Common functionality for UTH Learning System
 */

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    
    // Mobile menu toggle
    initMobileMenu();
    
    // Smooth scroll for anchor links
    initSmoothScroll();
    
    // Dropdown menus
    initDropdowns();
    
    // Form validation
    initFormValidation();
    
    // Lazy loading images
    initLazyLoading();
    
    // Toast notifications
    initToasts();
});

/**
 * Mobile Menu
 */
function initMobileMenu() {
    const toggle = document.querySelector('.navbar-toggle');
    const menu = document.querySelector('.navbar-menu');
    
    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            menu.classList.toggle('active');
            this.querySelector('i').classList.toggle('fa-bars');
            this.querySelector('i').classList.toggle('fa-times');
        });
        
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.navbar')) {
                menu.classList.remove('active');
                if (toggle.querySelector('i')) {
                    toggle.querySelector('i').classList.add('fa-bars');
                    toggle.querySelector('i').classList.remove('fa-times');
                }
            }
        });
    }
}

/**
 * Smooth Scroll
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#!') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
}

/**
 * Dropdown Menus
 */
function initDropdowns() {
    // User menu dropdown
    const userMenus = document.querySelectorAll('.admin-user-menu');
    
    userMenus.forEach(menu => {
        menu.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.querySelector('.dropdown-menu');
            if (dropdown) {
                dropdown.classList.toggle('show');
            }
        });
    });
    
    // Close dropdowns on outside click
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });
}

/**
 * Form Validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Check required fields
            const required = form.querySelectorAll('[required]');
            required.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    showFieldError(field, 'Trường này là bắt buộc');
                } else {
                    clearFieldError(field);
                }
            });
            
            // Email validation
            const emails = form.querySelectorAll('input[type="email"]');
            emails.forEach(field => {
                if (field.value && !isValidEmail(field.value)) {
                    isValid = false;
                    showFieldError(field, 'Email không hợp lệ');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('error');
    const error = document.createElement('span');
    error.className = 'error-message';
    error.textContent = message;
    field.parentNode.appendChild(error);
}

function clearFieldError(field) {
    field.classList.remove('error');
    const error = field.parentNode.querySelector('.error-message');
    if (error) error.remove();
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * Lazy Loading Images
 */
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img.lazy').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

/**
 * Toast Notifications
 */
function initToasts() {
    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * AJAX Helper Function
 */
function ajax(url, options = {}) {
    return fetch(url, {
        method: options.method || 'GET',
        headers: {
            'Content-Type': 'application/json',
            ...options.headers
        },
        body: options.body ? JSON.stringify(options.body) : null
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .catch(error => {
        console.error('Error:', error);
        throw error;
    });
}

/**
 * Utility Functions
 */
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function formatNumber(num) {
    return new Intl.NumberFormat('vi-VN').format(num);
}

function formatCurrency(num) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(num);
}

function formatDate(date) {
    return new Intl.DateTimeFormat('vi-VN').format(new Date(date));
}

// Export functions for use in other scripts
window.UTH = {
    showToast,
    ajax,
    debounce,
    formatNumber,
    formatCurrency,
    formatDate
};
