/**
 * HTML5 Electronic Canvas Signature Pad
 */

class ElectronicSignaturePad {
    constructor(canvasId, clearBtnId, hiddenInputId) {
        this.canvas = document.getElementById(canvasId);
        this.clearBtn = document.getElementById(clearBtnId);
        this.hiddenInput = document.getElementById(hiddenInputId);
        if (!this.canvas) return;

        this.ctx = this.canvas.getContext('2d');
        this.isDrawing = false;
        this.hasSignature = false;

        this.resizeCanvas();
        this.initEvents();
    }

    resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        this.canvas.width = this.canvas.offsetWidth * ratio;
        this.canvas.height = this.canvas.offsetHeight * ratio;
        this.ctx.scale(ratio, ratio);
        this.ctx.lineWidth = 2.5;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
        this.ctx.strokeStyle = '#0f172a';
    }

    getCoordinates(event) {
        const rect = this.canvas.getBoundingClientRect();
        const clientX = event.touches ? event.touches[0].clientX : event.clientX;
        const clientY = event.touches ? event.touches[0].clientY : event.clientY;
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    startDrawing(event) {
        event.preventDefault();
        this.isDrawing = true;
        const { x, y } = this.getCoordinates(event);
        this.ctx.beginPath();
        this.ctx.moveTo(x, y);
    }

    draw(event) {
        if (!this.isDrawing) return;
        event.preventDefault();
        const { x, y } = this.getCoordinates(event);
        this.ctx.lineTo(x, y);
        this.ctx.stroke();
        this.hasSignature = true;
        this.updateInput();
    }

    stopDrawing() {
        this.isDrawing = false;
    }

    clear() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.hasSignature = false;
        if (this.hiddenInput) {
            this.hiddenInput.value = '';
        }
    }

    updateInput() {
        if (this.hiddenInput && this.hasSignature) {
            this.hiddenInput.value = this.canvas.toDataURL('image/png');
        }
    }

    initEvents() {
        // Mouse Events
        this.canvas.addEventListener('mousedown', (e) => this.startDrawing(e));
        this.canvas.addEventListener('mousemove', (e) => this.draw(e));
        window.addEventListener('mouseup', () => this.stopDrawing());

        // Touch Events
        this.canvas.addEventListener('touchstart', (e) => this.startDrawing(e), { passive: false });
        this.canvas.addEventListener('touchmove', (e) => this.draw(e), { passive: false });
        window.addEventListener('touchend', () => this.stopDrawing());

        // Clear Button
        if (this.clearBtn) {
            this.clearBtn.addEventListener('click', () => this.clear());
        }
    }
}
