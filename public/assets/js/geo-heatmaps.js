(function(){
  'use strict';

  // Unified GeoJSON map logic for both Disease and Medication maps
  let diseaseMap, diseaseLayer, diseaseLegend;
  let medicationMap, medicationLayer, medicationLegend;
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
    const isDisease = mapKind === 'disease';
    const containerId = isDisease ? 'diseaseHeatmap' : 'medicationGeo';
    const legendTitle = isDisease ? 'Cases' : 'Prescriptions';

    const container = document.getElementById(containerId);
    if (!container) return;

    const mapDivId = isDisease ? 'map-heat' : 'map-med-geo';
    if (!document.getElementById(mapDivId)) {
      container.innerHTML = `<div id="${mapDivId}" style="height:90vh;width:100%"></div>`;
      if (isDisease) diseaseMap = L.map(mapDivId);
      else medicationMap = L.map(mapDivId);
      ensureBasemap(isDisease ? diseaseMap : medicationMap);
      const m = isDisease ? diseaseMap : medicationMap;
      // Set a sensible default view over Zanzibar (Stone Town area) so tiles load even without bounds
      try { m.setView([-6.165, 39.201], 9); } catch(e){}
      // Give the browser a tick to lay out the container, then invalidate size
      requestAnimationFrame(() => m.invalidateSize());
      setTimeout(() => m.invalidateSize(), 50);
    }

    setStatus(containerId, 'Loading map data...');

    const mapRef = isDisease ? diseaseMap : medicationMap;
    const oldLayer = isDisease ? diseaseLayer : medicationLayer;

    const oldLegend = isDisease ? diseaseLegend : medicationLegend;

    // Fetch dashboard data (for values) + GeoJSON (geometry)
    Promise.all([
      fetch(`/api/dashboard?period=${period}`).then(r=>r.json()).catch(e=>({success:false,error:e})),
      fetch('/data/zanibar_kata2.geojson', { cache:'no-store' }).then(r=>r.ok?r.json():Promise.reject(new Error('GeoJSON 404')))
    ]).then(([api, geojson]) => {
      // Defensive: ensure we have a valid FeatureCollection before proceeding
      if (!geojson || geojson.type !== 'FeatureCollection' || !Array.isArray(geojson.features)) {
        setStatus(containerId, 'Invalid GeoJSON format');
        console.error('Invalid GeoJSON:', geojson);
        return;
      }
      if (!api || api.success === false) {
        // Proceed with empty counts so we still draw geometry
        console.warn('Map API data unavailable; rendering base polygons only');
        clearStatus(containerId);
      } else {
        clearStatus(containerId);
      }

      // Build value map (normalized by name)
      let counts = {}; let maxVal = 0;
      if (isDisease) {
        const heat = api.data?.charts?.disease_heatmap;
        const tmp = {};
        (heat?.series||[]).forEach(s => (s.data||[]).forEach(d => {
          const key = normalizeName(d.x);
          tmp[key] = (tmp[key]||0) + (d.y||0);
        }));
        counts = tmp;
        maxVal = Object.values(counts).reduce((a,b)=>Math.max(a,b),0);
      } else {
        const med = api.data?.charts?.medication_geo;
        const tmp = {};
        (med?.by_shehia||[]).forEach(row => {
          const key = normalizeName(row.name);
          tmp[key] = (tmp[key]||0) + (row.value||0);
        });
        counts = tmp;
        maxVal = Object.values(counts).reduce((a,b)=>Math.max(a,b),0);
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
            const raw = isDisease ? (f.properties?.dist_name || f.properties?.ward_name || f.properties?.counc_name)
                                   : (f.properties?.ward_name || f.properties?.dist_name || f.properties?.counc_name);
            const v = counts[normalizeName(raw)] || 0;
            return { color:'#222', weight:2, opacity:1, fillOpacity:0.55, fillColor:getColor(v, maxVal) };
          },
          onEachFeature: (feature, l) => {
            const raw = isDisease ? (feature.properties?.dist_name || feature.properties?.ward_name || feature.properties?.counc_name)
                                   : (feature.properties?.ward_name || feature.properties?.dist_name || feature.properties?.counc_name);
            const v = counts[normalizeName(raw)] || 0;
            l.bindTooltip(`${raw}: ${v} ${legendTitle.toLowerCase()}`, {sticky:true});
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
      if (isDisease) { diseaseLayer = layer; diseaseLegend = legend; }
      else { medicationLayer = layer; medicationLegend = legend; }
    }).catch(err => console.error('Geo heatmap error', err));
  }

  // Wire up to dashboard period changes
  function bootGeoHeatmaps(){
    try {
      const manager = window.dashboardManager;
      const p = manager ? manager.getCurrentPeriod() : 'this_year';
      if (document.getElementById('diseaseHeatmap')) renderGeo('disease', p);
      if (document.getElementById('medicationGeo')) renderGeo('medication', p);
      if (manager) {
        const oldRefresh = manager.refreshData;
        manager.refreshData = function(){
          const period = manager.getCurrentPeriod();
          if (document.getElementById('diseaseHeatmap')) renderGeo('disease', period);
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

