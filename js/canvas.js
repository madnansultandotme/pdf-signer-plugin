// JavaScript for handling canvas drawing
let isDrawing = false;
let canvas, ctx;

function initCanvas() {
    canvas = document.getElementById('signatureCanvas');
    ctx = canvas.getContext('2d');
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
}

function startDrawing(event) {
    isDrawing = true;
    ctx.beginPath();
    ctx.moveTo(event.offsetX, event.offsetY);
}

function draw(event) {
    if (isDrawing) {
        ctx.lineTo(event.offsetX, event.offsetY);
        ctx.stroke();
    }
}

function stopDrawing() {
    isDrawing = false;
    ctx.closePath();
    enableGenerateButton();
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

    // Enable button only if either signatureData or signatureUpload is available
    generateButton.disabled = !(signatureData || signatureUpload);
}

document.addEventListener('DOMContentLoaded', initCanvas);
