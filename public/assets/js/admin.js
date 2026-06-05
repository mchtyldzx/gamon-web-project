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
    tbody.innerHTML = users.map(u => {
      if (u.role === 'admin') {
        return `
          <tr>
            <td>${escHtml(u.full_name)}</td>
            <td>${escHtml(u.email)}</td>
            <td><span class="badge badge-admin">admin</span></td>
            <td>${fmtDate(u.created_at)}</td>
          </tr>`;
      }
      return `
      <tr>
        <td>${escHtml(u.full_name)}</td>
        <td>${escHtml(u.email)}</td>
        <td>
          <select class="form-select" style="padding: 2px 5px; font-size: 0.8rem" onchange="changeRole(${u.id}, this.value)">
            <option value="citizen" ${u.role === 'citizen' ? 'selected' : ''}>Citizen</option>
            <option value="staff" ${u.role === 'staff' ? 'selected' : ''}>Staff</option>
            <option value="decision_maker" ${u.role === 'decision_maker' ? 'selected' : ''}>Decision Maker</option>
          </select>
        </td>
        <td>${fmtDate(u.created_at)}</td>
      </tr>`;
    }).join('');
  } catch(e) { tbody.innerHTML = '<tr><td colspan="4">Error loading users.</td></tr>'; }
}

async function changeRole(userId, newRole) {
  try {
    const res = await fetch('api/admin/users.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: userId, role: newRole })
    });
    if (!res.ok) throw new Error('Failed to change role');
    loadUsers(); // refresh the table
  } catch(e) {
    alert(e.message);
    loadUsers();
  }
}

async function loadAllReports() {
  const tbody = document.getElementById('reports-tbody');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="5">Loading…</td></tr>';
  try {
    const res     = await fetch('api/reports.php');
    const reports = await res.json();
    if (!reports.length) { tbody.innerHTML = '<tr><td colspan="6">No reports.</td></tr>'; return; }
    tbody.innerHTML = reports.map(r => `
      <tr>
        <td>#${r.id}</td>
        <td>${escHtml(r.city_name)}</td>
        <td>${escHtml(r.category_name || '—')}</td>
        <td><span class="badge badge-${escHtml(r.status)}">${escHtml(r.status)}</span></td>
        <td>${escHtml(r.reporter_name)}</td>
        <td>
          <button class="btn btn-sm btn-ghost" style="color:var(--red); padding: 2px 6px" onclick="deleteReport(${r.id})">Delete</button>
        </td>
      </tr>`).join('');
  } catch(e) { tbody.innerHTML = '<tr><td colspan="6">Error loading reports.</td></tr>'; }
}

async function deleteReport(id) {
  if (!confirm(`Are you sure you want to delete report #${id}? This action cannot be undone.`)) return;
  try {
    const res = await fetch(`api/reports.php?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Failed to delete report');
    loadStats();
    loadAllReports();
  } catch (e) {
    alert(e.message);
  }
}

async function deleteAllReports() {
  if (!confirm('⚠️ WARNING: This will permanently delete ALL reports from the system. Are you sure?')) return;
  if (!confirm('This is your LAST chance. Type OK to confirm deletion of ALL reports.')) return;
  try {
    const res = await fetch('api/admin/delete-all-reports.php', { method: 'POST' });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Failed to delete reports');
    alert(`Successfully deleted ${data.deleted} reports.`);
    loadStats();
    loadAllReports();
  } catch (e) {
    alert(e.message);
  }
}

document.addEventListener('DOMContentLoaded', async () => {
  const user = await Auth.getUser();
  if (!user || user.role !== 'admin') { window.location.href = 'login.html'; return; }
  loadStats();
  loadUsers();
  loadAllReports();
});
