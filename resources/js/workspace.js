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

const workspaceNavigation = document.querySelector('[data-workspace-nav-scroll]');
const revealNavigationItem = (item) => item?.scrollIntoView({ block: 'nearest', inline: 'nearest' });

workspaceNavigation?.addEventListener('focusin', (event) => revealNavigationItem(event.target.closest('a')));
revealNavigationItem(workspaceNavigation?.querySelector('.is-current'));

const more = document.querySelector('[data-workspace-more]');
const moreTrigger = more?.querySelector('[data-workspace-more-trigger]');
const moreMenu = more?.querySelector('[data-workspace-more-menu]');
const overflowSlot = more?.querySelector('[data-tablet-overflow-slot]');
const tabletOverflow = window.matchMedia('(min-width: 621px) and (max-width: 1350px)');
const overflowLinks = [...document.querySelectorAll('[data-tablet-overflow]')];
const overflowHome = overflowLinks[0]?.parentElement;

const closeMore = (returnFocus = false) => {
    if (!moreTrigger || !moreMenu) return;
    moreTrigger.setAttribute('aria-expanded', 'false');
    moreMenu.hidden = true;
    if (returnFocus) moreTrigger.focus();
};

const toggleMore = () => {
    if (!moreTrigger || !moreMenu) return;
    const opening = moreTrigger.getAttribute('aria-expanded') !== 'true';
    moreTrigger.setAttribute('aria-expanded', String(opening));
    moreMenu.hidden = !opening;
    if (opening) (moreMenu.querySelector('.is-current') ?? moreMenu.querySelector('a'))?.focus();
};

const placeTabletOverflow = () => {
    if (!overflowHome || !overflowSlot) return;
    closeMore();
    if (tabletOverflow.matches) {
        overflowLinks.forEach((link) => overflowSlot.append(link));
        more.classList.toggle('has-overflow-current', overflowLinks.some((link) => link.classList.contains('is-current')));
    } else {
        overflowLinks.forEach((link) => overflowHome.append(link));
        more.classList.remove('has-overflow-current');
    }
    revealNavigationItem(workspaceNavigation?.querySelector('.is-current'));
};

moreTrigger?.addEventListener('click', toggleMore);
document.addEventListener('click', (event) => {
    if (more && !more.contains(event.target)) closeMore();
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && moreTrigger?.getAttribute('aria-expanded') === 'true') closeMore(true);
});
tabletOverflow.addEventListener('change', placeTabletOverflow);
placeTabletOverflow();

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');
    if (form && !window.confirm(form.dataset.confirm)) event.preventDefault();
});

document.querySelectorAll('[data-rejection-rule]').forEach((form) => {
    const useRule = form.querySelector('[data-use-rule]');
    const fields = form.querySelector('[data-rule-fields]');
    const severity = form.querySelector('[data-rule-severity]');
    const preview = form.querySelector('[data-rule-preview]');
    const hardConfirm = form.querySelector('[data-hard-confirm]');
    const refresh = () => {
        fields.hidden = !useRule.checked;
        hardConfirm.hidden = severity.value !== 'hard_exclusion';
        preview.textContent = severity.value === 'hard_exclusion'
            ? 'Hard exclusion: matching future jobs in this scope will be excluded with a stored explanation.'
            : 'Matching future jobs will receive the selected preference penalty and a stored explanation.';
    };
    useRule.addEventListener('change', refresh);
    severity.addEventListener('change', refresh);
    refresh();
});
