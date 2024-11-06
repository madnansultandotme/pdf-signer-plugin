let isDrawing = false;
let canvas, ctx;

function initCanvas() {
    canvas = document.getElementById('signatureCanvas');
    ctx = canvas.getContext('2d');
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;

    // Mouse events for non-touch devices
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);

    // Touch events for touch-enabled devices
    canvas.addEventListener('touchstart', startDrawingTouch);
    canvas.addEventListener('touchmove', drawTouch);
    canvas.addEventListener('touchend', stopDrawingTouch);
    canvas.addEventListener('touchcancel', stopDrawingTouch);
}

function startDrawing(event) {
    isDrawing = true;
    const { offsetX, offsetY } = event;
    ctx.beginPath();
    ctx.moveTo(offsetX, offsetY);
    event.preventDefault(); // Prevent scrolling or other default actions on touch
}

function draw(event) {
    if (isDrawing) {
        const { offsetX, offsetY } = event;
        ctx.lineTo(offsetX, offsetY);
        ctx.stroke();
    }
}

function stopDrawing() {
    isDrawing = false;
    ctx.closePath();
    enableGenerateButton();
}

function startDrawingTouch(event) {
    isDrawing = true;
    const { clientX, clientY } = event.touches[0]; // Get the first touch point
    const rect = canvas.getBoundingClientRect();
    const offsetX = clientX - rect.left;
    const offsetY = clientY - rect.top;

    ctx.beginPath();
    ctx.moveTo(offsetX, offsetY);
    event.preventDefault(); // Prevent default touch actions
}

function drawTouch(event) {
    if (isDrawing) {
        const { clientX, clientY } = event.touches[0]; // Get the first touch point
        const rect = canvas.getBoundingClientRect();
        const offsetX = clientX - rect.left;
        const offsetY = clientY - rect.top;

        ctx.lineTo(offsetX, offsetY);
        ctx.stroke();
    }
}

function stopDrawingTouch(event) {
    isDrawing = false;
    ctx.closePath();
    enableGenerateButton();
    event.preventDefault();
}

function clearCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function saveSignature() {
    const dataURL = canvas.toDataURL('image/png');
    document.getElementById('signatureData').value = dataURL;
    enableGenerateButton();
}

function enableGenerateButton() {
    const signatureData = document.getElementById('signatureData').value;
    const signatureUpload = document.getElementById('signatureUpload').files.length > 0;
    const generateButton = document.getElementById('generateContractButton');
    generateButton.disabled = !(signatureData || signatureUpload);
}

document.addEventListener('DOMContentLoaded', initCanvas);
