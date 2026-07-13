const article = document.querySelector('[data-article-body]');
const progress = document.querySelector('[data-reading-progress]');
const tocLinks = [...document.querySelectorAll('[data-article-toc] a')];

if (article && progress) {
    let ticking = false;
    const update = () => {
        const rect = article.getBoundingClientRect();
        const start = window.scrollY + rect.top;
        const distance = Math.max(article.offsetHeight - window.innerHeight, 1);
        const value = Math.min(Math.max((window.scrollY - start) / distance, 0), 1);
        progress.style.transform = `scaleX(${value})`;
        ticking = false;
    };
    const requestUpdate = () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    };
    update();
    addEventListener('scroll', requestUpdate, { passive: true });
    addEventListener('resize', requestUpdate, { passive: true });
}

if (article && tocLinks.length) {
    const sections = tocLinks.map((link) => document.querySelector(link.hash)).filter(Boolean);
    const observer = new IntersectionObserver((entries) => {
        const visible = entries.filter((entry) => entry.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];
        if (!visible) return;
        tocLinks.forEach((link) => link.removeAttribute('aria-current'));
        document.querySelector(`[data-article-toc] a[href="#${visible.target.id}"]`)?.setAttribute('aria-current', 'location');
    }, { rootMargin: '-15% 0px -70% 0px' });
    sections.forEach((section) => observer.observe(section));
}
