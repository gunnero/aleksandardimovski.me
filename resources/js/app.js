const navButton = document.querySelector('.nav-toggle');
const nav = document.querySelector('#site-nav');
const closeNav = () => {
    nav?.classList.remove('open');
    navButton?.setAttribute('aria-expanded', 'false');
};
navButton?.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    navButton.setAttribute('aria-expanded', String(open));
});
nav?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeNav));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && nav?.classList.contains('open')) {
        closeNav();
        navButton?.focus();
    }
});
const themeButton = document.querySelector('[data-theme-toggle]');
const updateThemeButton = () => {
    const dark = document.documentElement.dataset.theme === 'dark';
    themeButton?.setAttribute('aria-pressed', String(dark));
    themeButton?.setAttribute('aria-label', dark ? 'Use light theme' : 'Use dark theme');
};
updateThemeButton();
themeButton?.addEventListener('click', () => {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    localStorage.setItem('theme', next);
    updateThemeButton();
});
