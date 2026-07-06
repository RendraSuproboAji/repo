// Drawer menu ala superspl.at. Diinject ke halaman; semua elemen dengan
// [data-menu-open] akan membuka drawer.

const GITHUB_URL = 'https://github.com/RendraSuproboAji/repo';

const icon = (d) => `<svg viewBox="0 0 24 24" width="20" height="20" fill="none"
    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">${d}</svg>`;

const ICONS = {
    explore: icon('<circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>'),
    editor: icon('<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/>'),
    convert: icon('<polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/><line x1="4" y1="4" x2="9" y2="9"/>'),
    github: icon('<path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>'),
    close: icon('<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>')
};

export const MENU_ITEMS = {
    main: [
        { label: 'Explore', href: 'index.html', icon: ICONS.explore },
        { label: 'Editor', href: 'https://superspl.at/editor', icon: ICONS.editor, external: true },
        { label: 'Convert', href: 'https://superspl.at/convert', icon: ICONS.convert, external: true }
    ],
    footer: [
        { label: 'GitHub', href: GITHUB_URL, icon: ICONS.github, external: true }
    ]
};

const itemHtml = (item) => `
    <a class="drawer-item" href="${item.href}"${item.external ? ' target="_blank" rel="noopener"' : ''}>
        ${item.icon}<span>${item.label}</span>${item.external ? '<span class="ext">↗</span>' : ''}
    </a>`;

const initMenu = () => {
    const root = document.createElement('div');
    root.innerHTML = `
        <div class="drawer-backdrop" hidden></div>
        <aside class="drawer" hidden aria-label="Menu">
            <div class="drawer-header">
                <span class="brand-logo" aria-hidden="true"></span>
                <span class="drawer-title">Splat Gallery</span>
                <button class="icon-btn drawer-close" aria-label="Tutup menu">${ICONS.close}</button>
            </div>
            <nav class="drawer-nav">${MENU_ITEMS.main.map(itemHtml).join('')}</nav>
            <div class="drawer-footer">${MENU_ITEMS.footer.map(itemHtml).join('')}</div>
        </aside>`;
    document.body.append(...root.children);

    const backdrop = document.querySelector('.drawer-backdrop');
    const drawer = document.querySelector('.drawer');

    const setOpen = (open) => {
        backdrop.hidden = drawer.hidden = !open;
        requestAnimationFrame(() => {
            backdrop.classList.toggle('open', open);
            drawer.classList.toggle('open', open);
        });
    };

    document.querySelectorAll('[data-menu-open]').forEach(el =>
        el.addEventListener('click', () => setOpen(true)));
    backdrop.addEventListener('click', () => setOpen(false));
    drawer.querySelector('.drawer-close').addEventListener('click', () => setOpen(false));
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setOpen(false);
    });
};

initMenu();
