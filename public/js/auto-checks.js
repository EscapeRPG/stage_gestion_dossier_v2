const dateRDV = document.getElementById('dateRDV');
const timeRDV = document.getElementById('timeRDV');
const techRDV = document.getElementById('tech-rdv');
const validRDV = document.getElementById('validRDV');
const checkG3 = document.getElementById('checkG3');

function verifyFields() {
    const allFilled =
        dateRDV.value.trim() !== '' &&
        timeRDV.value.trim() !== '' &&
        techRDV.value.trim() !== '' &&
        validRDV.checked;

    checkG3.checked = allFilled;
}

[dateRDV, timeRDV, techRDV, validRDV].forEach(el => {
    el.addEventListener('input', verifyFields);
    el.addEventListener('change', verifyFields);
});
