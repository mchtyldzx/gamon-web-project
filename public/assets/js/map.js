/* map.js — Leaflet map with report markers */
const STATUS_COLOR = { open: '#c44536', assigned: '#c97920', resolved: '#2d6a4f', rejected: '#888' };

document.addEventListener('DOMContentLoaded', async () => {
  const map = L.map('map').setView([45.5, 25.0], 6);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  try {
    const res     = await fetch('api/reports.php');
    const reports = await res.json();
    const located = reports.filter(r => r.lat && r.lng);

    located.forEach(r => {
      const color = STATUS_COLOR[r.status] || '#888';
      L.circleMarker([r.lat, r.lng], { radius: 8, color, fillColor: color, fillOpacity: 0.8 })
        .bindPopup(`<strong>#${r.id}</strong> — ${r.neighborhood_name}<br><em>${r.description.slice(0,80)}</em><br>Status: ${r.status}`)
        .addTo(map);
    });

    document.getElementById('map-info').textContent =
      `${located.length} of ${reports.length} reports have location data.`;
  } catch (e) {
    document.getElementById('map-info').textContent = 'Failed to load reports.';
  }
});
