/**
 * Call Center High-Speed AJAX Universal Search
 * Shortcut: Ctrl+K or Cmd+K
 */

const FastSearch = {
    searchModal: null,
    searchInput: null,
    resultsContainer: null,
    debounceTimer: null,

    init() {
        this.searchInput = document.getElementById('fast-search-input');
        this.resultsContainer = document.getElementById('fast-search-results');
        const modalEl = document.getElementById('fastSearchModal');

        if (modalEl) {
            this.searchModal = new bootstrap.Modal(modalEl);
        }

        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                clearTimeout(this.debounceTimer);
                const query = e.target.value.trim();
                if (query.length < 2) {
                    this.resultsContainer.innerHTML = '<div class="text-center text-muted p-4"><i class="bi bi-search fs-3 mb-2 d-block"></i>Type at least 2 characters to search across Customers, Serials, Phones, Asset Tags, and Contracts...</div>';
                    return;
                }
                this.debounceTimer = setTimeout(() => this.performSearch(query), 200);
            });
        }

        // Global Keyboard Shortcut: Ctrl+K or Cmd+K
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                if (this.searchModal) {
                    this.searchModal.show();
                    setTimeout(() => this.searchInput?.focus(), 300);
                }
            }
        });
    },

    async performSearch(query) {
        this.resultsContainer.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary spinner-border-sm me-2"></div>Searching database...</div>';
        
        const res = await App.fetch(`ajax/search?q=${encodeURIComponent(query)}`);
        if (!res.success || !res.data || (res.data.machines.length === 0 && res.data.customers.length === 0 && res.data.calls.length === 0)) {
            this.resultsContainer.innerHTML = '<div class="text-center text-muted p-4"><i class="bi bi-emoji-neutral fs-3 mb-2 d-block"></i>No matching records found for "<strong>' + query + '</strong>"</div>';
            return;
        }

        let html = '';

        // 1. Matched Machines / Assets
        if (res.data.machines && res.data.machines.length > 0) {
            html += `<div class="px-3 py-2 bg-light fw-bold text-uppercase fs-7 text-muted border-bottom"><i class="bi bi-pc-display me-1"></i> Machines / Assets (${res.data.machines.length})</div>`;
            res.data.machines.forEach(m => {
                const amcBadge = m.status === 'UNDER_AMC' 
                    ? '<span class="badge bg-success-subtle text-success border border-success-subtle">AMC Active</span>' 
                    : '<span class="badge bg-secondary-subtle text-secondary">Non-AMC</span>';

                html += `
                    <div class="search-result-item p-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-primary">${m.make} ${m.model} <span class="badge bg-dark text-white ms-1">${m.asset_code}</span> ${amcBadge}</div>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-hash"></i> S/N: <strong>${m.serial_number}</strong> | <i class="bi bi-building"></i> ${m.company_name} (${m.location_name})
                            </div>
                            <div class="small text-muted">
                                <i class="bi bi-geo-alt"></i> ${m.department || 'General'} | User: ${m.assigned_employee || 'Unassigned'}
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="${App.baseUrl}/calls/create?machine_id=${m.id}&customer_id=${m.customer_id}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Create Call
                            </a>
                            <a href="${App.baseUrl}/machines/${m.id}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                `;
            });
        }

        // 2. Matched Customers
        if (res.data.customers && res.data.customers.length > 0) {
            html += `<div class="px-3 py-2 bg-light fw-bold text-uppercase fs-7 text-muted border-bottom mt-2"><i class="bi bi-buildings me-1"></i> Customers (${res.data.customers.length})</div>`;
            res.data.customers.forEach(c => {
                html += `
                    <div class="search-result-item p-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">${c.company_name} <span class="badge bg-info-subtle text-info">${c.customer_code}</span></div>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-person"></i> ${c.contact_person} | <i class="bi bi-telephone"></i> <a href="tel:${c.mobile}">${c.mobile}</a> | <i class="bi bi-whatsapp text-success"></i> <a href="https://wa.me/91${c.mobile}" target="_blank">Chat</a>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="${App.baseUrl}/calls/create?customer_id=${c.id}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Create Call
                            </a>
                            <a href="${App.baseUrl}/customers/${c.id}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-folder2-open"></i> Profile
                            </a>
                        </div>
                    </div>
                `;
            });
        }

        // 3. Matched Active Calls
        if (res.data.calls && res.data.calls.length > 0) {
            html += `<div class="px-3 py-2 bg-light fw-bold text-uppercase fs-7 text-muted border-bottom mt-2"><i class="bi bi-headset me-1"></i> Active Calls (${res.data.calls.length})</div>`;
            res.data.calls.forEach(cl => {
                html += `
                    <div class="search-result-item p-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">${cl.call_number} <span class="badge bg-warning-subtle text-warning">${cl.status}</span></div>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-building"></i> ${cl.company_name} | Issue: ${cl.reported_issue.substring(0, 50)}...
                            </div>
                        </div>
                        <a href="${App.baseUrl}/calls/${cl.id}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-right"></i> View Ticket
                        </a>
                    </div>
                `;
            });
        }

        this.resultsContainer.innerHTML = html;
    }
};

document.addEventListener('DOMContentLoaded', () => FastSearch.init());
