/* admin.js — user list + report list for admin */

function escHtml(s) { const d = document.createElement('div'); d.textContent = String(s ?? ''); return d.innerHTML; }
function fmtDate(iso) { return iso ? new Date(iso.replace(' ','T')+'Z').toLocaleDateString('en-GB') : '—'; }

async function loadStats() {
  try {
    const res  = await fetch('api/admin/stats.php');
    if (!res.ok) { if (res.status === 401 || res.status === 403) { window.location.href = 'login.html'; } return; }
    const s = await res.json();
    document.getElementById('a-users').textContent    = s.users;
    document.getElementById('a-reports').textContent  = s.reports;
    document.getElementById('a-open').textContent     = s.open;
    document.getElementById('a-resolved').textContent = s.resolved;
  } catch(e) { console.error(e); }
}

async function loadUsers() {
  const tbody = document.getElementById('users-tbody');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="4">Loading…</td></tr>';
  try {
    const res   = await fetch('api/admin/users.php');
    const users = await res.json();
    if (!users.length) { tbody.innerHTML = '<tr><td colspan="4">No users found.</td></tr>'; return; }
    tbody.innerHTML = users.map(u => `
      <tr>
        <td>${escHtml(u.full_name)}</td>
        <td>${escHtml(u.email)}</td>
        <td><span class="badge badge-${escHtml(u.role)}">${escHtml(u.role)}</span></td>
        <td>${fmtDate(u.created_at)}</td>
      </tr>`).join('');
  } catch(e) { tbody.innerHTML = '<tr><td colspan="4">Error loading users.</td></tr>'; }
}

async function loadAllReports() {
  const tbody = document.getElementById('reports-tbody');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="5">Loading…</td></tr>';
  try {
    const res     = await fetch('api/reports.php');
    const reports = await res.json();
    if (!reports.length) { tbody.innerHTML = '<tr><td colspan="5">No reports.</td></tr>'; return; }
    tbody.innerHTML = reports.map(r => `
      <tr>
        <td>#${r.id}</td>
        <td>${escHtml(r.neighborhood_name)}</td>
        <td>${escHtml(r.category_name || '—')}</td>
        <td><span class="badge badge-${escHtml(r.status)}">${escHtml(r.status)}</span></td>
        <td>${escHtml(r.reporter_name)}</td>
      </tr>`).join('');
  } catch(e) { tbody.innerHTML = '<tr><td colspan="5">Error loading reports.</td></tr>'; }
}

document.addEventListener('DOMContentLoaded', async () => {
  const user = await Auth.getUser();
  if (!user || user.role !== 'admin') { window.location.href = 'login.html'; return; }
  loadStats();
  loadUsers();
  loadAllReports();
});
