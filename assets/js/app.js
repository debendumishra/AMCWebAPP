/**
 * ServicEngine Core Application Script
 */

const App = {
    baseUrl: document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',

    init() {
        this.initSlaTimers();
        this.initTooltips();
        this.initSidebarToggle();
    },

    /**
     * Display a modern floating toast notification
     */
    toast(message, type = 'success') {
        const icon = type === 'success' ? 'bi-check-circle-fill' : (type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill');
        const bgClass = type === 'success' ? 'text-bg-success' : (type === 'danger' ? 'text-bg-danger' : 'text-bg-primary');
        
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center ${bgClass} border-0 shadow-lg`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi ${icon} fs-5"></i>
                    <div>${message}</div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);
        const bsToast = new bootstrap.Toast(toastEl, { delay: 4000 });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    },

    /**
     * Make an asynchronous AJAX request with CSRF header
     */
    async fetch(url, options = {}) {
        options.headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.csrfToken,
            ...options.headers
        };

        if (options.body && !(options.body instanceof FormData) && typeof options.body === 'object') {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(options.body);
        }

        try {
            const response = await fetch(url.startsWith('http') ? url : this.baseUrl + '/' + url.replace(/^\//, ''), options);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API Request Error:', error);
            return { success: false, message: 'Network or server communication error.' };
        }
    },

    /**
     * Dynamic SLA countdown timers
     */
    initSlaTimers() {
        const updateTimers = () => {
            document.querySelectorAll('[data-sla-deadline]').forEach(el => {
                const deadlineStr = el.getAttribute('data-sla-deadline');
                if (!deadlineStr) return;

                const deadline = new Date(deadlineStr).getTime();
                const now = new Date().getTime();
                const diff = deadline - now;

                if (diff <= 0) {
                    el.innerHTML = `<span class="badge bg-danger text-white"><i class="bi bi-alarm-fill me-1"></i>BREACHED (${Math.abs(Math.round(diff / 60000))}m ago)</span>`;
                } else {
                    const hrs = Math.floor(diff / (1000 * 60 * 60));
                    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const badgeClass = hrs < 1 ? 'bg-warning text-dark' : 'bg-success text-white';
                    el.innerHTML = `<span class="badge ${badgeClass}"><i class="bi bi-clock-history me-1"></i>${hrs}h ${mins}m left</span>`;
                }
            });
        };

        updateTimers();
        setInterval(updateTimers, 60000); // refresh every minute
    },

    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
    },

    initSidebarToggle() {
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.querySelector('.app-sidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
            });
        }
    }
};

document.addEventListener('DOMContentLoaded', () => App.init());
