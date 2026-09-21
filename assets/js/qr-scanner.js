/**
 * HTML5 Camera QR Code Scanner Helper & Token Parser
 */

class MachineQRScanner {
    constructor(videoElementId, onScanSuccess) {
        this.video = document.getElementById(videoElementId);
        this.onScanSuccess = onScanSuccess;
        this.stream = null;
        this.scanning = false;
        this.animFrameId = null;
    }

    /**
     * Helper to extract clean asset token / code from scanned string or full URL
     */
    static extractToken(raw) {
        if (!raw) return '';
        let str = String(raw).trim();

        // If URL encoded, decode it
        try {
            str = decodeURIComponent(str);
        } catch(e) {}

        // Match /machines/qr/{token} inside URL
        const qrMatch = str.match(/machines\/qr\/([^/?#\s]+)/i);
        if (qrMatch) {
            return qrMatch[1].trim();
        }

        // If full URL, take last path segment
        if (str.startsWith('http://') || str.startsWith('https://')) {
            try {
                const u = new URL(str);
                const parts = u.pathname.split('/').filter(Boolean);
                if (parts.length > 0) {
                    return parts[parts.length - 1];
                }
            } catch(e) {}
        }

        return str;
    }

    async start() {
        this.stop();

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            console.warn('Camera access is not supported or not secure context (HTTPS required on mobile).');
            const errEl = document.getElementById('qrCameraNotice');
            if (errEl) {
                errEl.innerHTML = '<div class="alert alert-warning p-2 small mb-0"><i class="bi bi-camera-video-off me-1"></i>Camera requires HTTPS on remote mobile devices. You can type the Asset Code / S/N below.</div>';
            }
            return;
        }

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });

            if (!this.video) return;

            this.video.srcObject = this.stream;
            this.video.setAttribute('playsinline', true);
            await this.video.play();
            this.scanning = true;

            const errEl = document.getElementById('qrCameraNotice');
            if (errEl) errEl.innerHTML = '';

            this.scanFrame();
        } catch (err) {
            console.error('Camera access error:', err);
            const errEl = document.getElementById('qrCameraNotice');
            if (errEl) {
                errEl.innerHTML = `<div class="alert alert-warning p-2 small mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Camera permission not granted or unavailable. Use manual input below.</div>`;
            }
        }
    }

    scanFrame() {
        if (!this.scanning || !this.video) return;

        if (this.video.readyState === this.video.HAVE_ENOUGH_DATA) {
            // Check native BarcodeDetector API (Chrome, Edge, Android Chrome)
            if ('BarcodeDetector' in window) {
                const barcodeDetector = new BarcodeDetector({ 
                    formats: ['qr_code', 'code_128', 'code_39', 'ean_13', 'data_matrix'] 
                });
                barcodeDetector.detect(this.video)
                    .then(barcodes => {
                        if (barcodes.length > 0 && this.scanning) {
                            const raw = barcodes[0].rawValue;
                            const clean = MachineQRScanner.extractToken(raw);
                            this.stop();
                            if (this.onScanSuccess) {
                                this.onScanSuccess(clean, raw);
                            }
                        } else if (this.scanning) {
                            this.animFrameId = requestAnimationFrame(() => this.scanFrame());
                        }
                    })
                    .catch(() => {
                        if (this.scanning) {
                            this.animFrameId = requestAnimationFrame(() => this.scanFrame());
                        }
                    });
                return;
            }
        }

        if (this.scanning) {
            this.animFrameId = requestAnimationFrame(() => this.scanFrame());
        }
    }

    stop() {
        this.scanning = false;
        if (this.animFrameId) {
            cancelAnimationFrame(this.animFrameId);
            this.animFrameId = null;
        }
        if (this.stream) {
            this.stream.getTracks().forEach(track => {
                try { track.stop(); } catch(e) {}
            });
            this.stream = null;
        }
        if (this.video) {
            this.video.srcObject = null;
        }
    }
}
