const gallery = document.getElementById('gallery');

const escapeHtml = (s) => String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');

const cardHtml = (scene) => {
    // scene dengan paket viewer HTML hasil ekspor SuperSplat dibuka langsung,
    // selain itu gunakan viewer bawaan
    const href = scene.viewerUrl ?? `viewer.html?scene=${encodeURIComponent(scene.id)}`;

    const thumb = scene.thumbnail ?
        `<img src="${escapeHtml(scene.thumbnail)}" alt="${escapeHtml(scene.title)}" loading="lazy">` :
        `<div class="placeholder">${escapeHtml((scene.title || '?').charAt(0).toUpperCase())}</div>`;

    return `
        <a class="card" href="${escapeHtml(href)}">
            <div class="thumb">${thumb}</div>
            <div class="info">
                <h2>${escapeHtml(scene.title || scene.id)}</h2>
                ${scene.author ? `<div class="author">${escapeHtml(scene.author)}</div>` : ''}
                ${scene.description ? `<div class="desc">${escapeHtml(scene.description)}</div>` : ''}
            </div>
        </a>`;
};

const render = (scenes, query, sort) => {
    let list = [...scenes];

    if (query) {
        const q = query.toLowerCase();
        list = list.filter(s => [s.title, s.author, s.description]
            .some(v => v && v.toLowerCase().includes(q)));
    }

    // tanpa tanggal di index.json: "terbaru" = urutan daftar dibalik
    // (entri baru ditambahkan di akhir file)
    if (sort === 'newest') list.reverse();
    if (sort === 'title') list.sort((a, b) => (a.title ?? a.id).localeCompare(b.title ?? b.id));

    gallery.innerHTML = list.length ?
        list.map(cardHtml).join('') :
        `<div class="status">Tidak ada scene yang cocok.</div>`;
};

const main = async () => {
    let index;
    try {
        const res = await fetch('scenes/index.json');
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        index = await res.json();
    } catch (err) {
        gallery.innerHTML = `<div class="status">Gagal memuat scenes/index.json (${escapeHtml(err.message)}).<br>
            Jalankan lewat web server, misalnya: <code>python3 -m http.server</code></div>`;
        return;
    }

    if (index.title) {
        document.title = index.title;
        const parts = index.title.split(' ');
        document.getElementById('site-title').innerHTML = parts.length > 1 ?
            `${escapeHtml(parts.slice(0, -1).join(' '))} <span class="accent">${escapeHtml(parts.at(-1))}</span>` :
            escapeHtml(index.title);
    }

    const scenes = index.scenes ?? [];
    if (scenes.length === 0) {
        gallery.innerHTML = `<div class="status">Belum ada scene. Tambahkan project Anda ke <code>scenes/index.json</code>.</div>`;
        return;
    }

    const search = document.getElementById('search');
    const sort = document.getElementById('sort');
    const refresh = () => render(scenes, search.value.trim(), sort.value);
    search.addEventListener('input', refresh);
    sort.addEventListener('change', refresh);
    refresh();
};

main();
