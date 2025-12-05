(function () {
    var body = document.body;
    var themeToggle = document.querySelector("[data-theme-toggle]");
    var langButtons = document.querySelectorAll("[data-lang-btn]");
    var navToggle = document.querySelector("[data-mobile-toggle]");
    var nav = document.querySelector("[data-nav]");

    var storedTheme = window.localStorage.getItem("pmac-theme");
    if (storedTheme) {
        body.setAttribute("data-theme", storedTheme);
    }

    var storedLang = window.localStorage.getItem("pmac-lang");
    if (storedLang) {
        body.setAttribute("data-lang", storedLang);
        langButtons.forEach(function (btn) {
            btn.classList.toggle("is-active", btn.getAttribute("data-lang-btn") === storedLang);
        });
    }

    if (themeToggle) {
        themeToggle.addEventListener("click", function () {
            var next = body.getAttribute("data-theme") === "light" ? "dark" : "light";
            body.setAttribute("data-theme", next);
            window.localStorage.setItem("pmac-theme", next);
        });
    }

    langButtons.forEach(function (btn) {
        btn.addEventListener("click", function () {
            var target = btn.getAttribute("data-lang-btn");
            body.setAttribute("data-lang", target);
            window.localStorage.setItem("pmac-lang", target);
            langButtons.forEach(function (inner) {
                inner.classList.toggle("is-active", inner === btn);
            });
        });
    });

    if (navToggle && nav) {
        navToggle.addEventListener("click", function () {
            nav.classList.toggle("is-open");
        });

        nav.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                nav.classList.remove("is-open");
            });
        });
    }
})();
