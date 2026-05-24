/* dashboard.js — summary stats + Chart.js visualizations */

function escHtml(s) { const d = document.createElement('div'); d.textContent = String(s ?? ''); return d.innerHTML; }

const PERIOD_LABELS = { day: 'Today', week: 'Last 7 days', month: 'Last 30 days' };
let charts = {};

function destroyChart(id) { if (charts[id]) { charts[id].destroy(); delete charts[id]; } }

async function loadDashboard(period) {
  const [sumRes, rankRes, trendRes] = await Promise.all([
    fetch(`api/summary.php?period=${period}`),
    fetch('api/summary.php?ranking=true'),
    fetch(`api/summary.php?trend=1&period=${period}`),
  ]);

  const sum   = await sumRes.json();
  const rank  = await rankRes.json();
  const trend = await trendRes.json();

  /* Stat tiles */
  document.getElementById('d-total').textContent    = sum.total;
  document.getElementById('d-period').textContent   = PERIOD_LABELS[period] || period;

  const statusMap = Object.fromEntries((sum.by_status || []).map(s => [s.status, s.cnt]));
  document.getElementById('d-open').textContent     = statusMap.open     ?? 0;
  document.getElementById('d-resolved').textContent = statusMap.resolved ?? 0;

  /* Chart 1 — category donut */
  destroyChart('cat');
  const catCtx = document.getElementById('chart-cat')?.getContext('2d');
  if (catCtx && sum.by_category?.length) {
    charts.cat = new Chart(catCtx, {
      type: 'doughnut',
      data: {
        labels: sum.by_category.map(c => c.category || 'Unknown'),
        datasets: [{ data: sum.by_category.map(c => c.cnt), backgroundColor: ['#2d6a4f','#40916c','#52b788','#74c69d','#95d5b2','#b7e4c7','#d8f3dc'] }],
      },
      options: { plugins: { legend: { position: 'bottom' } }, responsive: true },
    });
  }

  /* Chart 2 — daily trend line */
  destroyChart('trend');
  const trendCtx = document.getElementById('chart-trend')?.getContext('2d');
  if (trendCtx) {
    charts.trend = new Chart(trendCtx, {
      type: 'line',
      data: {
        labels: trend.map(t => t.day),
        datasets: [{ label: 'Reports', data: trend.map(t => t.cnt), borderColor: '#2d6a4f', backgroundColor: 'rgba(45,106,79,.1)', fill: true, tension: 0.3 }],
      },
      options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, responsive: true },
    });
  }

  /* Chart 3 — neighbourhood ranking bar */
  destroyChart('rank');
  const rankCtx = document.getElementById('chart-rank')?.getContext('2d');
  if (rankCtx && rank.length) {
    charts.rank = new Chart(rankCtx, {
      type: 'bar',
      data: {
        labels: rank.map(n => n.name),
        datasets: [
          { label: 'Open', data: rank.map(n => n.open), backgroundColor: '#c44536' },
          { label: 'Resolved', data: rank.map(n => n.resolved), backgroundColor: '#2d6a4f' },
        ],
      },
      options: { indexAxis: 'y', plugins: { legend: { position: 'bottom' } }, scales: { x: { stacked: true }, y: { stacked: true } }, responsive: true },
    });
  }
}

document.addEventListener('DOMContentLoaded', async () => {
  const user = await Auth.getUser();
  if (!user) { window.location.href = 'login.html'; return; }

  let period = 'week';
  loadDashboard(period);

  document.querySelectorAll('.period-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('btn-primary'));
      btn.classList.add('btn-primary');
      period = btn.dataset.period;
      loadDashboard(period);
    });
  });
});
