(function(){
  'use strict';

  // Unified GeoJSON map logic for both Disease (Shehia) and Medication maps
  let diseaseMap, diseaseLayer, diseaseLegend;
  let medicationMap, medicationLayer, medicationLegend;
  // Track active fetch controllers per map to abort previous requests cleanly
  let shehiaCtrl = null, medicationCtrl = null;
  // Note: We no longer use L.Proj.geoJson path to avoid inconsistencies
  const hasProjLeaflet = !!(window.L && L.Proj && L.Proj.geoJson);

  // Deterministic EPSG:3395/3857 (Spherical Mercator meters) -> WGS84 conversion (no external plugins)
  const R_MAJOR = 6378137;
  function mercatorToWgs84Point(pt){
    const x = pt[0];
    const y = pt[1];
    const lng = (x / R_MAJOR) * (180 / Math.PI);
    const lat = (2 * Math.atan(Math.exp(y / R_MAJOR)) - Math.PI / 2) * (180 / Math.PI);
    // Clamp latitude to WebMercator limits
    const clampedLat = Math.max(-85.05112878, Math.min(85.05112878, lat));
    return [lng, clampedLat];
  }
  function geomToWgs84(geom){
    if (!geom || !geom.type) return geom;
    const t = geom.type, c = geom.coordinates;
    if (t === 'MultiPolygon') return { type:t, coordinates: c.map(poly=>poly.map(r=>r.map(mercatorToWgs84Point))) };
    if (t === 'Polygon') return { type:t, coordinates: c.map(r=>r.map(mercatorToWgs84Point)) };
    return geom;
  }
  function toWGS84(geojson){
    const out = JSON.parse(JSON.stringify(geojson));
    if (out.type === 'FeatureCollection') out.features = out.features.map(f=>({ type:'Feature', properties:f.properties, geometry: geomToWgs84(f.geometry) }));
    else if (out.type === 'Feature') out.geometry = geomToWgs84(out.geometry);
    return out;
  }

  function getColor(value, max){
    const ratio = max>0 ? Math.min(1, value/max) : 0;
    const r = Math.round(255 * ratio);
    const b = Math.round(255 * (1 - ratio));
    return `rgb(${r}, 80, ${b})`;
  }


  function normalizeName(s){
    if (s === null || s === undefined) return '';
    try {
      return String(s).toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]/g, '');
    } catch(e) {
      return String(s).toLowerCase();
    }
  }

  function addLegend(map, max, title){
    const legend = L.control({position:'bottomright'});
    legend.onAdd = function(){
      const div = L.DomUtil.create('div', 'info legend');
      const grades = [0, max*0.2, max*0.4, max*0.6, max*0.8, max];
      let html = '<div style="background:#fff;padding:6px 8px;border-radius:4px;box-shadow:0 1px 4px rgba(0,0,0,0.2);font:12px/14px Arial">'
        + `<b>${title}</b><br/>`;
      for (let i=0;i<grades.length-1;i++){
        const from = Math.round(grades[i]);
        const to = Math.round(grades[i+1]);
        const c = getColor(to, max);
        html += `<i style="background:${c};width:14px;height:14px;display:inline-block;margin-right:6px"></i> ${from} – ${to}<br/>`;
      }
      html += '</div>';
      div.innerHTML = html; return div;
    };
    legend.addTo(map);
    return legend;
  }

  function ensureBasemap(map){
    // Always add OSM basemap (standard CRS)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      noWrap: true,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
  }

  function setStatus(containerId, text){
    const c = document.getElementById(containerId);
    if (!c) return;
    let el = c.querySelector('.geo-status');
    if (!el) {
      el = document.createElement('div');
      el.className = 'geo-status';
      el.style.cssText = 'position:absolute;top:8px;left:8px;z-index:1000;background:#fff;padding:6px 8px;border-radius:4px;box-shadow:0 1px 4px rgba(0,0,0,0.2);font:12px Arial';
      c.style.position = 'relative';
      c.appendChild(el);
    }
    el.textContent = text;
  }

  function clearStatus(containerId){
    const c = document.getElementById(containerId);
    if (!c) return;
    const el = c.querySelector('.geo-status');
    if (el) el.remove();
  }

  function renderGeo(mapKind, period){
    // mapKind: 'disease' or 'medication'
    const isShehia = mapKind === 'shehia';
    const containerId = isShehia ? 'shehiaMap' : (mapKind === 'medication' ? 'medicationGeo' : '');
    const legendTitle = isShehia ? 'Cases' : 'Prescriptions';

    const container = document.getElementById(containerId);
    if (!container) return;

    const mapDivId = isShehia ? 'map-shehia' : 'map-med-geo';
    if (!document.getElementById(mapDivId)) {
      container.innerHTML = `<div id="${mapDivId}" style=\"height:80vh;width:100%\"></div>`;
      if (isShehia) diseaseMap = L.map(mapDivId);
      else medicationMap = L.map(mapDivId);
      ensureBasemap(isShehia ? diseaseMap : medicationMap);
      const m = isShehia ? diseaseMap : medicationMap;
      try { m.setView([-6.165, 39.201], 9); } catch(e){}
      requestAnimationFrame(() => m.invalidateSize());
      setTimeout(() => m.invalidateSize(), 50);
    }

    setStatus(containerId, 'Loading map data...');

    const mapRef = isShehia ? diseaseMap : medicationMap;
    const oldLayer = isShehia ? diseaseLayer : medicationLayer;

    const oldLegend = isShehia ? diseaseLegend : medicationLegend;

    // Fetch shehia stats (cases + meds + totals) + GeoJSON with timeout; abort any prior in-flight request
    let ctrl;
    if (isShehia) {
      if (shehiaCtrl) { try { shehiaCtrl.abort(); } catch(_){} }
      shehiaCtrl = new AbortController();
      ctrl = shehiaCtrl;
    } else {
      if (medicationCtrl) { try { medicationCtrl.abort(); } catch(_){} }
      medicationCtrl = new AbortController();
      ctrl = medicationCtrl;
    }
    const to = setTimeout(() => ctrl.abort(), 20000);
    Promise.all([
      fetch(`/api/shehia/stats?period=${period}`, { signal: ctrl.signal }).then(r=>r.ok?r.json():{success:false}).catch(()=>({success:false})),
      fetch('/geo/shehia', { cache:'no-store', signal: ctrl.signal }).then(r=>r.ok?r.json():Promise.reject(new Error('GeoJSON 404')))
    ]).then(([api, geojson]) => {
      clearTimeout(to);
      // Defensive: ensure we have a valid FeatureCollection before proceeding
      if (!geojson || geojson.type !== 'FeatureCollection' || !Array.isArray(geojson.features)) {
        setStatus(containerId, 'Invalid GeoJSON format');
        console.error('Invalid GeoJSON:', geojson);
        return;
      }
      clearStatus(containerId);

      // Build lookup maps by shehia
      const mapCases = {}; const mapMeds = {}; const mapPatients = {}; const mapChronic = {}; const mapPreg = {};
      let maxVal = 0;
      if (api && api.success && api.data && Array.isArray(api.data.stats)) {
        api.data.stats.forEach(row => {
          const key = normalizeName(row.shehia);
          mapCases[key] = row.disease_cases || 0;
          mapMeds[key] = row.meds_prescribed || 0;
          mapPatients[key] = row.total_patients || 0;
          mapChronic[key] = row.chronic_cases || 0;
          mapPreg[key] = row.pregnancy_cases || 0;
          maxVal = Math.max(maxVal, row.disease_cases || 0); // color scale by cases
        });
      }

      // Remove prior layer/legend
      if (oldLayer) try { mapRef.removeLayer(oldLayer); } catch(e){}
      if (oldLegend) try { mapRef.removeControl(oldLegend); } catch(e){}

      // Choose renderer: always convert Mercator meters -> WGS84 and render via standard Leaflet
      let layer;
      try {
        const wgs84 = toWGS84(geojson);
        layer = L.geoJSON(wgs84, {
          style: f => {
            const raw = (f.properties?.ward_name || f.properties?.dist_name || f.properties?.counc_name || f.properties?.name);
            const v = mapCases[normalizeName(raw)] || 0;
            return { color:'#222', weight:2, opacity:1, fillOpacity:0.55, fillColor:getColor(v, maxVal) };
          },
          onEachFeature: (feature, l) => {
            const raw = (feature.properties?.ward_name || feature.properties?.dist_name || feature.properties?.counc_name || feature.properties?.name);
            const key = normalizeName(raw);
            const patients = mapPatients[key] || 0;
            const chronic = mapChronic[key] || 0;
            const preg = mapPreg[key] || 0;
            const html = `<div><b>${raw}</b>
              <br/>Total patients: ${patients}
              <br/>Chronic (Diabetes/Hypertension): ${chronic}
              <br/>Pregnancy cases: ${preg}</div>`;
            l.bindTooltip(html, {sticky:true});
          }
        });
      } catch (e) {
        setStatus(containerId, `Failed to render polygons: ${e && e.message ? e.message : e}`);
        console.error('Layer creation failed', e);
        return;
      }

      // Add layer, fix size, and fit bounds if valid; otherwise keep default view
      layer.addTo(mapRef);
      try { mapRef.invalidateSize(); } catch(e){}
      setTimeout(() => { try { mapRef.invalidateSize(); } catch(e){} }, 50);
      try {
        const b = layer.getBounds();
        if (b && b.isValid && b.isValid()) {
          mapRef.fitBounds(b);
        }
      } catch(e){}

      // Add legend
      const legend = maxVal > 0 ? addLegend(mapRef, maxVal, legendTitle) : null;

      // Store refs
      if (isShehia) { diseaseLayer = layer; diseaseLegend = legend; }
      else { medicationLayer = layer; medicationLegend = legend; }
    }).catch(err => {
      // Swallow expected aborts silently
      if (err && (err.name === 'AbortError' || (err.message && err.message.toLowerCase().includes('abort')))) return;
      console.error('Geo heatmap error', err);
    });
  }

  // Wire up to dashboard period changes
  function bootGeoHeatmaps(){
    try {
      const manager = window.dashboardManager;
      const p = manager ? manager.getCurrentPeriod() : 'this_year';
      if (document.getElementById('shehiaMap')) renderGeo('shehia', p);
      if (document.getElementById('medicationGeo')) renderGeo('medication', p);
      if (manager) {
        const oldRefresh = manager.refreshData;
        manager.refreshData = function(){
          const period = manager.getCurrentPeriod();
          if (document.getElementById('shehiaMap')) renderGeo('shehia', period);
          if (document.getElementById('medicationGeo')) renderGeo('medication', period);
          return oldRefresh();
        };
      }
    } catch (e) {
      console.error('Geo heatmaps boot error', e);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootGeoHeatmaps);
  } else {
    bootGeoHeatmaps();
  }
})();

