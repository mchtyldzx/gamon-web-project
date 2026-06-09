/* dashboard.js */
let charts = {};

function destroyChart(id) { if (charts[id]) { charts[id].destroy(); delete charts[id]; } }

async function loadDashboard(period) {
  const [sumRes, rankRes] = await Promise.all([
    fetch(`api/summary.php?period=${period}`),
    fetch('api/summary.php?ranking=true'),
  ]);
  const sum  = await sumRes.json();
  const rank = await rankRes.json();

  const sm = Object.fromEntries((sum.by_status || []).map(s => [s.status, s.cnt]));
  document.getElementById('d-total').textContent    = sum.total;
  document.getElementById('d-open').textContent     = sm.open     ?? 0;
  document.getElementById('d-resolved').textContent = sm.resolved ?? 0;

  if (rank && rank.length > 0) {
    const cleanest = rank[0];
    const dirtiest = rank[rank.length - 1];
    document.getElementById('d-cleanest').textContent = `${cleanest.name} (${cleanest.open} open reports)`;
    document.getElementById('d-dirtiest').textContent = `${dirtiest.name} (${dirtiest.open} open reports)`;
  } else {
    document.getElementById('d-cleanest').textContent = 'No data';
    document.getElementById('d-dirtiest').textContent = 'No data';
  }

  destroyChart('cat');
  const catCtx = document.getElementById('chart-cat')?.getContext('2d');
  if (catCtx && sum.by_category?.length) {
    charts.cat = new Chart(catCtx, {
      type: 'doughnut',
      data: { labels: sum.by_category.map(c => c.category || 'N/A'), datasets: [{ data: sum.by_category.map(c => c.cnt), backgroundColor: ['#2d6a4f','#40916c','#52b788','#74c69d','#95d5b2','#b7e4c7','#d8f3dc'] }] },
      options: { plugins: { legend: { position: 'bottom' } }, responsive: true },
    });
  }

  destroyChart('rank');
  const rankCtx = document.getElementById('chart-rank')?.getContext('2d');
  if (rankCtx && rank.length) {
    charts.rank = new Chart(rankCtx, {
      type: 'bar',
      data: {
        labels: rank.map(n => n.name),
        datasets: [
          { label: 'Open',     data: rank.map(n => n.open),     backgroundColor: '#c44536' },
          { label: 'Resolved', data: rank.map(n => n.resolved), backgroundColor: '#2d6a4f' },
        ],
      },
      options: { indexAxis: 'y', plugins: { legend: { position: 'bottom' } }, scales: { x: { stacked: true }, y: { stacked: true } }, responsive: true },
    });
  }
  window.currentRankData = rank;
}

