/* reports.js — report listing, creation and status update */

const SEVERITY_LABELS = { '1': 'Low', '2': 'Medium', '3': 'High' };
const SEVERITY_CLASSES = { '1': 'badge-sev1', '2': 'badge-sev2', '3': 'badge-sev3' };
const STATUS_CLASSES   = { open: 'badge-open', assigned: 'badge-assigned', resolved: 'badge-resolved', rejected: 'badge-rejected' };

function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = String(str ?? '');
  return d.innerHTML;
}

function fmtDate(iso) {
  if (!iso) return '—';
  return new Date(iso.replace(' ', 'T') + 'Z').toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function buildReportCard(r, canChangeStatus) {
  const sevLabel = SEVERITY_LABELS[r.severity] || r.severity;
  const sevCls   = SEVERITY_CLASSES[r.severity] || '';
  const staCls   = STATUS_CLASSES[r.status] || 'badge-open';

  let statusSelect = '';
  if (canChangeStatus) {
    statusSelect = `
      <div style="margin-top:.5rem;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
        <select class="form-select" style="flex:1;min-width:120px;padding:.3rem .7rem;font-size:.8rem;" data-id="${r.id}" id="sel-${r.id}">
          <option value="open"     ${r.status==='open'?'selected':''}>Open</option>
          <option value="assigned" ${r.status==='assigned'?'selected':''}>Assigned</option>
          <option value="resolved" ${r.status==='resolved'?'selected':''}>Resolved</option>
          <option value="rejected" ${r.status==='rejected'?'selected':''}>Rejected</option>
        </select>
        <button class="btn btn-primary btn-sm" onclick="updateStatus(${r.id})">Update</button>
      </div>`;
  }

  return `
    <article class="report-card" id="rcard-${r.id}">
      <div class="report-card-top">
        <div class="report-card-badges">
          <span class="badge ${staCls}">${escHtml(r.status)}</span>
          <span class="badge ${sevCls}">⚠ ${escHtml(sevLabel)}</span>
          ${r.category_name ? `<span class="badge" style="background:var(--clr-accent-light);color:var(--clr-primary-dark)">${escHtml(r.category_name)}</span>` : ''}
        </div>
        <span style="font-size:.75rem;color:var(--clr-muted);">#${r.id}</span>
      </div>
      <p class="report-desc">${escHtml(r.description)}</p>
      <div class="report-meta">
        <span>📍 ${escHtml(r.neighborhood_name)}, ${escHtml(r.locality)}</span>
        <span>👤 ${escHtml(r.reporter_name)}</span>
        <span>🗓 ${fmtDate(r.created_at)}</span>
      </div>
      ${statusSelect}
    </article>`;
}

async function updateStatus(id) {
  const sel = document.getElementById('sel-' + id);
  if (!sel) return;
  const status = sel.value;
  try {
    const res = await fetch('api/reports.php?id=' + id, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status }),
    });
    if (!res.ok) {
      const d = await res.json();
      if (res.status === 401) { window.location.href = 'login.html'; return; }
      alert(d.error || 'Update failed');
      return;
    }
    loadReports();
  } catch (e) { alert('Network error'); }
}

async function loadMeta() {
  try {
    const res  = await fetch('api/meta.php');
    const data = await res.json();
    return data;
  } catch { return { categories: [], neighborhoods: [] }; }
}

function populateSelect(selectId, items, valueKey, labelKey, placeholder = 'All') {
  const el = document.getElementById(selectId);
  if (!el) return;
  el.innerHTML = `<option value="">${placeholder}</option>` +
    items.map(i => `<option value="${i[valueKey]}">${escHtml(i[labelKey])}</option>`).join('');
}

async function loadReports() {
  const grid = document.getElementById('report-grid');
  if (!grid) return;

  grid.innerHTML = '<div class="loading-wrap"><div class="spinner"></div></div>';

  const params = new URLSearchParams();
  const nhood  = document.getElementById('filter-neighborhood')?.value;
  const cat    = document.getElementById('filter-category')?.value;
  const status = document.getElementById('filter-status')?.value;
  if (nhood)  params.set('neighborhood_id', nhood);
  if (cat)    params.set('category_id', cat);
  if (status) params.set('status', status);

  try {
    const [repRes, user] = await Promise.all([
      fetch('api/reports.php?' + params.toString()),
      Auth.getUser(),
    ]);

    if (repRes.status === 401) { window.location.href = 'login.html'; return; }

    const reports = await repRes.json();
    const canChange = user && (user.role === 'staff' || user.role === 'admin');

    if (!reports.length) {
      grid.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">🗑️</div>
          <h3>No reports found</h3>
          <p>No accumulation reports match your current filters.</p>
          <a href="report.html#new-report" class="btn btn-primary">Submit a report</a>
        </div>`;
      return;
    }

    grid.innerHTML = `<div class="report-grid">${reports.map(r => buildReportCard(r, canChange)).join('')}</div>`;
  } catch (e) {
    grid.innerHTML = `<div class="alert alert-error visible">Failed to load reports. ${e.message}</div>`;
  }
}

async function initReportPage() {
  const user = await Auth.getUser();
  const newSection = document.getElementById('new-report-section');

  const meta = await loadMeta();
  populateSelect('filter-neighborhood', meta.neighborhoods, 'id', 'name');
  populateSelect('filter-category',    meta.categories,    'id', 'name');

  if (newSection) {
    if (!user) {
      newSection.innerHTML = `<div class="alert alert-warning visible" style="margin-bottom:0">
        <a href="login.html">Sign in</a> to submit a garbage accumulation report.</div>`;
    } else {
      populateSelect('report-neighborhood', meta.neighborhoods, 'id', 'name', 'Select neighborhood');
      populateSelect('report-category',     meta.categories,    'id', 'name', 'Select category (optional)');

      const form  = document.getElementById('report-form');
      const alert = document.getElementById('report-alert');
      if (form) {
        form.addEventListener('submit', async (e) => {
          e.preventDefault();
          alert.className = 'alert';
          const btn = form.querySelector('button[type=submit]');
          btn.disabled = true; btn.textContent = 'Submitting…';
          const body = {
            neighborhood_id: form['report-neighborhood'].value,
            category_id:     form['report-category'].value,
            description:     form['report-desc'].value.trim(),
            severity:        form.querySelector('input[name="severity"]:checked')?.value || '2',
          };
          if (!body.neighborhood_id || !body.description) {
            alert.textContent = 'Please fill in all required fields.';
            alert.className = 'alert alert-error visible';
            btn.disabled = false; btn.textContent = 'Submit Report';
            return;
          }
          try {
            const res = await fetch('api/reports.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(body),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Submission failed');
            alert.textContent = `Report #${data.id} submitted successfully!`;
            alert.className = 'alert alert-success visible';
            form.reset();
            loadReports();
          } catch (err) {
            alert.textContent = err.message;
            alert.className = 'alert alert-error visible';
          }
          btn.disabled = false; btn.textContent = 'Submit Report';
        });
      }
    }
  }

  document.getElementById('filter-neighborhood')?.addEventListener('change', loadReports);
  document.getElementById('filter-category')?.addEventListener('change', loadReports);
  document.getElementById('filter-status')?.addEventListener('change', loadReports);
  document.getElementById('btn-clear-filters')?.addEventListener('click', () => {
    ['filter-neighborhood','filter-category','filter-status'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });
    loadReports();
  });

  loadReports();
}

document.addEventListener('DOMContentLoaded', initReportPage);
