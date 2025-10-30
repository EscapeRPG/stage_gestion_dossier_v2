const button = document.getElementById('theme-button'),
    btnTxt = document.getElementById('btnTxt'),
    body = document.body;

let theme = localStorage.getItem("theme");

button.addEventListener('click', changeTheme);

document.addEventListener("DOMContentLoaded", () => {
    body.classList.add('no-transition');

    if (theme === "dark") {
        body.classList.add("dark-mode");

        btnTxt.innerText = "☼";
    }

    void body.offsetWidth;

    body.classList.remove('no-transition');
});

function changeTheme() {
    if (body.className === "dark-mode") {
        body.classList.remove("dark-mode");
        localStorage.setItem("theme", "light");
        btnTxt.innerText = "☽";
    } else {
        body.classList.add("dark-mode");
        localStorage.setItem("theme", "dark");
        btnTxt.innerText = "☼";
    }
}
