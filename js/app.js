/* ===========================
   STATE MANAGEMENT
   =========================== */

const state = {
  sensorData: {
    level: 75,
    height: 20,
    time: '18:00',
    temperature: null,
    ph: null,
  },
  controlStatus: {
    pompaMasuk: 'OFF',
    pompaBuang: 'OFF',
    servo: 'OFF',
    otomatisAir: 'OFF',
    otomatisPakan: 'OFF',
  },
  schedules: [],
  isAutoFeeding: true,
  isAutoWaterChange: false,
  waterChangeTimeouts: [],
};

/* ===========================
   DOM ELEMENTS
   =========================== */

const statLevel = document.getElementById('stat-level');
const statHeight = document.getElementById('stat-height');
const statTime = document.getElementById('stat-time');
const feedingLastTime = document.getElementById('feeding-last-time');
const feedingAutoToggle = document.getElementById('feeding-auto-toggle');
const btnFeedingNow = document.getElementById('btn-feeding-now');
const btnWaterDiscard = document.getElementById('btn-water-discard');
const btnWaterFill = document.getElementById('btn-water-fill');
const btnWaterDiscardStop = document.getElementById('btn-water-discard-stop');
const btnWaterFillStop = document.getElementById('btn-water-fill-stop');
const btnWaterChange = document.getElementById('btn-water-change');
const btnWaterChangeStop = document.getElementById('btn-water-change-stop');
const btnFeedingStop = document.getElementById('btn-feeding-stop');
const btnAddSchedule = document.getElementById('btn-add-schedule');
const schedulesList = document.getElementById('schedules-list');
const nextFeedingSchedule = document.getElementById('next-feeding-schedule');
const modalOverlay = document.getElementById('modal-overlay');
const btnModalCancel = document.getElementById('btn-modal-cancel');
const btnModalSubmit = document.getElementById('btn-modal-submit');

// LCD Status Elements
const lcdHeight = document.getElementById('lcd-height');
const lcdStatus = document.getElementById('lcd-status');
const lcdPumpIn = document.getElementById('lcd-pump-in');
const lcdPumpOut = document.getElementById('lcd-pump-out');
const lcdServoStatus = document.getElementById('lcd-servo-status');

/* ===========================
   API CALLS
   =========================== */

async function fetchSensorData() {
  try {
    const response = await fetch('api/get_sensor.php');
    const data = await response.json();

    if (data) {
      state.sensorData.level = Math.round(data.ketinggian || 0);
      state.sensorData.height = 20;
      state.sensorData.time = new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
      });

      updateSensorDisplay();
    }
  } catch (error) {
    console.error('Error fetching sensor data:', error);
  }
}

async function fetchControlStatus() {
  try {
    console.log('Fetching control status...');
    const response = await fetch('api/get_status.php');
    console.log('get_status.php response status:', response.status);
    
    if (!response.ok) {
      console.error('get_status.php returned:', response.status);
      return;
    }
    
    const data = await response.json();
    console.log('Control status data:', data);

    if (data && data.pompa_masuk !== undefined) {
      state.controlStatus = {
        pompaMasuk: data.pompa_masuk || 'OFF',
        pompaBuang: data.pompa_buang || 'OFF',
        servo: data.servo || 'OFF',
        otomatisAir: data.otomatis_air || 'OFF',
        otomatisPakan: data.otomatis_pakan || 'OFF',
      };

      state.isAutoFeeding = state.controlStatus.otomatisPakan === 'ON';
      updateControlDisplay();
      console.log('Control display updated');
    } else {
      console.error('Invalid data structure:', data);
    }
  } catch (error) {
    console.error('Error fetching control status:', error);
  }
}

async function fetchSchedules() {
  try {
    const response = await fetch('api/schedule.php?action=list');
    const data = await response.json();

    if (data.ok && data.schedules) {
      state.schedules = data.schedules;
      updateScheduleDisplay();
    }
  } catch (error) {
    console.error('Error fetching schedules:', error);
  }
}

async function saveSchedule(schedule) {
  try {
    const response = await fetch('api/schedule.php?action=create', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(schedule),
    });

    const data = await response.json();

    if (data.ok) {
      await fetchSchedules();
      return true;
    } else {
      showNotification('Error: ' + data.error, 'error');
      return false;
    }
  } catch (error) {
    console.error('Error saving schedule:', error);
    showNotification('Error: Connection failed', 'error');
    return false;
  }
}

