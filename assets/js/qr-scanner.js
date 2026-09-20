/**
 * HTML5 Camera QR Code Scanner Helper
 */

class MachineQRScanner {
    constructor(videoElementId, onScanSuccess) {
        this.video = document.getElementById(videoElementId);
        this.onScanSuccess = onScanSuccess;
        this.stream = null;
        this.scanning = false;
    }

    async start() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Camera access is not supported on this browser or connection is not secure (HTTPS required in production).');
            return;
        }

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' }
            });
            this.video.srcObject = this.stream;
            this.video.setAttribute('playsinline', true);
            await this.video.play();
            this.scanning = true;
            this.scanFrame();
        } catch (err) {
            console.error('Camera access error:', err);
            alert('Could not start camera: ' + err.message);
        }
    }

    scanFrame() {
        if (!this.scanning) return;

        // Use native BarcodeDetector API if available (Supported in modern Chrome/Android/Edge)
        if ('BarcodeDetector' in window) {
            const barcodeDetector = new BarcodeDetector({ formats: ['qr_code', 'code_128'] });
            barcodeDetector.detect(this.video)
                .then(barcodes => {
                    if (barcodes.length > 0) {
                        const code = barcodes[0].rawValue;
                        this.stop();
                        if (this.onScanSuccess) {
                            this.onScanSuccess(code);
                        }
                    } else if (this.scanning) {
                        requestAnimationFrame(() => this.scanFrame());
                    }
                })
                .catch(() => {
                    if (this.scanning) requestAnimationFrame(() => this.scanFrame());
                });
        } else {
            // Fallback interval
            if (this.scanning) {
                setTimeout(() => this.scanFrame(), 500);
            }
        }
    }

    stop() {
        this.scanning = false;
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
    }
}
