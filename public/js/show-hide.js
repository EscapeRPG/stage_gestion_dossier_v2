const histoBtn = document.getElementById("histo-btn"),
    traitementBtn = document.getElementById("traitement-btn"),
    traitementPanel = document.getElementById("traitement"),
    etatBtn = document.getElementById("etat-btn"),
    etatPanel = document.getElementById("etat"),
    aFaireBtn = document.getElementById("a-faire-btn"),
    aFairePanel = document.getElementById("a-faire"),
    commentBtn = document.getElementById("comment-btn"),
    histoPanel = document.getElementById("histo"),
    commentPanel = document.getElementById("commentaire");

histoBtn.addEventListener('click', showHisto);
traitementBtn.addEventListener('click', showTraitement);
etatBtn.addEventListener('click', showEtat);
aFaireBtn.addEventListener('click', showAFaire);
commentBtn.addEventListener('click', showComment);

window.onload = () => showPanels();

window.onresize = () => showPanels();

function showPanels() {
    document.querySelectorAll(".panel").forEach((panel) => {
        panel.style.maxHeight = '0px';
    });

    document.querySelectorAll(".show").forEach((panel) => {
        panel.style.transition = 'none';
        panel.style.maxHeight = panel.scrollHeight + 'px';
        panel.offsetHeight;
        panel.style.transition = '';
    });

    if (traitementPanel.style.maxHeight !== '0px') {
        traitementPanel.style.transition = '';
        traitementPanel.style.maxHeight = traitementPanel.scrollHeight + 'px';
    }
}

function showHisto() {
    if (histoPanel.style.maxHeight !== '0px') {
        histoPanel.style.maxHeight = '0px';
        histoPanel.parentNode.querySelector('h2').classList.add('closed');
        histoPanel.classList.remove("show");
    } else {
        histoPanel.style.maxHeight = histoPanel.scrollHeight + 'px';
        histoPanel.parentNode.querySelector('h2').classList.remove('closed');
        histoPanel.classList.add("show");
    }
}

function showTraitement() {
    if (traitementPanel.style.maxHeight !== '0px') {
        traitementPanel.style.maxHeight = '0px';
        traitementPanel.parentNode.querySelector('h2').classList.add('closed');
        traitementPanel.classList.remove("show");
    } else {
        traitementPanel.style.maxHeight = traitementPanel.scrollHeight + 'px';
        traitementPanel.parentNode.querySelector('h2').classList.remove('closed');
        traitementPanel.classList.add("show");
    }
}

function showEtat() {
    if (etatPanel.style.maxHeight !== '0px') {
        etatPanel.style.maxHeight = '0px';
        etatPanel.parentNode.querySelector('h2').classList.add('closed');
        etatPanel.classList.remove("show");
    } else {
        etatPanel.style.maxHeight = etatPanel.scrollHeight + 'px';
        etatPanel.classList.add("show");
        etatPanel.parentNode.querySelector('h2').classList.remove('closed');
        setTimeout(() => traitementPanel.style.maxHeight = traitementPanel.scrollHeight + 'px', 501);
    }
}

function showAFaire() {
    if (aFairePanel.style.maxHeight !== '0px') {
        aFairePanel.style.maxHeight = '0px';
        aFairePanel.parentNode.querySelector('h2').classList.add('closed');
        aFairePanel.classList.remove("show");
    } else {
        aFairePanel.style.maxHeight = aFairePanel.scrollHeight + 'px';
        aFairePanel.classList.add("show");
        aFairePanel.parentNode.querySelector('h2').classList.remove('closed');
        setTimeout(() => traitementPanel.style.maxHeight = traitementPanel.scrollHeight + 'px', 501);
    }
}

function showComment() {
    if (commentPanel.style.maxHeight !== '0px') {
        commentPanel.style.maxHeight = '0px';
        commentPanel.parentNode.querySelector('h2').classList.add('closed');
        commentPanel.classList.remove("show");
    } else {
        commentPanel.style.maxHeight = commentPanel.scrollHeight + 'px';
        commentPanel.parentNode.querySelector('h2').classList.remove('closed');
        commentPanel.classList.add("show");
    }
}