async function removeSchedule(id) {
  try {
    const response = await fetch(`api/schedule.php?action=delete&id=${id}`, {
      method: 'DELETE',
    });

    const data = await response.json();

    if (data.ok) {
      await fetchSchedules();
      showNotification('Jadwal berhasil dihapus');
    } else {
      showNotification('Error: ' + data.error, 'error');
    }
  } catch (error) {
    console.error('Error deleting schedule:', error);
    showNotification('Error: Connection failed', 'error');
  }
}

async function setControl(device, value, duration = null) {
  try {
    let url = `api/set_control.php?device=${device}&value=${value}`;
    if (duration && device === 'servo') {
      url += `&duration=${duration}`;
    }
    
    console.log('setControl URL:', url);
    const response = await fetch(url);
    console.log('Response status:', response.status);
    
    const data = await response.json();
    console.log('setControl response:', data);

    if (data.ok) {
      console.log('setControl success, calling fetchControlStatus...');
      await fetchControlStatus();
      showNotification(`${device} set to ${value}`);
    } else {
      console.error('setControl error:', data.error);
      showNotification(`Error: ${data.error || 'Failed to update control'}`, 'error');
    }
  } catch (error) {
    console.error('Error setting control:', error);
    showNotification('Error: Connection failed', 'error');
  }
}

async function updateSensorValues(ketinggian) {
  try {
    const response = await fetch('api/update_sensor.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        ketinggian_air: ketinggian,
        status_air: ketinggian < 10 ? 'LOW' : ketinggian >= 22 ? 'HIGH' : 'OK',
      }),
    });

    const data = await response.json();
    return data.ok;
  } catch (error) {
    console.error('Error updating sensor:', error);
    return false;
  }
}

/* ===========================
   UI UPDATES
   =========================== */

function updateSensorDisplay() {
  if (statLevel) statLevel.textContent = `${state.sensorData.level}%`;
  if (statHeight) statHeight.textContent = `${state.sensorData.height}cm`;
  if (statTime) statTime.textContent = state.sensorData.time;
}

function updateLCDDisplay() {
  // Update servo status
  if (lcdServoStatus) {
    const servoStatus = state.controlStatus.servo;
    let displayStatus = 'IDLE';
    if (servoStatus === 'OPEN') displayStatus = 'OPEN';
    else if (servoStatus && servoStatus.startsWith('OPEN:')) displayStatus = 'OPEN';
    
    lcdServoStatus.textContent = displayStatus;
  }

  // Update pump out status
  if (lcdPumpOut) {
    const isPumpOutOn = state.controlStatus.pompaBuang === 'ON';
    lcdPumpOut.textContent = isPumpOutOn ? 'ON' : 'OFF';
    lcdPumpOut.classList.toggle('on', isPumpOutOn);
    lcdPumpOut.classList.toggle('off', !isPumpOutOn);
  }

  // Update pump in status
  if (lcdPumpIn) {
    const isPumpInOn = state.controlStatus.pompaMasuk === 'ON';
    lcdPumpIn.textContent = isPumpInOn ? 'ON' : 'OFF';
    lcdPumpIn.classList.toggle('on', isPumpInOn);
    lcdPumpIn.classList.toggle('off', !isPumpInOn);
  }
}

function updateControlDisplay() {
  const isAuto = state.isAutoFeeding;
  feedingAutoToggle.classList.toggle('off', !isAuto);
  feedingAutoToggle.textContent = isAuto ? '' : '';

  // Update LCD display juga
  updateLCDDisplay();
}

function updateScheduleDisplay() {
  if (!schedulesList) return;

  if (state.schedules.length === 0) {
    schedulesList.innerHTML =
      '<div class="schedule-item"><p class="text-center" style="color: #999;">Tidak ada jadwal</p></div>';
    return;
  }

  schedulesList.innerHTML = state.schedules
    .map(
      (schedule, index) => `
    <div class="schedule-item">
      <div class="schedule-item-time">${schedule.time}</div>
      <div class="schedule-item-details">
        <div class="schedule-item-label">${schedule.label}</div>
        <div class="schedule-item-portion">Porsi: ${schedule.portion}</div>
      </div>
      <button class="schedule-item-delete" onclick="deleteSchedule(${index})">🗑️</button>
    </div>
  `
    )
    .join('');

  // Update next feeding schedule
  updateNextFeedingDisplay();
}

