// const amountInput = document.getElementById('amount');
// const summary = document.getElementById('summary');
// const finalAmount = document.getElementById('finalAmount');
// const boxes = document.querySelectorAll('.amount-box');
// const payBtn = document.getElementById('payBtn');

// function updateSummary(value) {
//     if (value && value > 0) {
//         summary.classList.remove('d-none');
//         finalAmount.innerText = Number(value).toLocaleString();
//         payBtn.disabled = false;
//     } else {
//         summary.classList.add('d-none');
//         payBtn.disabled = true;
//     }
// }

// boxes.forEach(box => {
//     box.addEventListener('click', () => {
//         boxes.forEach(b => b.classList.remove('active'));
//         box.classList.add('active');
//         amountInput.value = box.dataset.value;
//         updateSummary(box.dataset.value);
//     });
// });

// amountInput.addEventListener('input', () => {
//     boxes.forEach(b => b.classList.remove('active'));
//     updateSummary(amountInput.value);
// });


const amountInput = document.getElementById('amount');
const summary = document.getElementById('summary');
const finalAmount = document.getElementById('finalAmount');
const boxes = document.querySelectorAll('.amount-box');
const payBtn = document.getElementById('payBtn');

function updateSummary(value) {
    value = parseInt(value);
    if (value >= 10000) {
        summary.classList.remove('d-none');
        finalAmount.innerText = value.toLocaleString('fa-IR');
        payBtn.disabled = false;
    } else {
        summary.classList.add('d-none');
        payBtn.disabled = true;
    }
}

boxes.forEach(box => {
    box.addEventListener('click', () => {
        boxes.forEach(b => b.classList.remove('bg-success', 'text-white'));
        box.classList.add('bg-success', 'text-white');
        amountInput.value = box.dataset.value;
        updateSummary(box.dataset.value);
    });
});

amountInput.addEventListener('input', () => {
    boxes.forEach(b => b.classList.remove('bg-success', 'text-white'));
    updateSummary(amountInput.value);
});

// اگر مقدار از قبل بود (مثلاً از redirect برگشتی)
if (amountInput.value) {
    updateSummary(amountInput.value);
}