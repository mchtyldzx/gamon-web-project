/* app.js — home page: meta + health + stats */

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = String(str ?? '');
  return d.innerHTML;
}

const CAT_ICONS = {
  household: '🏠', paper: '📄', plastic: '♻️',
  glass: '🍶', metal: '🔩', organic: '🌱', hazardous: '⚠️',
};

async function loadMeta() {
  try {
    const res  = await fetch('api/meta.php');
    const data = await res.json();

    const catList = document.getElementById('category-list');
    if (catList) {
      if (!data.categories?.length) {
        catList.innerHTML = '<li>Run: <code>php scripts/init-database.php</code></li>';
      } else {
        catList.innerHTML = data.categories.map(c =>
          `<li><span class="cat-icon">${CAT_ICONS[c.code] || '🗑️'}</span> ${escHtml(c.name)}</li>`
        ).join('');
      }
    }

    const hoodList = document.getElementById('city-list');
    if (hoodList) {
      if (!data.cities?.length) {
        hoodList.innerHTML = '<li>No cities found.</li>';
      } else {
        hoodList.innerHTML = data.cities.map(n =>
          `<li>📍 ${escHtml(n.locality)}</li>`
        ).join('');
      }
    }
  } catch (e) {
    const el = document.getElementById('category-list');
    if (el) el.innerHTML = `<li class="alert alert-error visible" style="list-style:none">Failed to load: ${escHtml(e.message)}</li>`;
  }
}



async function loadStats() {
  try {
    const res     = await fetch('api/reports.php');
    const reports = res.ok ? await res.json() : [];

    const total    = reports.length;
    const open     = reports.filter(r => r.status === 'open').length;
    const resolved = reports.filter(r => r.status === 'resolved').length;

    const setEl = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    setEl('stat-reports',  total);
    setEl('stat-open',     open);
    setEl('stat-resolved', resolved);
  } catch { /* stats are optional on home page */ }
}

document.addEventListener('DOMContentLoaded', () => {
  loadMeta();
  loadStats();
});
