document.getElementById('numIntForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const numInt = document.getElementById('dossier-list').value,
        id = document.getElementById('id').value;

    if (numInt) {
        window.location.href = `/ClientInfo?id=${id}&action=detail-dossier&numInt=${numInt}`;
    }
});
