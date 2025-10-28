/**
 * Authentication JavaScript
 * Login/Register form validation and handling
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        initLoginForm(loginForm);
    }
    
    // Register form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        initRegisterForm(registerForm);
    }
    
    // Password toggle
    initPasswordToggle();
    
    // Remember me
    initRememberMe();
});

/**
 * Login Form Handler
 */
function initLoginForm(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = form.querySelector('[name="username"]').value.trim();
        const password = form.querySelector('[name="password"]').value;
        const remember = form.querySelector('[name="remember"]')?.checked;
        
        // Clear previous errors
        clearFormErrors(form);
        
        // Validation
        let isValid = true;
        
        if (!username) {
            showFieldError(form.querySelector('[name="username"]'), 'Vui lòng nhập tên đăng nhập hoặc email');
            isValid = false;
        }
        
        if (!password) {
            showFieldError(form.querySelector('[name="password"]'), 'Vui lòng nhập mật khẩu');
            isValid = false;
        }
        
        if (isValid) {
            // Show loading
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đăng nhập...';
            submitBtn.disabled = true;
            
            // Submit form
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect || '/';
                } else {
                    showFormError(form, data.message || 'Đăng nhập thất bại');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFormError(form, 'Có lỗi xảy ra. Vui lòng thử lại.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }
    });
}

/**
 * Register Form Handler
 */
function initRegisterForm(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const fullname = form.querySelector('[name="fullname"]').value.trim();
        const username = form.querySelector('[name="username"]').value.trim();
        const email = form.querySelector('[name="email"]').value.trim();
        const password = form.querySelector('[name="password"]').value;
        const confirmPassword = form.querySelector('[name="confirm_password"]').value;
        
        // Clear previous errors
        clearFormErrors(form);
        
        // Validation
        let isValid = true;
        
        if (!fullname) {
            showFieldError(form.querySelector('[name="fullname"]'), 'Vui lòng nhập họ và tên');
            isValid = false;
        }
        
        if (!username) {
            showFieldError(form.querySelector('[name="username"]'), 'Vui lòng nhập tên đăng nhập');
            isValid = false;
        } else if (username.length < 4) {
            showFieldError(form.querySelector('[name="username"]'), 'Tên đăng nhập phải có ít nhất 4 ký tự');
            isValid = false;
        }
        
        if (!email) {
            showFieldError(form.querySelector('[name="email"]'), 'Vui lòng nhập email');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showFieldError(form.querySelector('[name="email"]'), 'Email không hợp lệ');
            isValid = false;
        }
        
        if (!password) {
            showFieldError(form.querySelector('[name="password"]'), 'Vui lòng nhập mật khẩu');
            isValid = false;
        } else if (password.length < 6) {
            showFieldError(form.querySelector('[name="password"]'), 'Mật khẩu phải có ít nhất 6 ký tự');
            isValid = false;
        }
        
        if (password !== confirmPassword) {
            showFieldError(form.querySelector('[name="confirm_password"]'), 'Mật khẩu xác nhận không khớp');
            isValid = false;
        }
        
        if (isValid) {
            // Show loading
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            submitBtn.disabled = true;
            
            // Submit form
            form.submit();
        }
    });
    
    // Real-time password strength indicator
    const passwordField = form.querySelector('[name="password"]');
    if (passwordField) {
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength';
        passwordField.parentNode.appendChild(strengthIndicator);
        
        passwordField.addEventListener('input', function() {
            const strength = getPasswordStrength(this.value);
            strengthIndicator.innerHTML = `
                <div class="strength-bar strength-${strength.level}">
                    <div class="strength-fill" style="width: ${strength.percentage}%"></div>
                </div>
                <span class="strength-text">${strength.text}</span>
            `;
        });
    }
}

/**
 * Password Toggle (Show/Hide)
 */
function initPasswordToggle() {
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

/**
 * Remember Me
 */
function initRememberMe() {
    const rememberCheckbox = document.querySelector('[name="remember"]');
    const usernameField = document.querySelector('[name="username"]');
    
    if (rememberCheckbox && usernameField) {
        // Load saved username
        const savedUsername = localStorage.getItem('remembered_username');
        if (savedUsername) {
            usernameField.value = savedUsername;
            rememberCheckbox.checked = true;
        }
        
        // Save username on form submit
        const form = rememberCheckbox.closest('form');
        form.addEventListener('submit', function() {
            if (rememberCheckbox.checked) {
                localStorage.setItem('remembered_username', usernameField.value);
            } else {
                localStorage.removeItem('remembered_username');
            }
        });
    }
}

/**
 * Password Strength Calculator
 */
function getPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 6) strength += 20;
    if (password.length >= 8) strength += 20;
    if (password.length >= 12) strength += 10;
    if (/[a-z]/.test(password)) strength += 10;
    if (/[A-Z]/.test(password)) strength += 15;
    if (/[0-9]/.test(password)) strength += 15;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 10;
    
    let level = 'weak';
    let text = 'Yếu';
    
    if (strength >= 70) {
        level = 'strong';
        text = 'Mạnh';
    } else if (strength >= 40) {
        level = 'medium';
        text = 'Trung bình';
    }
    
    return { level, percentage: strength, text };
}

/**
 * Utility Functions
 */
function showFieldError(field, message) {
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function showFormError(form, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-error';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    form.insertBefore(errorDiv, form.firstChild);
}

function clearFormErrors(form) {
    form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    form.querySelectorAll('.field-error').forEach(el => el.remove());
    form.querySelectorAll('.alert').forEach(el => el.remove());
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