function updateNextFeedingDisplay() {
  if (!nextFeedingSchedule || state.schedules.length === 0) return;

  const now = new Date();
  const currentTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
  const currentDay = now.getDay();
  const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
  const dayLabel = dayNames[currentDay];

  // Cari jadwal berikutnya
  let nextSchedule = null;
  let scheduleTodayFound = false;

  // Urutkan jadwal berdasarkan waktu
  const sortedSchedules = [...state.schedules].sort((a, b) => a.time.localeCompare(b.time));

  // Cari jadwal hari ini yang belum berlalu
  for (let schedule of sortedSchedules) {
    if (schedule.time > currentTime) {
      nextSchedule = schedule;
      scheduleTodayFound = true;
      break;
    }
  }

  // Jika tidak ada jadwal hari ini, ambil jadwal pertama hari berikutnya
  if (!nextSchedule && sortedSchedules.length > 0) {
    nextSchedule = sortedSchedules[0];
  }

  if (nextSchedule) {
    const scheduleHTML = `
      <p class="schedule-time" style="margin: 0;">⏱️ ${nextSchedule.time}</p>
      <p class="schedule-subtitle" style="font-size: 11px; color: rgba(255,255,255,0.8); margin: 4px 0 0 0;">${nextSchedule.label} • ${nextSchedule.portion}</p>
      ${scheduleTodayFound ? '<p class="schedule-status" style="font-size: 10px; color: #34c759; margin: 4px 0 0 0;">⏳ Hari ini</p>' : '<p class="schedule-status" style="font-size: 10px; color: #ff9999; margin: 4px 0 0 0;">🔜 Besok</p>'}
    `;
    nextFeedingSchedule.innerHTML = scheduleHTML;
  } else {
    nextFeedingSchedule.innerHTML = '<p class="schedule-time">Belum ada jadwal</p>';
  }
}

