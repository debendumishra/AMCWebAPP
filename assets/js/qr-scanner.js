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

        // Iteratively decode if URL encoded
        try {
            while (str.includes('%2F') || str.includes('%3A') || str.includes('%20')) {
                const decoded = decodeURIComponent(str);
                if (decoded === str) break;
                str = decoded;
            }
        } catch(e) {}

        // Match all /machines/qr/{token} occurrences and pick the last one that isn't 'http:'
        const matches = [...str.matchAll(/machines\/qr\/([^/?#\s]+)/gi)];
        if (matches && matches.length > 0) {
            for (let i = matches.length - 1; i >= 0; i--) {
                const candidate = matches[i][1].trim();
                if (candidate && !['http:', 'https:'].includes(candidate.toLowerCase())) {
                    return candidate;
                }
            }
        }

        // If it's a URL or path with slashes, take the last non-empty segment
        const segments = str.replace(/\\/g, '/').split('/').map(s => s.trim()).filter(s => {
            return s !== '' && !['http:', 'https:', 'localhost', 'machines', 'qr'].includes(s.toLowerCase());
        });

        if (segments.length > 0) {
            return segments[segments.length - 1];
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
