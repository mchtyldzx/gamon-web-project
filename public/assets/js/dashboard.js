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
