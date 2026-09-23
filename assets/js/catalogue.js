(() => {
  'use strict';
  const grid = document.querySelector('[data-grid-view]');
  const map = document.querySelector('[data-map-view]');
  const switches = document.querySelectorAll('[data-view]');
  const status = document.querySelector('[data-geo-status]');
  let leafletMap = null;
  let userMarker = null;

  function initLeafletMap() {
    if (leafletMap || !window.L) return;
    const liveMap = document.querySelector('[data-leaflet-map]');
    const fallback = document.querySelector('[data-map-fallback]');
    if (!liveMap) return;
    liveMap.hidden = false;
    liveMap.classList.add('is-loading');
    leafletMap = L.map(liveMap, { scrollWheelZoom: false, zoomControl: true });
    const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    });
    tiles.on('load', () => { liveMap.classList.remove('is-loading'); if (fallback) fallback.hidden = true; setTimeout(() => leafletMap.invalidateSize(), 50); });
    tiles.addTo(leafletMap);
    const bounds = [];
    document.querySelectorAll('[data-map-pin]').forEach((pin) => {
      const lat = Number(pin.dataset.lat), lon = Number(pin.dataset.lon);
      if (!lat || !lon) return;
      const colors = { green: '#237a4b', orange: '#c77727', red: '#b23d34' };
      L.circleMarker([lat, lon], { radius: 10, color: '#fff', weight: 3, fillColor: colors[pin.dataset.tier] || '#17201f', fillOpacity: 1 })
        .addTo(leafletMap)
        .bindPopup(`<strong>${pin.dataset.name}</strong><br>${pin.dataset.city} · ${pin.dataset.symbol} ${pin.dataset.level}<br><a href="${pin.dataset.url}">Voir l’offre</a>`);
      bounds.push([lat, lon]);
    });
    if (bounds.length) leafletMap.fitBounds(bounds, { padding: [35, 35] }); else leafletMap.setView([48.8566, 2.3522], 11);
  }

  switches.forEach((button) => button.addEventListener('click', () => {
    const showMap = button.dataset.view === 'map';
    if (grid) grid.hidden = showMap;
    if (map) map.hidden = !showMap;
    if (showMap) { initLeafletMap(); setTimeout(() => leafletMap?.invalidateSize(), 80); }
    switches.forEach((item) => {
      const active = item === button;
      item.classList.toggle('is-active', active);
      item.setAttribute('aria-pressed', String(active));
    });
  }));

  const rad = (value) => value * Math.PI / 180;
  const distanceKm = (aLat, aLon, bLat, bLon) => {
    const earth = 6371;
    const dLat = rad(bLat - aLat), dLon = rad(bLon - aLon);
    const value = Math.sin(dLat / 2) ** 2 + Math.cos(rad(aLat)) * Math.cos(rad(bLat)) * Math.sin(dLon / 2) ** 2;
    return earth * 2 * Math.atan2(Math.sqrt(value), Math.sqrt(1 - value));
  };

  document.querySelector('[data-near-me]')?.addEventListener('click', () => {
    if (!navigator.geolocation) {
      if (status) status.textContent = 'La géolocalisation n’est pas disponible dans ce navigateur.';
      return;
    }
    if (status) status.textContent = 'Localisation en cours…';
    navigator.geolocation.getCurrentPosition(({ coords }) => {
      let nearby = 0;
      const selectedRadius = Number(document.querySelector('#radius')?.value || 25);
      document.querySelectorAll('[data-product-card]').forEach((card) => {
        const lat = Number(card.dataset.lat), lon = Number(card.dataset.lon);
        if (!lat || !lon) return;
        const distance = distanceKm(coords.latitude, coords.longitude, lat, lon);
        card.dataset.distanceKm = distance.toFixed(1);
        const label = card.querySelector('[data-distance]');
        if (label) label.textContent = `${distance.toFixed(0)} km`;
        card.hidden = distance > selectedRadius;
        if (distance <= selectedRadius) nearby++;
      });
      if (status) status.textContent = `${nearby} offre${nearby > 1 ? 's' : ''} trouvée${nearby > 1 ? 's' : ''} dans un rayon de ${selectedRadius} km.`;
      if (leafletMap && window.L) {
        if (userMarker) leafletMap.removeLayer(userMarker);
        userMarker = L.circleMarker([coords.latitude, coords.longitude], { radius: 9, color: '#17201f', weight: 3, fillColor: '#fff', fillOpacity: 1 }).addTo(leafletMap).bindPopup('Votre position approximative');
        leafletMap.setView([coords.latitude, coords.longitude], 11);
      }
      window.FocalAnalytics?.track('near_me_success', { results: nearby });
    }, () => {
      if (status) status.textContent = 'Localisation refusée. La carte reste consultable sans partager votre position.';
    }, { enableHighAccuracy: false, timeout: 8000, maximumAge: 300000 });
  });
})();
