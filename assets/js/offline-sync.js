/**
 * IndexedDB Offline Draft Storage & Background Sync Manager
 */

const OfflineManager = {
    db: null,
    dbName: 'AMCFieldServiceDB',

    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, 1);
            request.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains('job_drafts')) {
                    db.createObjectStore('job_drafts', { keyPath: 'call_id' });
                }
            };
            request.onsuccess = (e) => {
                this.db = e.target.result;
                resolve(this.db);
            };
            request.onerror = (e) => reject(e);
        });
    },

    async saveDraft(callId, data) {
        if (!this.db) await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('job_drafts', 'readwrite');
            const store = tx.objectStore('job_drafts');
            store.put({ call_id: parseInt(callId), data: data, saved_at: new Date().toISOString() });
            tx.oncomplete = () => resolve(true);
            tx.onerror = (e) => reject(e);
        });
    },

    async getDraft(callId) {
        if (!this.db) await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('job_drafts', 'readonly');
            const store = tx.objectStore('job_drafts');
            const req = store.get(parseInt(callId));
            req.onsuccess = () => resolve(req.result ? req.result.data : null);
            req.onerror = (e) => reject(e);
        });
    },

    async clearDraft(callId) {
        if (!this.db) await this.init();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('job_drafts', 'readwrite');
            const store = tx.objectStore('job_drafts');
            store.delete(parseInt(callId));
            tx.oncomplete = () => resolve(true);
            tx.onerror = (e) => reject(e);
        });
    }
};

document.addEventListener('DOMContentLoaded', () => OfflineManager.init());