async function exportDashboardPdf() {
  const [repRes, sumRes, rankRes] = await Promise.all([
    fetch('api/reports.php'),
    fetch('api/summary.php?period=month'), // overall stats
    fetch('api/summary.php?ranking=true')
  ]);
  const reports = await repRes.json();
  const sum = await sumRes.json();
  const rank = await rankRes.json();
  const sm = Object.fromEntries((sum.by_status || []).map(s => [s.status, s.cnt]));

  let cleanestText = 'N/A';
  let dirtiestText = 'N/A';
  if (rank && rank.length > 0) {
    cleanestText = `${rank[0].name} (${rank[0].open} open)`;
    dirtiestText = `${rank[rank.length - 1].name} (${rank[rank.length - 1].open} open)`;
  }

  const escapeHTML = str => {
    const d = document.createElement('div');
    d.textContent = String(str ?? '');
    return d.innerHTML;
  };

  const div = document.createElement('div');
  div.style.padding = '20px';
  div.style.fontFamily = 'Arial, sans-serif';
  
  let html = `
    <h2 style="color:#2d6a4f; border-bottom:2px solid #2d6a4f; padding-bottom:10px;">GaMon - Detailed Waste Accumulation Report</h2>
    <p><strong>Report Generated At:</strong> ${new Date().toLocaleString('en-GB')}</p>
    <div style="display:flex; gap:20px; margin-bottom:10px; background:#f4f4f4; padding:15px; border-radius:8px;">
      <div><strong>Total Reports:</strong> ${sum.total}</div>
      <div style="color:#c44536"><strong>Open:</strong> ${sm.open ?? 0}</div>
      <div style="color:#2d6a4f"><strong>Resolved:</strong> ${sm.resolved ?? 0}</div>
    </div>
    <div style="display:flex; gap:20px; margin-bottom:20px; background:#f4f4f4; padding:15px; border-radius:8px;">
      <div style="color:#2d6a4f"><strong>🌟 Cleanest Area:</strong> ${cleanestText}</div>
      <div style="color:#c44536"><strong>⚠️ Dirtiest Area:</strong> ${dirtiestText}</div>
    </div>
    <table style="width:100%; border-collapse:collapse; font-size:11px;">
      <thead>
        <tr style="background:#2d6a4f; color:#fff; text-align:left;">
          <th style="padding:8px; border:1px solid #ddd;">ID</th>
          <th style="padding:8px; border:1px solid #ddd;">Date / Time</th>
          <th style="padding:8px; border:1px solid #ddd;">Location (City)</th>
          <th style="padding:8px; border:1px solid #ddd;">Status</th>
          <th style="padding:8px; border:1px solid #ddd;">Description</th>
        </tr>
      </thead>
      <tbody>
  `;
  
  reports.forEach(r => {
    const desc = r.description.length > 50 ? r.description.substring(0,50)+'...' : r.description;
    html += `
      <tr>
        <td style="padding:8px; border:1px solid #ddd;">#${r.id}</td>
        <td style="padding:8px; border:1px solid #ddd;">${escapeHTML(r.created_at)}</td>
        <td style="padding:8px; border:1px solid #ddd;">${escapeHTML(r.city_name)}</td>
        <td style="padding:8px; border:1px solid #ddd;">${escapeHTML(r.status)}</td>
        <td style="padding:8px; border:1px solid #ddd;">${escapeHTML(desc)}</td>
      </tr>
    `;
  });
  
  html += `</tbody></table>`;
  div.innerHTML = html;
  
  html2pdf().set({
    margin: 10,
    filename: `gamon_detailed_report_${new Date().toISOString().slice(0,10)}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
  }).from(div).save();
}

document.addEventListener('DOMContentLoaded', async () => {
  const user = await Auth.getUser();
  if (!user || (user.role !== 'decision_maker' && user.role !== 'admin')) { 
      window.location.href = 'index.html'; 
      return; 
  }

  loadDashboard('all');

  document.querySelectorAll('.period-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      document.querySelectorAll('.period-btn').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-secondary');
      });
      e.target.classList.remove('btn-secondary');
      e.target.classList.add('btn-primary');
      loadDashboard(e.target.dataset.period);
    });
  });

  document.getElementById('btn-export-pdf')?.addEventListener('click', exportDashboardPdf);

  if (user.role === 'admin') {
    const importSec = document.getElementById('dashboard-import-section');
    if (importSec) importSec.style.display = 'flex';

    document.getElementById('import-csv')?.addEventListener('change', async (e) => {
      const file = e.target.files[0]; if (!file) return;
      const fd = new FormData(); fd.append('file', file);
      try {
        const res = await fetch('api/import.php?format=csv', { method: 'POST', body: fd });
        const d   = await res.json();
        if (!res.ok) throw new Error(d.error || 'Import failed');
        alert(`Imported ${d.imported} reports from CSV`);
        loadDashboard(document.querySelector('.period-btn.btn-primary')?.dataset.period || 'week');
      } catch (err) { alert(err.message); }
      e.target.value = '';
    });

    document.getElementById('import-json')?.addEventListener('change', async (e) => {
      const file = e.target.files[0]; if (!file) return;
      const text = await file.text();
      try {
        const res = await fetch('api/import.php?format=json', {
          method: 'POST', headers: { 'Content-Type': 'application/json' }, body: text,
        });
        const d = await res.json();
        if (!res.ok) throw new Error(d.error || 'Import failed');
        alert(`Imported ${d.imported} reports from JSON`);
        loadDashboard(document.querySelector('.period-btn.btn-primary')?.dataset.period || 'week');
      } catch (err) { alert(err.message); }
      e.target.value = '';
    });
  }
});
