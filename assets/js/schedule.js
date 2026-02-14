/**
 * Schedule Manager - Feeding Schedule Management
 * File: assets/js/schedule.js
 * Handles CRUD operations for feeding schedules via REST API
 */

const API_BASE = (() => {
    // Calculate API base path from current URL
    // From /iot_ikan/schedule.php → /iot_ikan/api
    const pathParts = window.location.pathname.split('/');
    pathParts.pop(); // Remove filename
    return pathParts.join('/') + '/api';
})();

console.log('[ScheduleManager] API Base:', API_BASE);

class ScheduleManager {
    constructor() {
        this.schedules = [];
        this.loadingMessage = document.getElementById('loadingMessage');
        this.emptyMessage = document.getElementById('emptyMessage');
        this.scheduleTable = document.getElementById('scheduleTable');
        this.scheduleTableBody = document.getElementById('scheduleTableBody');
        
        this.loadSchedules();
        this.setupEventListeners();
    }

    /**
     * Load all schedules from API
     */
    async loadSchedules() {
        try {
            console.log('[ScheduleManager] Loading schedules from API...');
            
            const response = await fetch(`${API_BASE}/schedules.php?action=list`);
            
            if (response.status === 401) {
                console.error('[ScheduleManager] Unauthorized - session expired');
                this.showToast('Session expired, please login again', 'error');
                setTimeout(() => window.location.href = 'auth.php', 2000);
                return;
            }
            
            const data = await response.json();
            console.log('[ScheduleManager] API Response:', data);
            
            if (data.success) {
                this.schedules = data.data || [];
                this.renderSchedules();
            } else {
                console.error('[ScheduleManager] API Error:', data.message);
                this.showToast('Error: ' + data.message, 'error');
                this.schedules = [];
                this.renderSchedules();
            }
        } catch (error) {
            console.error('[ScheduleManager] Error loading schedules:', error);
            this.showToast('Gagal memuat jadwal: ' + error.message, 'error');
        }
    }

    /**
     * Add new schedule
     */
    async addSchedule(formData) {
        try {
            // Get checked days
            const daysCheckboxes = document.querySelectorAll('input[name="days"]:checked');
            const days = Array.from(daysCheckboxes).map(cb => cb.value);
            
            if (days.length === 0) {
                this.showToast('Pilih minimal satu hari', 'warning');
                return;
            }
            
            const payload = {
                time: formData.get('time'),
                label: formData.get('label'),
                portion: parseInt(formData.get('portion')),
                days: days
            };
            
            console.log('[ScheduleManager] Adding schedule:', payload);
            
            const response = await fetch(`${API_BASE}/schedules.php?action=add`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            
            const data = await response.json();
            console.log('[ScheduleManager] Add response:', data);
            
            if (data.success) {
                console.log('[ScheduleManager] Schedule added with ID:', data.id);
                this.showToast('✅ Jadwal berhasil ditambahkan!', 'success');
                document.getElementById('scheduleForm').reset();
                this.loadSchedules();
            } else {
                this.showToast('❌ Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('[ScheduleManager] Error adding schedule:', error);
            this.showToast('❌ Gagal menambahkan jadwal', 'error');
        }
    }

    /**
     * Delete schedule
     */
    async deleteSchedule(id) {
        if (!confirm('Yakin ingin menghapus jadwal ini?')) {
            return;
        }
        
        try {
            console.log('[ScheduleManager] Deleting schedule ID:', id);
            
            const response = await fetch(`${API_BASE}/schedules.php?action=delete`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            
            const data = await response.json();
            console.log('[ScheduleManager] Delete response:', data);
            
            if (data.success) {
                console.log('[ScheduleManager] Schedule deleted');
                this.showToast('✅ Jadwal berhasil dihapus!', 'success');
                this.loadSchedules();
            } else {
                this.showToast('❌ Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('[ScheduleManager] Error deleting schedule:', error);
            this.showToast('❌ Gagal menghapus jadwal', 'error');
        }
    }

    /**
     * Render schedules in table
     */
    renderSchedules() {
        if (!this.scheduleTableBody) return;
        
        this.scheduleTableBody.innerHTML = '';
        
        if (this.schedules.length === 0) {
            if (this.loadingMessage) this.loadingMessage.style.display = 'none';
            if (this.emptyMessage) this.emptyMessage.style.display = 'block';
            if (this.scheduleTable) this.scheduleTable.style.display = 'none';
            return;
        }
        
        if (this.loadingMessage) this.loadingMessage.style.display = 'none';
        if (this.emptyMessage) this.emptyMessage.style.display = 'none';
        if (this.scheduleTable) this.scheduleTable.style.display = 'table';
        
        this.schedules.forEach(schedule => {
            const days = this.parseDays(schedule.days);
            
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #eee';
            tr.innerHTML = `
                <td style="padding: 12px; color: #333; font-weight: 600;">${schedule.time}</td>
                <td style="padding: 12px; color: #666;">${schedule.label}</td>
                <td style="padding: 12px; color: #666;">${schedule.portion}g</td>
                <td style="padding: 12px; color: #666; font-size: 13px;">${days.join(', ')}</td>
                <td style="padding: 12px; text-align: center;">
                    <button class="btn-edit" onclick="scheduleManager.editSchedule(${schedule.id})" 
                            style="padding: 6px 12px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 5px; font-size: 12px;">
                        Edit
                    </button>
                    <button class="btn-delete" onclick="scheduleManager.deleteSchedule(${schedule.id})" 
                            style="padding: 6px 12px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
                        Hapus
                    </button>
                </td>
            `;
            this.scheduleTableBody.appendChild(tr);
        });
    }

    /**
     * Parse days JSON array
     */
    parseDays(daysJSON) {
        if (!daysJSON) return [];
        
        try {
            if (typeof daysJSON === 'string') {
                const parsed = JSON.parse(daysJSON);
                return Array.isArray(parsed) ? parsed : [];
            }
            return Array.isArray(daysJSON) ? daysJSON : [];
        } catch (e) {
            console.warn('[ScheduleManager] Error parsing days:', e);
            return [];
        }
    }

    /**
     * Edit schedule (TODO: Implement edit dialog)
     */
    editSchedule(id) {
        const schedule = this.schedules.find(s => s.id === id);
        if (schedule) {
            console.log('[ScheduleManager] Edit schedule:', schedule);
            this.showToast('⚠️ Fitur edit belum tersedia', 'warning');
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        const form = document.getElementById('scheduleForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.addSchedule(new FormData(form));
            });
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const toast = document.getElementById('toast');
        if (!toast) return;
        
        toast.textContent = message;
        toast.style.display = 'block';
        toast.style.background = type === 'error' ? '#e74c3c' : type === 'success' ? '#27ae60' : type === 'warning' ? '#f39c12' : '#3498db';
        toast.style.color = 'white';
        toast.style.padding = '15px 20px';
        toast.style.borderRadius = '5px';
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.style.minWidth = '200px';
        toast.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
        
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('[ScheduleManager] DOM Content Loaded - Initializing...');
    window.scheduleManager = new ScheduleManager();
});

// Global error handler
window.addEventListener('error', (event) => {
    console.error('[ScheduleManager] Global error:', event.error);
});

window.addEventListener('unhandledrejection', (event) => {
    console.error('[ScheduleManager] Unhandled promise rejection:', event.reason);
});

