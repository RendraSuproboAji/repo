// Perilaku drawer menu; markup-nya dirender server di partials/drawer.blade.php.

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
