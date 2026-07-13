const root = document.documentElement;
const toggle = document.querySelector('[data-theme-toggle]');
const stored = window.localStorage.getItem('workspace-theme');
const initial = stored === 'light' || stored === 'dark'
    ? stored
    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

const applyTheme = (theme) => {
    root.dataset.theme = theme;
    if (toggle) {
        const dark = theme === 'dark';
        toggle.setAttribute('aria-pressed', String(dark));
        toggle.textContent = dark ? 'Use light theme' : 'Use dark theme';
    }
};

applyTheme(initial);
toggle?.addEventListener('click', () => {
    const theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    window.localStorage.setItem('workspace-theme', theme);
    applyTheme(theme);
});

document.querySelector('.validation-summary')?.focus();

const workspaceNavigation = document.querySelector('.workspace-nav');
const revealNavigationItem = (item) => item?.scrollIntoView({ block: 'nearest', inline: 'nearest' });

workspaceNavigation?.addEventListener('focusin', (event) => revealNavigationItem(event.target.closest('a')));
revealNavigationItem(workspaceNavigation?.querySelector('.is-current'));

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');
    if (form && !window.confirm(form.dataset.confirm)) event.preventDefault();
});