function showNotification(message, type = 'success') {
  const notification = document.createElement('div');
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: ${type === 'success' ? '#34c759' : '#f4736e'};
    color: white;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 14px;
    z-index: 2000;
    animation: slideDown 0.3s ease;
  `;
  notification.textContent = message;
  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.animation = 'slideUp 0.3s ease';
    setTimeout(() => notification.remove(), 300);
  }, 2000);
}

/* ===========================
   EVENT HANDLERS
   =========================== */

function onFeedingNow() {
  const modal = document.getElementById('modal-feeding-duration');
  const input = document.getElementById('feeding-duration-input');
  
  // Show modal and focus on input
  modal.style.display = 'flex';
  input.focus();
  input.select();
}

function onFeedingStop() {
  setControl('servo', 'IDLE');
  showNotification('Pemberian pakan dihentikan');
}

function onWaterDiscard() {
  const modal = document.getElementById('modal-water-discard-duration');
  const input = document.getElementById('water-discard-duration-input');
  
  // Show modal and focus on input
  modal.style.display = 'flex';
  input.focus();
  input.select();
}

function onWaterFill() {
  const modal = document.getElementById('modal-water-fill-duration');
  const input = document.getElementById('water-fill-duration-input');
  
  // Show modal and focus on input
  modal.style.display = 'flex';
  input.focus();
  input.select();
}

function onWaterChange() {
  // Start auto water change process
  state.isAutoWaterChange = true;
  showNotification('Memulai ganti air otomatis...');
  
  // Clear any existing timeouts
  state.waterChangeTimeouts.forEach(timeout => clearTimeout(timeout));
  state.waterChangeTimeouts = [];
  
  startAutoWaterChange();
}

function onWaterChangeStop() {
  // Stop auto water change process
  state.isAutoWaterChange = false;
  
  // Clear all pending timeouts
  state.waterChangeTimeouts.forEach(timeout => clearTimeout(timeout));
  state.waterChangeTimeouts = [];
  
  // Stop both pumps
  setControl('pompa_buang', 'OFF');
  setControl('pompa_masuk', 'OFF');
  showNotification('Ganti air dihentikan');
}

function startAutoWaterChange() {
  if (!state.isAutoWaterChange) return;
  
  const currentLevel = state.sensorData.level;
  
  // Phase 1: Buang air jika level > 5%
  if (currentLevel > 5) {
    setControl('pompa_buang', 'ON');
    showNotification(`Membuang air... Level: ${currentLevel.toFixed(1)}%`);
    
    // Check level every 500ms until reaches 5%
    const checkDiscardInterval = setInterval(() => {
      if (!state.isAutoWaterChange) {
        clearInterval(checkDiscardInterval);
        return;
      }
      
      const level = state.sensorData.level;
      if (level <= 5) {
        clearInterval(checkDiscardInterval);
        setControl('pompa_buang', 'OFF');
        showNotification(`Air berhasil dibuang. Level: ${level.toFixed(1)}%`);
        
        // Delay sebelum mulai isi air
        const delayTimeout = setTimeout(() => {
          startFillPhase();
        }, 2000);
        state.waterChangeTimeouts.push(delayTimeout);
      }
    }, 500);
  } else {
    // Langsung ke fase isi jika level sudah <= 5%
    startFillPhase();
  }
}

function startFillPhase() {
  if (!state.isAutoWaterChange) return;
  
  setControl('pompa_masuk', 'ON');
  showNotification('Mengisi air ke level maksimum (80%)...');
  
  // Check level every 500ms until reaches 80%
  const checkFillInterval = setInterval(() => {
    if (!state.isAutoWaterChange) {
      clearInterval(checkFillInterval);
      return;
    }
    
    const level = state.sensorData.level;
    if (level >= 80) {
      clearInterval(checkFillInterval);
      setControl('pompa_masuk', 'OFF');
      state.isAutoWaterChange = false;
      showNotification(`Ganti air selesai! Level: ${level.toFixed(1)}%`);
    }
  }, 500);
}

function onToggleAutoFeeding() {
  state.isAutoFeeding = !state.isAutoFeeding;
  const value = state.isAutoFeeding ? 'ON' : 'OFF';
  setControl('otomatis_pakan', value);
}

function openScheduleModal() {
  modalOverlay.classList.add('active');
  loadScheduleForm();
}

function closeScheduleModal() {
  modalOverlay.classList.remove('active');
}

function deleteSchedule(index) {
  const schedule = state.schedules[index];
  if (schedule && schedule.id) {
    removeSchedule(schedule.id);
  } else {
    state.schedules.splice(index, 1);
    updateScheduleDisplay();
    showNotification('Jadwal dihapus');
  }
}

function loadScheduleForm() {
  const form = document.getElementById('schedule-form');
  if (!form) return;

  form.innerHTML = `
    <div class="form-group">
      <label class="form-label">Waktu Pemberian Makan</label>
      <p class="form-description">Pilih waktu pemberian makan otomatis</p>
      <div style="position: relative; margin-top: 8px;">
        <input type="time" id="schedule-time" class="time-input" value="08:00">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Label Jadwal</label>
      <p class="form-description">Beri nama untuk jadwal ini</p>
      <input type="text" id="schedule-label" class="form-input" placeholder="Contoh: Pagi Hari, Siang, Sore">
    </div>

    <div class="form-group">
      <label class="form-label">Jumlah Porsi</label>
      <p class="form-description">Pilih ukuran porsi untuk jadwal ini</p>
      <div class="portion-options">
        <button class="portion-btn active" data-portion="small" onclick="selectPortion(this, 'Kecil (2g)')">
          2 detik
          <span class="portion-label">Kecil</span>
        </button>
        <button class="portion-btn" data-portion="medium" onclick="selectPortion(this, 'Normal (5g)')">
          5 detik
          <span class="portion-label">Normal</span>
        </button>
        <button class="portion-btn" data-portion="large" onclick="selectPortion(this, 'Besar (10g)')">
          10 detik
          <span class="portion-label">Besar</span>
        </button>
      </div>
      <input type="hidden" id="schedule-portion" value="Kecil (2g)">
    </div>

    <div class="form-group">
      <label class="form-label">Ulangi Setiap Hari</label>
      <p class="form-description">Pilih hari untuk jadwal ini aktif</p>
      <div class="days-selector">
        <button class="day-btn active" onclick="toggleDay(this, 'Mon')">Min</button>
        <button class="day-btn active" onclick="toggleDay(this, 'Tue')">Sen</button>
        <button class="day-btn active" onclick="toggleDay(this, 'Wed')">Sel</button>
        <button class="day-btn active" onclick="toggleDay(this, 'Thu')">Rab</button>
        <button class="day-btn active" onclick="toggleDay(this, 'Fri')">Kam</button>
        <button class="day-btn active" onclick="toggleDay(this, 'Sat')">Jum</button>
        <button class="day-btn active" onclick="toggleDay(this, 'Sun')">Sab</button>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Catatan</label>
      <textarea id="schedule-note" class="form-textarea" placeholder="Jadwal akan berjalan otomatis setiap hari sesuai pengaturan. Pastikan wadah pakan selalu terisi"></textarea>
    </div>
  `;
}

function selectPortion(button, label) {
  document.querySelectorAll('.portion-btn').forEach((btn) => btn.classList.remove('active'));
  button.classList.add('active');
  document.getElementById('schedule-portion').value = label;
}

function toggleDay(button, day) {
  button.classList.toggle('active');
}

function submitScheduleForm() {
  const time = document.getElementById('schedule-time')?.value || '08:00';
  const label = document.getElementById('schedule-label')?.value || 'Jadwal Baru';
  const portion = document.getElementById('schedule-portion')?.value || 'Normal (5g)';

  if (!time || !label) {
    showNotification('Mohon isi waktu dan label', 'error');
    return;
  }

  const schedule = {
    time,
    label,
    portion,
    days: 'Mon,Tue,Wed,Thu,Fri,Sat,Sun',
  };

  saveSchedule(schedule).then((success) => {
    if (success) {
      closeScheduleModal();
    }
  });
}

function submitFeedingDuration() {
  const input = document.getElementById('feeding-duration-input');
  const duration = parseInt(input.value) || 5;
  const modal = document.getElementById('modal-feeding-duration');
  
  // Validate duration
  if (duration < 1 || duration > 60) {
    showNotification('Durasi harus antara 1-60 detik', 'error');
    input.focus();
    return;
  }

  // Close modal
  modal.style.display = 'none';

  // Send control command
  setControl('servo', 'OPEN', duration);
  showNotification(`Memberikan pakan selama ${duration} detik...`);
}

function submitWaterDiscard() {
  const input = document.getElementById('water-discard-duration-input');
  const duration = parseInt(input.value) || 10;
  const modal = document.getElementById('modal-water-discard-duration');
  
  // Validate duration (no upper limit)
  if (duration < 1) {
    showNotification('Durasi harus minimal 1 detik', 'error');
    input.focus();
    return;
  }

  // Close modal
  modal.style.display = 'none';

  // Send control command
  setControl('pompa_buang', 'ON');
  showNotification(`Membuang air selama ${duration} detik...`);
  
  // Turn off pump after duration
  setTimeout(() => {
    setControl('pompa_buang', 'OFF');
  }, duration * 1000);
}

function submitWaterFill() {
  const input = document.getElementById('water-fill-duration-input');
  const duration = parseInt(input.value) || 10;
  const modal = document.getElementById('modal-water-fill-duration');
  
  // Validate duration (no upper limit)
  if (duration < 1) {
    showNotification('Durasi harus minimal 1 detik', 'error');
    input.focus();
    return;
  }

  // Close modal
  modal.style.display = 'none';

  // Send control command
  setControl('pompa_masuk', 'ON');
  showNotification(`Mengisi air selama ${duration} detik...`);
  
  // Turn off pump after duration
  setTimeout(() => {
    setControl('pompa_masuk', 'OFF');
  }, duration * 1000);
}

function onWaterDiscardStop() {
  setControl('pompa_buang', 'OFF');
  showNotification('Buang air dihentikan');
}

function onWaterFillStop() {
  setControl('pompa_masuk', 'OFF');
  showNotification('Isi air dihentikan');
}

/* ===========================
   INITIALIZATION
   =========================== */

function initializeEventListeners() {
  if (feedingAutoToggle) {
    feedingAutoToggle.addEventListener('click', onToggleAutoFeeding);
  }
  if (btnFeedingNow) {
    btnFeedingNow.addEventListener('click', onFeedingNow);
  }
  if (btnFeedingStop) {
    btnFeedingStop.addEventListener('click', onFeedingStop);
  }
  if (btnWaterDiscard) {
    btnWaterDiscard.addEventListener('click', onWaterDiscard);
  }
  if (btnWaterDiscardStop) {
    btnWaterDiscardStop.addEventListener('click', onWaterDiscardStop);
  }
  if (btnWaterFill) {
    btnWaterFill.addEventListener('click', onWaterFill);
  }
  if (btnWaterFillStop) {
    btnWaterFillStop.addEventListener('click', onWaterFillStop);
  }
  if (btnWaterChange) {
    btnWaterChange.addEventListener('click', onWaterChange);
  }
  if (btnWaterChangeStop) {
    btnWaterChangeStop.addEventListener('click', onWaterChangeStop);
  }
  if (btnAddSchedule) {
    btnAddSchedule.addEventListener('click', openScheduleModal);
  }
  if (btnModalCancel) {
    btnModalCancel.addEventListener('click', closeScheduleModal);
  }
  if (btnModalSubmit) {
    btnModalSubmit.addEventListener('click', submitScheduleForm);
  }

  // Duration modal handlers
  const btnDurationCancel = document.getElementById('btn-duration-cancel');
  const btnDurationSubmit = document.getElementById('btn-duration-submit');
  const modalFeedingDuration = document.getElementById('modal-feeding-duration');
  
  if (btnDurationCancel) {
    btnDurationCancel.addEventListener('click', () => {
      modalFeedingDuration.style.display = 'none';
    });
  }
  
  if (btnDurationSubmit) {
    btnDurationSubmit.addEventListener('click', submitFeedingDuration);
  }

  // Close duration modal on overlay click
  if (modalFeedingDuration) {
    modalFeedingDuration.addEventListener('click', (e) => {
      if (e.target === modalFeedingDuration) {
        modalFeedingDuration.style.display = 'none';
      }
    });
  }

  // Water discard duration modal handlers
  const btnWaterDiscardCancel = document.getElementById('btn-water-discard-cancel');
  const btnWaterDiscardSubmit = document.getElementById('btn-water-discard-submit');
  const modalWaterDiscardDuration = document.getElementById('modal-water-discard-duration');
  
  if (btnWaterDiscardCancel) {
    btnWaterDiscardCancel.addEventListener('click', () => {
      modalWaterDiscardDuration.style.display = 'none';
    });
  }
  
  if (btnWaterDiscardSubmit) {
    btnWaterDiscardSubmit.addEventListener('click', submitWaterDiscard);
  }

  // Close water discard modal on overlay click
  if (modalWaterDiscardDuration) {
    modalWaterDiscardDuration.addEventListener('click', (e) => {
      if (e.target === modalWaterDiscardDuration) {
        modalWaterDiscardDuration.style.display = 'none';
      }
    });
  }

  // Water fill duration modal handlers
  const btnWaterFillCancel = document.getElementById('btn-water-fill-cancel');
  const btnWaterFillSubmit = document.getElementById('btn-water-fill-submit');
  const modalWaterFillDuration = document.getElementById('modal-water-fill-duration');
  
  if (btnWaterFillCancel) {
    btnWaterFillCancel.addEventListener('click', () => {
      modalWaterFillDuration.style.display = 'none';
    });
  }
  
  if (btnWaterFillSubmit) {
    btnWaterFillSubmit.addEventListener('click', submitWaterFill);
  }

  // Close water fill modal on overlay click
  if (modalWaterFillDuration) {
    modalWaterFillDuration.addEventListener('click', (e) => {
      if (e.target === modalWaterFillDuration) {
        modalWaterFillDuration.style.display = 'none';
      }
    });
  }

  // Close modal on overlay click
  if (modalOverlay) {
    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) {
        closeScheduleModal();
      }
    });
  }
}

function startAutoRefresh() {
  // Fetch sensor data every 2 seconds
  setInterval(fetchSensorData, 2000);

  // Fetch control status every 2 seconds
  setInterval(fetchControlStatus, 2000);

  // Initial fetch
  fetchSensorData();
  fetchControlStatus();
  fetchSchedules();
}

function init() {
  console.log('AquaSmart app initialized');
  initializeEventListeners();
  startAutoRefresh();
  updateScheduleDisplay();
  
  // Update next feeding setiap menit
  setInterval(updateNextFeedingDisplay, 60000);
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
