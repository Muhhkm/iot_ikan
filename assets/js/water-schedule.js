// ===========================
// WATER SCHEDULE - JAVASCRIPT
// ===========================

// Get page type from URL or filename
const currentPage = window.location.pathname;
const isFillPage = currentPage.includes('fill-schedule');
const isDrainPage = currentPage.includes('drain-schedule');

// Element References
const durationBtns = document.querySelectorAll('.duration-btn');
const customDurationInput = document.getElementById('customDuration');
const cancelBtn = document.getElementById('cancelBtn');
const submitBtn = document.getElementById('submitBtn');

// State
let selectedDuration = isFillPage ? 10 : 5; // Default based on page

// ===========================
// DURATION SELECTION
// ===========================

durationBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        selectDuration(btn);
    });
});

function selectDuration(btn) {
    durationBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    selectedDuration = parseInt(btn.dataset.duration);
    customDurationInput.value = '';
}

// Custom duration input
customDurationInput.addEventListener('input', () => {
    durationBtns.forEach(b => b.classList.remove('active'));
    if (customDurationInput.value) {
        selectedDuration = parseInt(customDurationInput.value) || 0;
    }
});

function getSelectedDuration() {
    if (customDurationInput.value) {
        return parseInt(customDurationInput.value);
    }
    const activeBtn = document.querySelector('.duration-btn.active');
    return activeBtn ? parseInt(activeBtn.dataset.duration) : selectedDuration;
}

// ===========================
// FORM SUBMISSION
// ===========================

submitBtn.addEventListener('click', (e) => {
    e.preventDefault();
    submitSchedule();
});

function submitSchedule() {
    const duration = getSelectedDuration();

    if (!duration || duration < 1) {
        showToast('Pilih atau masukkan durasi yang valid', 'warning');
        return;
    }

    if (duration > 60) {
        showToast('Durasi maksimal 60 menit', 'warning');
        return;
    }

    // Determine action type
    const actionType = isFillPage ? 'pengisian' : 'pembuangan';
    
    // Save to localStorage
    const scheduleData = {
        type: actionType,
        duration: duration,
        timestamp: new Date().toISOString()
    };

    localStorage.setItem(`${actionType}_schedule`, JSON.stringify(scheduleData));

    // Show success message
    const message = isFillPage 
        ? `Durasi pengisian air disimpan: ${duration} menit` 
        : `Durasi pembuangan air disimpan: ${duration} menit`;
    
    showToast(message, 'success');

    // Redirect to dashboard after 1.5 seconds
    setTimeout(() => {
        window.location.href = 'index.html';
    }, 1500);
}

// ===========================
// CANCEL ACTION
// ===========================

cancelBtn.addEventListener('click', () => {
    if (confirm('Batalkan perubahan?')) {
        window.history.back();
    }
});

// ===========================
// KEYBOARD SHORTCUTS
// ===========================

document.addEventListener('keydown', (e) => {
    // Enter to submit
    if (e.key === 'Enter') {
        submitSchedule();
    }
    // Escape to cancel
    if (e.key === 'Escape') {
        cancelBtn.click();
    }
});

// ===========================
// INITIALIZATION
// ===========================

document.addEventListener('DOMContentLoaded', () => {
    console.log(`Water Schedule Page Loaded - Type: ${isFillPage ? 'Fill' : 'Drain'}`);
    
    // Load saved duration if exists
    loadSavedDuration();
});

function loadSavedDuration() {
    const actionType = isFillPage ? 'pengisian' : 'pembuangan';
    const savedData = localStorage.getItem(`${actionType}_schedule`);
    
    if (savedData) {
        try {
            const data = JSON.parse(savedData);
            const durationValue = data.duration;
            
            // Try to find and select matching button
            const matchingBtn = Array.from(durationBtns).find(
                btn => parseInt(btn.dataset.duration) === durationValue
            );
            
            if (matchingBtn) {
                selectDuration(matchingBtn);
            } else {
                customDurationInput.value = durationValue;
                selectedDuration = durationValue;
            }
        } catch (e) {
            console.error('Error loading saved duration:', e);
        }
    }
}

// ===========================
// SHOW TOAST NOTIFICATION
// ===========================

function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    if (toast) {
        toast.textContent = message;
        toast.className = `toast show ${type}`;
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }
}

// ===========================
// AUTO-FILL FOR TESTING
// ===========================

// Uncomment for testing purposes
// document.addEventListener('DOMContentLoaded', () => {
//     customDurationInput.value = '12';
//     console.log('Auto-filled for testing');
// });

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

