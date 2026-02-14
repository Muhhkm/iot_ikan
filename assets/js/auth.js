/**
 * Auth Page JavaScript
 * File: assets/js/auth.js
 */

// Get the base path from current URL
const currentPath = window.location.pathname;
const basePath = currentPath.substring(0, currentPath.lastIndexOf('/'));
const API_BASE = basePath + '/controller';

console.log('🔧 Auth.js loaded');
console.log('Current path:', currentPath);
console.log('Base path:', basePath);
console.log('API_BASE:', API_BASE);

// Wait for DOM to be fully loaded
if (document.readyState === 'loading') {
    console.log('Document still loading, waiting...');
    document.addEventListener('DOMContentLoaded', initializeAuth);
} else {
    console.log('Document already loaded, initializing now...');
    initializeAuth();
}

function initializeAuth() {
    console.log('✅ DOM ready, initializing auth system...');
    console.log('Calling setupTabListeners...');
    setupTabListeners();
    console.log('Calling setupLoginForm...');
    setupLoginForm();
    console.log('Calling setupRegisterForm...');
    setupRegisterForm();
    console.log('✅ Auth system initialization complete!');
}

/**
 * Switch between Sign In and Sign Up tabs
 */
function switchTab(tab) {
    console.log('Switching to tab:', tab);
    // Hide all content
    document.querySelectorAll('.auth-page-content').forEach(el => {
        el.classList.remove('active');
    });
    
    // Remove active from buttons
    document.querySelectorAll('.auth-page-tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected content
    const contentEl = document.getElementById(tab);
    if (contentEl) contentEl.classList.add('active');
    
    const tabBtn = document.querySelector(`[data-tab="${tab}"]`);
    if (tabBtn) tabBtn.classList.add('active');
}

/**
 * Setup tab button listeners
 */
function setupTabListeners() {
    const tabBtns = document.querySelectorAll('.auth-page-tab-btn');
    console.log('Found tab buttons:', tabBtns.length);
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            switchTab(btn.dataset.tab);
        });
    });
}

/**
 * Show toast notification
 */
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) {
        console.error('❌ Toast element not found!');
        alert(message);
        return;
    }
    
    console.log('Toast:', type, message);
    
    // Clear previous timeouts
    if (toast.timeout) clearTimeout(toast.timeout);
    
    toast.textContent = message;
    toast.className = 'toast ' + type;
    toast.style.display = 'block';

    // Auto hide after 4 seconds
    toast.timeout = setTimeout(() => {
        toast.style.display = 'none';
    }, 4000);
}

/**
 * Setup Login Form Handler
 */
function setupLoginForm() {
    const loginForm = document.getElementById('loginForm');
    console.log('📋 Setting up login form...');
    console.log('Login form element:', loginForm);
    
    if (!loginForm) {
        console.error('❌ Login form element NOT FOUND!');
        return;
    }

    console.log('✅ Login form found, attaching event listener...');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('========== 📝 LOGIN FORM SUBMITTED! ==========');

        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.textContent;

        console.log('Form data:');
        console.log('  Email:', email);
        console.log('  Password length:', password.length);

        // Validasi
        if (!email || !password) {
            showToast('Email dan password harus diisi!', 'error');
            console.warn('⚠️ Validation failed: empty fields');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Loading...';

        (async () => {
            try {
                const url = `${API_BASE}/login.php`;
                console.log('🔗 Fetching:', url);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                });

                console.log('📊 Response status:', response.status);
                const data = await response.json();
                console.log('📦 Response data:', data);

                if (data.success) {
                    showToast('✓ Login berhasil!', 'success');
                    console.log('✅ Login successful!');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    showToast(data.message || 'Login gagal', 'error');
                    console.error('❌ Login failed:', data.message);
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            } catch (error) {
                console.error('❌ Fetch error:', error);
                showToast('Terjadi kesalahan. Coba lagi. ' + error.message, 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })();
    });

    console.log('✅ Login form event listener attached');
}

/**
 * Setup Register Form Handler
 */
