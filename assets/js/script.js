// ===========================
// UTILITY FUNCTIONS
// ===========================

// Show Toast Notification
function showToast(message, type = 'success', duration = 3000) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast show ${type}`;
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, duration);
}

// Switch Auth Tab
function switchAuthTab(tab) {
    const tabs = document.querySelectorAll('.auth-tab-content');
    const buttons = document.querySelectorAll('.auth-tab-btn');
    
    tabs.forEach(t => t.classList.remove('active'));
    buttons.forEach(b => b.classList.remove('active'));
    
    document.getElementById(tab).classList.add('active');
    document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
}

// ===========================
// MODAL MANAGEMENT
// ===========================

// Auth Modal
const authModal = document.getElementById('authModal');
const closeAuthModal = document.getElementById('closeAuthModal');

closeAuthModal.addEventListener('click', () => {
    authModal.style.display = 'none';
});

authModal.addEventListener('click', (e) => {
    if (e.target === authModal) {
        authModal.style.display = 'none';
    }
});

// Action Modal
const actionModal = document.getElementById('actionModal');
const closeActionModal = document.getElementById('closeActionModal');
const cancelActionBtn = document.getElementById('cancelActionBtn');
const confirmActionBtn = document.getElementById('confirmActionBtn');

closeActionModal.addEventListener('click', () => {
    actionModal.style.display = 'none';
});

cancelActionBtn.addEventListener('click', () => {
    actionModal.style.display = 'none';
});

actionModal.addEventListener('click', (e) => {
    if (e.target === actionModal) {
        actionModal.style.display = 'none';
    }
});

// ===========================
// AUTH MODAL INTERACTIONS
// ===========================

const authTabBtns = document.querySelectorAll('.auth-tab-btn');
authTabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        switchAuthTab(tab);
    });
});

// Simulated Auth Submit
const authForms = document.querySelectorAll('.auth-tab-content');
authForms.forEach(form => {
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        showToast('Proses autentikasi berhasil!', 'success');
        authModal.style.display = 'none';
    });
});

// ===========================
// CONTROL BUTTONS
// ===========================

const controlButtons = document.querySelectorAll('[data-action]');

controlButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        const action = btn.dataset.action;
        openActionModal(action);
    });
});

function openActionModal(action) {
    const actionTitle = document.getElementById('actionTitle');
    const actionInfo = document.getElementById('actionInfo');
    const durationPresets = document.getElementById('durationPresets');
    
    // Reset modal
    document.querySelectorAll('.preset-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById('customDuration').value = '';
    
    if (action === 'feed') {
        actionTitle.textContent = 'Pemberian Makan Sekarang';
        actionInfo.textContent = 'Feeder akan memberikan pakan dalam sekali pemberian. Pilih jumlah pakan yang sesuai.';
        
        // Update presets for feeding
        durationPresets.innerHTML = `
            <button class="preset-btn" data-duration="0.25">
                <div class="preset-value">0,25d</div>
                <div class="preset-label">Kecil</div>
            </button>
            <button class="preset-btn active" data-duration="0.5">
                <div class="preset-value">0,5d</div>
                <div class="preset-label">Normal</div>
            </button>
            <button class="preset-btn" data-duration="1">
                <div class="preset-value">1d</div>
                <div class="preset-label">Besar</div>
            </button>
        `;
        
    } else if (action === 'fill') {
        actionTitle.textContent = 'Atur Durasi Pengisian';
        actionInfo.textContent = 'Air bersih akan diisi hingga mencapai durasi yang dipilih. Pompa akan berhenti otomatis setelah selesai.';
        
        // Update presets for filling
        durationPresets.innerHTML = `
            <button class="preset-btn" data-duration="5">
                <div class="preset-value">5m</div>
                <div class="preset-label">Setengah</div>
            </button>
            <button class="preset-btn active" data-duration="10">
                <div class="preset-value">10m</div>
                <div class="preset-label">Normal</div>
            </button>
            <button class="preset-btn" data-duration="15">
                <div class="preset-value">15m</div>
                <div class="preset-label">Penuh</div>
            </button>
        `;
        
    } else if (action === 'drain') {
        actionTitle.textContent = 'Atur Durasi Pembuangan';
        actionInfo.textContent = 'Air akan dibuang sesuai persentase yang dipilih. Pompa akan berhenti otomatis setelah selesai.';
        
        // Update presets for draining
        durationPresets.innerHTML = `
            <button class="preset-btn" data-duration="3">
                <div class="preset-value">3m</div>
                <div class="preset-label">Ringan</div>
            </button>
            <button class="preset-btn active" data-duration="5">
                <div class="preset-value">5m</div>
                <div class="preset-label">Normal</div>
            </button>
            <button class="preset-btn" data-duration="10">
                <div class="preset-value">10m</div>
                <div class="preset-label">Banyak</div>
            </button>
        `;
    }
    
    // Attach event listeners to new preset buttons
    attachPresetListeners();
    
    // Store current action
    confirmActionBtn.dataset.currentAction = action;
    
    actionModal.style.display = 'flex';
}

function attachPresetListeners() {
    const presetBtns = document.querySelectorAll('.preset-btn');
    presetBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            presetBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('customDuration').value = '';
        });
    });
}

// Custom duration input
document.getElementById('customDuration').addEventListener('input', () => {
    document.querySelectorAll('.preset-btn').forEach(btn => btn.classList.remove('active'));
});

// Confirm Action
confirmActionBtn.addEventListener('click', () => {
    const duration = getSelectedDuration();
    const action = confirmActionBtn.dataset.currentAction;
    
    if (!duration) {
        showToast('Pilih durasi terlebih dahulu!', 'warning');
        return;
    }
    
    // Simulate API call
    console.log(`Action: ${action}, Duration: ${duration}`);
    
    let actionMessage = '';
    if (action === 'feed') {
        actionMessage = `Feeder dimulai - Porsi: ${duration}d`;
    } else if (action === 'fill') {
        actionMessage = `Pengisian air dimulai - Durasi: ${duration} menit`;
    } else if (action === 'drain') {
        actionMessage = `Pembuangan air dimulai - Durasi: ${duration} menit`;
    }
    
    showToast(actionMessage, 'success');
    actionModal.style.display = 'none';
    
    // Simulate process
    simulateProcess(action, duration);
});

function getSelectedDuration() {
    const customDuration = document.getElementById('customDuration').value;
    if (customDuration) {
        return customDuration;
    }
    
    const activePreset = document.querySelector('.preset-btn.active');
    return activePreset ? activePreset.dataset.duration : null;
}

function simulateProcess(action, duration) {
    // This simulates a real process - you would replace this with actual API calls
    console.log(`Simulating ${action} with duration ${duration}`);
    
    // Update stats after process (optional)
    setTimeout(() => {
        if (action === 'feed') {
            console.log('Feeding complete');
        } else if (action === 'fill') {
            // Simulate water level increase
            updateWaterLevel(75);
        } else if (action === 'drain') {
            // Simulate water level decrease
            updateWaterLevel(50);
        }
    }, 3000);
}

function updateWaterLevel(newLevel) {
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        if (card.querySelector('.stat-label')?.textContent.includes('Level Air')) {
            card.querySelector('.stat-value').textContent = newLevel + '%';
        }
    });
}

// ===========================
// HAMBURGER MENU
// ===========================

const hamburger = document.getElementById('hamburger');
const navbarMenu = document.getElementById('navbarMenu');

hamburger.addEventListener('click', () => {
    navbarMenu.classList.toggle('active');
});

// Close menu when clicking on a link
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        navbarMenu.classList.remove('active');
    });
});

// ===========================
// AUTO TOGGLE SWITCH
// ===========================

const feederAuto = document.getElementById('feederAuto');

feederAuto.addEventListener('change', () => {
    if (feederAuto.checked) {
        showToast('Mode Otomatis: Diaktifkan', 'success');
        console.log('Auto mode enabled for feeder');
    } else {
        showToast('Mode Otomatis: Dinonaktifkan', 'warning');
        console.log('Auto mode disabled for feeder');
    }
});

// ===========================
// INITIALIZATION
// ===========================

document.addEventListener('DOMContentLoaded', () => {
    console.log('AquaSmart Dashboard loaded successfully');
    
    // Initialize tooltips or any other features
    initializeTooltips();
});

function initializeTooltips() {
    // Add tooltips to stat cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.cursor = 'help';
        });
    });
}

// ===========================
// KEYBOARD SHORTCUTS
// ===========================

document.addEventListener('keydown', (e) => {
    // Close modals with Escape key
    if (e.key === 'Escape') {
        authModal.style.display = 'none';
        actionModal.style.display = 'none';
    }
    
    // Debug: Log shortcut info
    if (e.ctrlKey && e.key === 'k') {
        e.preventDefault();
        console.log('Search feature placeholder');
    }
});

// ===========================
// DYNAMIC NAVBAR
// ===========================

let lastScrollTop = 0;
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    let scrollTop = window.scrollY;
    
    // Add shadow on scroll
    if (scrollTop > 10) {
        navbar.style.boxShadow = 'var(--shadow-lg)';
    } else {
        navbar.style.boxShadow = 'var(--shadow-md)';
    }
    
    lastScrollTop = scrollTop;
});

// ===========================
// DATA UPDATE SIMULATION
// ===========================

function simulateDataUpdate() {
    // This function simulates real-time data updates from an API
    setInterval(() => {
        // Update temperature (random between 24-28°C)
        const tempValue = Math.floor(Math.random() * 5) + 24;
        
        // Update pH (random between 6.5-7.5)
        const phValue = (Math.random() * 1 + 6.5).toFixed(1);
        
        // Update water level (random between 60-85%)
        const waterLevel = Math.floor(Math.random() * 25) + 60;
        
        // Apply updates (only in development, comment out in production)
        // updateStatsDisplay(waterLevel, tempValue, phValue);
        
    }, 30000); // Update every 30 seconds
}

function updateStatsDisplay(waterLevel, temp, ph) {
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        const label = card.querySelector('.stat-label').textContent;
        const valueElement = card.querySelector('.stat-value');
        
        if (label.includes('Level Air')) {
            valueElement.textContent = waterLevel + '%';
        } else if (label.includes('Suhu Air')) {
            valueElement.textContent = temp + '°C';
        } else if (label.includes('pH Air')) {
            valueElement.textContent = ph;
        }
    });
}

// Uncomment to enable simulated updates
// simulateDataUpdate();

// ===========================
// SCROLLBAR HIDING VIA JAVASCRIPT
// =========================== 
// Force hide all scrollbars via JS as backup
function hideAllScrollbars() {
    // Allow scroll but hide scrollbar via CSS
    const style = document.createElement('style');
    style.innerHTML = `
        html, body {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        
        html::-webkit-scrollbar,
        body::-webkit-scrollbar,
        *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        
        *::-webkit-scrollbar-track {
            display: none !important;
        }
        
        *::-webkit-scrollbar-thumb {
            display: none !important;
        }
    `;
    document.head.appendChild(style);
}

// Call on page load
window.addEventListener('DOMContentLoaded', hideAllScrollbars);

// ===========================
// LOGGING & DEBUG
// ===========================

console.log('%c🐠 AquaSmart Dashboard', 'color: #1e88e5; font-size: 18px; font-weight: bold;');
console.log('%cVersion 1.0.0', 'color: #666; font-size: 12px;');
console.log('%cDeveloped for IoT Aquarium Control System', 'color: #666; font-size: 12px;');
