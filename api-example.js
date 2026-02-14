/**
 * AQUASMART - API Integration Examples
 * 
 * File ini berisi contoh implementasi API calls
 * untuk integrasi dengan backend
 * 
 * Version: 1.0.0
 * Author: AquaSmart Development Team
 */

// ===========================
// API CONFIGURATION
// ===========================

const API_BASE_URL = 'http://localhost:3000/api';
const API_TIMEOUT = 10000; // 10 seconds

// Token placeholder - replace dengan token dari backend
let authToken = localStorage.getItem('authToken') || null;

// ===========================
// API REQUEST HELPER
// ===========================

/**
 * Utility function untuk membuat API requests
 * @param {string} endpoint - API endpoint
 * @param {object} options - Fetch options
 * @returns {Promise}
 */
async function apiRequest(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;
    
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            ...(authToken && { 'Authorization': `Bearer ${authToken}` })
        },
        timeout: API_TIMEOUT
    };
    
    const mergedOptions = { ...defaultOptions, ...options };
    
    try {
        const response = await fetch(url, mergedOptions);
        
        if (!response.ok) {
            if (response.status === 401) {
                // Token expired - redirect to login
                handleAuthError();
            }
            throw new Error(`API Error: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Request failed:', error);
        throw error;
    }
}

/**
 * Handle authentication errors
 */
function handleAuthError() {
    localStorage.removeItem('authToken');
    showToast('Session expired. Please login again.', 'error');
    window.location.href = '/auth.html';
}

// ===========================
// AUTHENTICATION ENDPOINTS
// ===========================

/**
 * Sign In dengan email & password
 */
async function signIn(email, password) {
    try {
        const response = await apiRequest('/auth/signin', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
        
        // Simpan token
        authToken = response.token;
        localStorage.setItem('authToken', authToken);
        localStorage.setItem('user', JSON.stringify(response.user));
        
        showToast('Login berhasil!', 'success');
        return response;
    } catch (error) {
        showToast('Login gagal: ' + error.message, 'error');
        throw error;
    }
}

/**
 * Sign Up - membuat akun baru
 */
async function signUp(name, email, password, confirmPassword) {
    try {
        if (password !== confirmPassword) {
            throw new Error('Password tidak cocok');
        }
        
        const response = await apiRequest('/auth/signup', {
            method: 'POST',
            body: JSON.stringify({ name, email, password })
        });
        
        showToast('Akun berhasil dibuat!', 'success');
        return response;
    } catch (error) {
        showToast('Registrasi gagal: ' + error.message, 'error');
        throw error;
    }
}

/**
 * Sign Out
 */
async function signOut() {
    try {
        await apiRequest('/auth/signout', { method: 'POST' });
        
        authToken = null;
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
        
        showToast('Logout berhasil', 'success');
        window.location.href = '/auth.html';
    } catch (error) {
        console.error('Logout error:', error);
    }
}

/**
 * Forgot Password
 */
async function requestPasswordReset(email) {
    try {
        const response = await apiRequest('/auth/forgot-password', {
            method: 'POST',
            body: JSON.stringify({ email })
        });
        
        showToast('Instruksi reset password dikirim ke email', 'success');
        return response;
    } catch (error) {
        showToast('Permintaan gagal: ' + error.message, 'error');
        throw error;
    }
}

// ===========================
// FEEDER CONTROL ENDPOINTS
// ===========================

/**
 * Kontrol pemberian makan (one-time)
 */
async function controlFeeder(portionSize) {
    try {
        const response = await apiRequest('/feeder/feed', {
            method: 'POST',
            body: JSON.stringify({ 
                portion_size: portionSize,
                type: 'manual'
            })
        });
        
        showToast(`Feeder dimulai - Porsi: ${portionSize}d`, 'success');
        updateLastActionTime('feed');
        return response;
    } catch (error) {
        showToast('Feeder control gagal: ' + error.message, 'error');
        throw error;
    }
}

/**
 * Hentikan pemberian makan
 */
async function stopFeeder() {
    try {
        const response = await apiRequest('/feeder/stop', {
            method: 'POST'
        });
        
        showToast('Feeder dihentikan', 'success');
        return response;
    } catch (error) {
        showToast('Stop feeder gagal: ' + error.message, 'error');
        throw error;
    }
}

/**
 * Dapatkan status feeder
 */
async function getFeederStatus() {
    try {
        const response = await apiRequest('/feeder/status', {
            method: 'GET'
        });
        
        return response;
    } catch (error) {
        console.error('Get feeder status failed:', error);
        throw error;
    }
}

// ===========================
// WATER CONTROL ENDPOINTS
// ===========================

/**
 * Pengisian air
 */
async function fillWater(duration) {
    try {
        const response = await apiRequest('/water/fill', {
            method: 'POST',
            body: JSON.stringify({ 
                duration: parseInt(duration),
                unit: 'minutes'
            })
        });
        
        showToast(`Pengisian air dimulai - ${duration} menit`, 'success');
        updateLastActionTime('fill');
        return response;
    } catch (error) {
        showToast('Water fill gagal: ' + error.message, 'error');
        throw error;
    }
}

/**
 * Pembuangan air
 */
async function drainWater(duration) {
    try {
        const response = await apiRequest('/water/drain', {
            method: 'POST',
            body: JSON.stringify({ 
                duration: parseInt(duration),
                unit: 'minutes'
            })
        });
        
        showToast(`Pembuangan air dimulai - ${duration} menit`, 'success');
        updateLastActionTime('drain');
        return response;
    } catch (error) {
        showToast('Water drain gagal: ' + error.message, 'error');
        throw error;
    }
}

/**
 * Hentikan pengisian/pembuangan
 */
async function stopWaterControl() {
    try {
        const response = await apiRequest('/water/stop', {
            method: 'POST'
        });
        
        showToast('Kontrol air dihentikan', 'success');
        return response;
    } catch (error) {
        showToast('Stop water control gagal: ' + error.message, 'error');
        throw error;
    }
}

/**
 * Dapatkan status air
 */
async function getWaterStatus() {
    try {
        const response = await apiRequest('/water/status', {
            method: 'GET'
        });
        
        return response;
    } catch (error) {
        console.error('Get water status failed:', error);
        throw error;
    }
}

// ===========================
// SENSOR DATA ENDPOINTS
// ===========================

/**
 * Dapatkan data sensor real-time
 */
async function getSensorData() {
    try {
        const response = await apiRequest('/sensors/data', {
            method: 'GET'
        });
        
        return response;
    } catch (error) {
        console.error('Get sensor data failed:', error);
        throw error;
    }
}

/**
 * Update display dengan data sensor
 */
async function updateSensorDisplay() {
    try {
        const data = await getSensorData();
        
        // Update stat cards
        updateStatCard('Level Air', data.water_level, '%');
        updateStatCard('Suhu Air', data.temperature, '°C');
        updateStatCard('pH Air', data.ph, '');
        
        return data;
    } catch (error) {
        console.error('Update sensor display failed:', error);
    }
}

/**
 * Helper function untuk update stat card
 */
function updateStatCard(label, value, unit) {
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        if (card.querySelector('.stat-label')?.textContent.includes(label)) {
            card.querySelector('.stat-value').textContent = value + unit;
        }
    });
}

// ===========================
// SCHEDULE ENDPOINTS
// ===========================

/**
 * Dapatkan semua jadwal
 */
async function getSchedules() {
    try {
        const response = await apiRequest('/schedules', {
            method: 'GET'
        });
        
        return response.schedules;
    } catch (error) {
        console.error('Get schedules failed:', error);
        throw error;
    }
}

/**
 * Buat jadwal baru
 */
async function createSchedule(scheduleData) {
    try {
        const response = await apiRequest('/schedules', {
            method: 'POST',
            body: JSON.stringify(scheduleData)
        });
        
        showToast('Jadwal berhasil dibuat', 'success');
        return response;
    } catch (error) {
        showToast('Membuat jadwal gagal: ' + error.message, 'error');
        throw error;
    }
}

/**
 * Update jadwal
 */
async function updateSchedule(scheduleId, scheduleData) {
    try {
        const response = await apiRequest(`/schedules/${scheduleId}`, {
            method: 'PUT',
            body: JSON.stringify(scheduleData)
        });
        
        showToast('Jadwal berhasil diupdate', 'success');
        return response;
    } catch (error) {
        showToast('Update jadwal gagal: ' + error.message, 'error');
        throw error;
    }
}

/**
 * Hapus jadwal
 */
async function deleteSchedule(scheduleId) {
    try {
        const response = await apiRequest(`/schedules/${scheduleId}`, {
            method: 'DELETE'
        });
        
        showToast('Jadwal berhasil dihapus', 'success');
        return response;
    } catch (error) {
        showToast('Hapus jadwal gagal: ' + error.message, 'error');
        throw error;
    }
}

/**
 * Toggle jadwal (aktif/nonaktif)
 */
async function toggleSchedule(scheduleId, isActive) {
    try {
        const response = await apiRequest(`/schedules/${scheduleId}/toggle`, {
            method: 'PATCH',
            body: JSON.stringify({ is_active: isActive })
        });
        
        showToast(`Jadwal ${isActive ? 'diaktifkan' : 'dinonaktifkan'}`, 'success');
        return response;
    } catch (error) {
        showToast('Toggle jadwal gagal: ' + error.message, 'error');
        throw error;
    }
}

// ===========================
// ACTIVITY LOG ENDPOINTS
// ===========================

/**
 * Dapatkan activity log
 */
async function getActivityLog(limit = 50) {
    try {
        const response = await apiRequest(`/logs?limit=${limit}`, {
            method: 'GET'
        });
        
        return response.logs;
    } catch (error) {
        console.error('Get activity log failed:', error);
        throw error;
    }
}

/**
 * Clear activity log
 */
async function clearActivityLog() {
    try {
        const response = await apiRequest('/logs/clear', {
            method: 'DELETE'
        });
        
        showToast('Activity log dibersihkan', 'success');
        return response;
    } catch (error) {
        showToast('Clear log gagal: ' + error.message, 'error');
        throw error;
    }
}

// ===========================
// UTILITY FUNCTIONS
// ===========================

/**
 * Update last action timestamp
 */
function updateLastActionTime(action) {
    const timestamp = new Date().toLocaleTimeString('id-ID');
    console.log(`${action.toUpperCase()} executed at ${timestamp}`);
    // Simpan ke localStorage atau update UI
}

/**
 * Polling untuk update data real-time
 */
function startSensorPolling(interval = 5000) {
    setInterval(async () => {
        try {
            await updateSensorDisplay();
        } catch (error) {
            console.error('Polling failed:', error);
        }
    }, interval);
}

/**
 * Check koneksi ke API
 */
async function checkAPIConnection() {
    try {
        const response = await fetch(`${API_BASE_URL}/health`, {
            method: 'GET'
        });
        return response.ok;
    } catch (error) {
        console.error('API connection failed:', error);
        return false;
    }
}

// ===========================
// INITIALIZATION
// ===========================

/**
 * Initialize API connection saat page load
 */
document.addEventListener('DOMContentLoaded', async () => {
    // Check API connection
    const connected = await checkAPIConnection();
    if (!connected) {
        showToast('Tidak terhubung ke server', 'warning');
        console.warn('Using demo mode (no API connection)');
    }
    
    // Load user data jika sudah login
    const user = localStorage.getItem('user');
    if (user) {
        console.log('User logged in:', JSON.parse(user));
    }
});

// ===========================
// EXAMPLE USAGE
// ===========================

/*
// Di index.html - override confirmActionBtn click event
confirmActionBtn.addEventListener('click', async () => {
    const duration = getSelectedDuration();
    const action = confirmActionBtn.dataset.currentAction;
    
    try {
        if (action === 'feed') {
            await controlFeeder(duration);
        } else if (action === 'fill') {
            await fillWater(duration);
        } else if (action === 'drain') {
            await drainWater(duration);
        }
        
        actionModal.style.display = 'none';
    } catch (error) {
        console.error('Action failed:', error);
    }
});

// Start polling sensor data setiap 5 detik
startSensorPolling(5000);
*/

// ===========================
// EXPORT UNTUK MODUL
// ===========================

// Jika menggunakan ES6 modules:
// export {
//     signIn, signUp, signOut,
//     controlFeeder, fillWater, drainWater,
//     getSchedules, createSchedule, updateSchedule, deleteSchedule,
//     getSensorData, getActivityLog
// };