function setupRegisterForm() {
    const registerForm = document.getElementById('registerForm');
    console.log('📋 Setting up register form...');
    console.log('Register form element:', registerForm);
    
    if (!registerForm) {
        console.error('❌ Register form element NOT FOUND!');
        return;
    }

    console.log('✅ Register form found, attaching event listener...');

    const passwordInput = document.getElementById('registerPassword');
    const confirmInput = document.getElementById('registerConfirmPassword');

    // Real-time password match check
    if (confirmInput && passwordInput) {
        confirmInput.addEventListener('input', function() {
            if (this.value && this.value !== passwordInput.value) {
                this.style.borderColor = '#d32f2f';
                this.style.boxShadow = '0 0 0 3px rgba(211, 47, 47, 0.1)';
            } else if (this.value && this.value === passwordInput.value) {
                this.style.borderColor = '#4caf50';
                this.style.boxShadow = '0 0 0 3px rgba(76, 175, 80, 0.1)';
            } else {
                this.style.borderColor = '#e0e0e0';
                this.style.boxShadow = 'none';
            }
        });

        // Reset on form reset
        passwordInput.addEventListener('input', function() {
            if (confirmInput.value && this.value && confirmInput.value === this.value) {
                confirmInput.style.borderColor = '#4caf50';
                confirmInput.style.boxShadow = '0 0 0 3px rgba(76, 175, 80, 0.1)';
            }
        });
    }

    // MAIN FORM SUBMISSION HANDLER
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('========== 📝 REGISTER FORM SUBMITTED! ==========');

        const name = document.getElementById('registerName').value.trim();
        const email = document.getElementById('registerEmail').value.trim();
        const username = document.getElementById('registerUsername').value.trim();
        const password = document.getElementById('registerPassword').value;
        const confirmPassword = document.getElementById('registerConfirmPassword').value;
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.textContent;

        console.log('Form data collected:');
        console.log('  Name:', name);
        console.log('  Email:', email);
        console.log('  Username:', username);
        console.log('  Password length:', password.length);

        // Validasi input kosong
        if (!name || !email || !username || !password || !confirmPassword) {
            showToast('Semua field harus diisi!', 'error');
            console.warn('⚠️ Validation failed: empty fields');
            return;
        }

        // Validasi panjang nama
        if (name.length < 3) {
            showToast('Nama minimal 3 karakter!', 'error');
            console.warn('⚠️ Validation failed: name too short');
            return;
        }

        // Validasi format email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showToast('Format email tidak valid!', 'error');
            console.warn('⚠️ Validation failed: invalid email');
            return;
        }

        // Validasi username
        if (username.length < 3) {
            showToast('Username minimal 3 karakter!', 'error');
            console.warn('⚠️ Validation failed: username too short');
            return;
        }

        // Validasi password match
        if (password !== confirmPassword) {
            showToast('Password tidak cocok!', 'error');
            console.warn('⚠️ Validation failed: passwords dont match');
            return;
        }

        // Validasi password length
        if (password.length < 6) {
            showToast('Password minimal 6 karakter!', 'error');
            console.warn('⚠️ Validation failed: password too short');
            return;
        }

        console.log('✅ All validations passed');
        btn.disabled = true;
        btn.textContent = 'Loading...';

        // Use async IIFE to handle async code
        (async () => {
            try {
                const url = `${API_BASE}/register.php`;
                console.log('🔗 Fetching:', url);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        name: name,
                        email: email,
                        username: username,
                        password: password
                    })
                });

                console.log('📊 Response status:', response.status);
                const data = await response.json();
                console.log('📦 Response data:', data);

                if (data.success) {
                    showToast('✓ Registrasi berhasil! Silakan login.', 'success');
                    console.log('✅ Registration successful!');
                    setTimeout(() => {
                        console.log('Switching to signin tab');
                        switchTab('signin');
                        registerForm.reset();
                        if (confirmInput) {
                            confirmInput.style.borderColor = '#e0e0e0';
                            confirmInput.style.boxShadow = 'none';
                        }
                        btn.disabled = false;
                        btn.textContent = originalText;
                    }, 1500);
                } else {
                    showToast(data.message || 'Registrasi gagal', 'error');
                    console.error('❌ Registration failed:', data.message);
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            } catch (error) {
                console.error('❌ Fetch error:', error);
                showToast('Terjadi kesalahan. Coba lagi. ' + error.message, 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })();
    });

    console.log('✅ Register form event listener attached');
}
