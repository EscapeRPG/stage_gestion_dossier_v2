document.getElementById('agences-list').addEventListener('change', function () {
    const codeAgence = this.value,
        salarieReaffect = document.getElementById('reaffectation'),
        techRDV = document.getElementById('tech-rdv');

    salarieReaffect.innerHTML = '<option value="">Chargement...</option>';
    techRDV.innerHTML = '<option value="">Chargement...</option>';

    fetch(`/ajax/salaries/${codeAgence}`)
        .then(response => response.json())
        .then(data => {
            salarieReaffect.innerHTML = '<option value="" selected="selected">-----</option>';
            techRDV.innerHTML = '<option value="" selected="selected">-----</option>';

            data.forEach(code => {
                const opt1 = document.createElement('option'),
                opt2 = document.createElement('option');

                opt1.value = code;
                opt1.textContent = code;
                salarieReaffect.appendChild(opt1);

                opt2.value = code;
                opt2.textContent = code;
                techRDV.appendChild(opt2);
            });
        })
        .catch(err => {
            console.error(err);
            salarieReaffect.innerHTML = '<option value="">Erreur</option>';
            techRDV.innerHTML = '<option value="">Erreur</option>';
        });
});
