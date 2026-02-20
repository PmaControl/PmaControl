(function () {
    function setTheme(theme) {
        document.body.setAttribute('data-theme', theme);
        localStorage.setItem('pmacontrol-theme', theme);
    }

    function setLang(lang) {
        document.body.setAttribute('data-lang', lang);
        localStorage.setItem('pmacontrol-lang', lang);
    }

    var savedTheme = localStorage.getItem('pmacontrol-theme');
    if (savedTheme) {
        setTheme(savedTheme);
    }

    var savedLang = localStorage.getItem('pmacontrol-lang');
    if (savedLang) {
        setLang(savedLang);
    }

    var themeButton = document.querySelector('[data-action="toggle-theme"]');
    if (themeButton) {
        themeButton.addEventListener('click', function () {
            var current = document.body.getAttribute('data-theme');
            setTheme(current === 'light' ? 'dark' : 'light');
        });
    }

    var langButton = document.querySelector('[data-action="toggle-lang"]');
    if (langButton) {
        langButton.addEventListener('click', function () {
            var current = document.body.getAttribute('data-lang');
            setLang(current === 'fr' ? 'en' : 'fr');
        });
    }
})();
