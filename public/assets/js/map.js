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

  /* Geocode search — uses api/geocode.php (PHP cURL → Nominatim) */
  let geoMarker = null;
  async function geoSearch() {
    const q = document.getElementById('geo-query')?.value.trim();
    if (!q) return;
    try {
      const res   = await fetch('api/geocode.php?q=' + encodeURIComponent(q));
      const items = await res.json();
      if (!items.length) { document.getElementById('map-info').textContent = 'Address not found.'; return; }
      const { lat, lon, display_name } = items[0];
      if (geoMarker) map.removeLayer(geoMarker);
      geoMarker = L.marker([lat, lon]).addTo(map).bindPopup(display_name).openPopup();
      map.setView([lat, lon], 14);
    } catch { document.getElementById('map-info').textContent = 'Geocode error.'; }
  }
  document.getElementById('geo-btn')?.addEventListener('click', geoSearch);
  document.getElementById('geo-query')?.addEventListener('keydown', e => { if (e.key === 'Enter') geoSearch(); });
});
