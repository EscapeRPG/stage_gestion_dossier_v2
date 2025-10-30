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
    if (histoBtn.textContent === "-") {
        histoBtn.textContent = "+";
        histoPanel.style.maxHeight = '0px';
        histoPanel.parentNode.querySelector('h2').className = 'closed';
        histoPanel.classList.remove("show");
    } else {
        histoBtn.textContent = "-";
        histoPanel.style.maxHeight = histoPanel.scrollHeight + 'px';
        histoPanel.parentNode.querySelector('h2').className = '';
        histoPanel.classList.add("show");
    }
}

function showTraitement() {
    if (traitementBtn.textContent === "-") {
        traitementBtn.textContent = "+";
        traitementPanel.style.maxHeight = '0px';
        traitementPanel.parentNode.querySelector('h2').className = 'closed';
        traitementPanel.classList.remove("show");
    } else {
        traitementBtn.textContent = "-";
        traitementPanel.style.maxHeight = traitementPanel.scrollHeight + 'px';
        traitementPanel.parentNode.querySelector('h2').className = '';
        traitementPanel.classList.add("show");
    }
}

function showEtat() {
    if (etatBtn.textContent === "-") {
        etatBtn.textContent = "+";
        etatPanel.style.maxHeight = '0px';
        etatPanel.parentNode.querySelector('h2').className = 'closed';
        etatPanel.classList.remove("show");
    } else {
        etatBtn.textContent = "-";
        etatPanel.style.maxHeight = etatPanel.scrollHeight + 'px';
        etatPanel.classList.add("show");
        etatPanel.parentNode.querySelector('h2').className = '';
        setTimeout(() => traitementPanel.style.maxHeight = traitementPanel.scrollHeight + 'px', 501);
    }
}

function showAFaire() {
    if (aFaireBtn.textContent === "-") {
        aFaireBtn.textContent = "+";
        aFairePanel.style.maxHeight = '0px';
        aFairePanel.parentNode.querySelector('h2').className = 'closed';
        aFairePanel.classList.remove("show");
    } else {
        aFaireBtn.textContent = "-";
        aFairePanel.style.maxHeight = aFairePanel.scrollHeight + 'px';
        aFairePanel.classList.add("show");
        aFairePanel.parentNode.querySelector('h2').className = '';
        setTimeout(() => traitementPanel.style.maxHeight = traitementPanel.scrollHeight + 'px', 501);
    }
}

function showComment() {
    if (commentBtn.textContent === "-") {
        commentBtn.textContent = "+";
        commentPanel.style.maxHeight = '0px';
        commentPanel.parentNode.querySelector('h2').className = 'closed';
        commentPanel.classList.remove("show");
    } else {
        commentBtn.textContent = "-";
        commentPanel.style.maxHeight = commentPanel.scrollHeight + 'px';
        commentPanel.parentNode.querySelector('h2').className = '';
        commentPanel.classList.add("show");
    }
}
