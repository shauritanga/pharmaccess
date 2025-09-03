@extends('layouts.app')

@section('content')
    <!-- App container starts -->
    <div class="app-container">
        <!-- App hero header starts -->
        <div class="app-hero-header d-flex align-items-center">
            <!-- Breadcrumb starts -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <i class="ri-home-8-line lh-1 pe-3 me-3 border-end"></i>
                    <a href="{{ route('home') }}">Home</a>
                </li>
                <li class="breadcrumb-item text-primary" aria-current="page">
                    Top Diseases Analytics
                </li>
            </ol>
            <!-- Breadcrumb ends -->
        </div>
        <!-- App hero header ends -->

        <!-- App body starts -->
        <div class="app-body">
            <!-- Filters Section -->
            <div class="row gx-3 mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Analytics Filters</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <!-- Disease Filter -->
                                <div class="col-lg-4 col-md-6">
                                    <label for="diseaseFilter" class="form-label">Select Disease</label>
                                    <input type="text" id="diseaseSearch" class="form-control mb-2" placeholder="Search ICD code or name..." />
                                    <select id="diseaseFilter" class="form-select">
                                        <option value="">All Diseases</option>
                                    </select>
                                    <small class="text-muted">Showing top results. Type to search to narrow down.</small>
                                </div>

                                <!-- Year Range Filter -->
                                <div class="col-lg-2 col-md-3">
                                    <label for="yearStart" class="form-label">Start Year</label>
                                    <select id="yearStart" class="form-select">
                                        @foreach($availableYears as $year)
                                            <option value="{{ $year }}" {{ $year == $startYear ? 'selected' : '' }}>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-3">
                                    <label for="yearEnd" class="form-label">End Year</label>
                                    <select id="yearEnd" class="form-select">
                                        @foreach($availableYears as $year)
                                            <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="col-lg-4 col-md-12">
                                    <button id="applyFilters" class="btn btn-primary me-2">
                                        <i class="ri-filter-line"></i> Apply Filters
                                    </button>
                                    <button id="resetFilters" class="btn btn-outline-secondary me-2">
                                        <i class="ri-refresh-line"></i> Reset
                                    </button>
                                    <div id="loadingIndicator" class="spinner-border spinner-border-sm d-none" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <!-- First Row: Monthly Distribution and Gender (Full Row) -->
            <div class="row gx-3 chart-row-1">
                <!-- Monthly Distribution Chart -->
                <div class="col-xxl-8 col-lg-8 col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Monthly Distribution of Cases</h5>
                        </div>
                        <div class="card-body">
                            <div id="monthlyChart"></div>
                        </div>
                    </div>
                </div>

                <!-- Gender Distribution Chart -->
                <div class="col-xxl-4 col-lg-4 col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Cases by Gender</h5>
                        </div>
                        <div class="card-body">
                            <div id="genderChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row: Age Group and Economic Status -->
            <div class="row gx-3 chart-row-2">
                <!-- Age Group Distribution Chart -->
                <div class="col-xxl-6 col-lg-6 col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Cases by Age Group</h5>
                        </div>
                        <div class="card-body">
                            <div id="ageGroupChart"></div>
                        </div>
                    </div>
                </div>

                <!-- Economic Status Chart -->
                <div class="col-xxl-6 col-lg-6 col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Cases by Economic Status</h5>
                        </div>
                        <div class="card-body">
                            <div id="economicChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- District Distribution Chart - Full Width -->
            <div class="row gx-3 chart-row-3">
                <div class="col-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Cases by District - All Zanzibar Districts</h5>
                        </div>
                        <div class="card-body">
                            <div id="districtChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Heat Map - Full Width -->
            <!-- <div class="row gx-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Geographic Heat Map - Disease Distribution Across Zanzibar</h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="perCapitaToggle">
                                <label class="form-check-label" for="perCapitaToggle">
                                    Per Capita View
                                </label>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="heatMap" style="height: 500px;"></div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
        <!-- App body ends -->
    </div>
    <!-- App container ends -->
@endsection

@push('styles')
    <!-- Leaflet CSS for Heat Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .leaflet-container {
            height: 100%;
            width: 100%;
            border-radius: 8px;
        }

        .chart-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 350px;
            color: #6c757d;
        }

        .filter-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        /* Enforce consistent chart container heights per row */
        .chart-row-1 .card {
            height: 480px; /* Fixed height for row 1 cards */
        }

        .chart-row-2 .card {
            height: 460px; /* Fixed height for row 2 cards */
        }

        .chart-row-3 .card {
            height: 480px; /* Fixed height for row 3 cards */
        }

        .chart-row-1 .card-body,
        .chart-row-2 .card-body,
        .chart-row-3 .card-body {
            height: calc(100% - 60px); /* Account for card header */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chart-row-1 #monthlyChart,
        .chart-row-1 #genderChart {
            height: 400px !important;
            width: 100%;
        }

        .chart-row-2 #ageGroupChart,
        .chart-row-2 #economicChart {
            height: 380px !important;
            width: 100%;
        }

        .chart-row-3 #districtChart {
            height: 400px !important;
            width: 100%;
        }

        /* Professional heat map styling */
        #heatMap {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .heat-legend {
            font-family: Arial, sans-serif !important;
        }

        .custom-popup .leaflet-popup-content-wrapper {
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .custom-popup .leaflet-popup-content {
            margin: 0;
            padding: 0;
        }

        /* Leaflet control styling */
        .leaflet-control-layers {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .leaflet-control-scale {
            border-radius: 4px;
        }


    </style>
@endpush

@push('scripts')
    <!-- Include Leaflet and Heat Map Plugin -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>

    <script>
        // Isolate our script to prevent conflicts
        (function() {
            'use strict';

            // Prevent multiple initializations
            if (window.topDiseasesScriptLoaded) {
                return;
            }
            window.topDiseasesScriptLoaded = true;

            // Local variables (not global to prevent conflicts)
            let charts = {};
            let heatMap = null;
            let currentData = null;

        // Prevent conflicts with other scripts
        window.topDiseasesInitialized = false;

        // Simple initialization like other pages
        document.addEventListener('DOMContentLoaded', function() {
            if (!document.getElementById('monthlyChart') || window.topDiseasesInitialized) {
                return;
            }

            window.topDiseasesInitialized = true;

            console.log('Initializing top-diseases analytics...');
            initializeHeatMap(); // Initialize heat map
            setupEventListeners();
            loadInitialData();
        });

        // Also handle page visibility changes (helps with browser tab switching)
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && currentData) {
                setTimeout(() => {
                    resizeAllCharts();
                }, 100);
            }
        });



        // Initialize professional heat map
        function initializeHeatMap() {
            try {
                const mapContainer = document.getElementById('heatMap');
                if (!mapContainer) {
                    console.error('Heat map container not found');
                    return;
                }

                // Check if Leaflet and heat plugin are available
                if (typeof L === 'undefined' || typeof L.heatLayer === 'undefined') {
                    console.error('Leaflet or heat plugin not loaded');
                    return;
                }

                // Create map centered on Zanzibar with better styling
                heatMap = L.map('heatMap', {
                    zoomControl: true,
                    scrollWheelZoom: true,
                    doubleClickZoom: true,
                    boxZoom: true,
                    keyboard: true,
                    dragging: true,
                    touchZoom: true
                }).setView([-6.1659, 39.1917], 10);

                // Add multiple tile layer options
                const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 18
                });

                const cartoLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    attribution: '© CARTO © OpenStreetMap contributors',
                    maxZoom: 18
                });

                // Add default layer
                cartoLayer.addTo(heatMap);

                // Layer control
                const baseLayers = {
                    "Light": cartoLayer,
                    "OpenStreetMap": osmLayer
                };
                L.control.layers(baseLayers).addTo(heatMap);

                // Add scale control
                L.control.scale().addTo(heatMap);

            } catch (error) {
                console.error('Error initializing heat map:', error);
            }
        }

        // Setup event listeners
        function setupEventListeners() {
            document.getElementById('applyFilters').addEventListener('click', applyFilters);
            document.getElementById('resetFilters').addEventListener('click', resetFilters);
            const perCapitaToggle = document.getElementById('perCapitaToggle');
            if (perCapitaToggle) perCapitaToggle.addEventListener('change', updateHeatMap);

            // Auto-apply on change (like medication page)
            const diseaseSelect = document.getElementById('diseaseFilter');
            const diseaseSearch = document.getElementById('diseaseSearch');
            const yearStart = document.getElementById('yearStart');
            const yearEnd = document.getElementById('yearEnd');

            ;[diseaseSelect, yearStart, yearEnd].forEach(el => {
                if (!el) return;
                el.addEventListener('change', () => {
                    const filters = getCurrentFilters();
                    fetchAnalyticsData(filters);
                });
            });

            if (diseaseSearch) {
                let searchTimeout;
                diseaseSearch.addEventListener('input', () => {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        loadDiseases(diseaseSearch.value.trim());
                    }, 300);
                });
            }

            // Add window resize handler to fix chart rendering issues
            window.addEventListener('resize', debounce(function() {
                resizeAllCharts();
            }, 250));
        }

        // Debounce function to limit resize events
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = function() {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Resize all charts when window is resized
        function resizeAllCharts() {
            try {
                Object.keys(charts).forEach(chartKey => {
                    if (charts[chartKey] && typeof charts[chartKey].resize === 'function') {
                        charts[chartKey].resize();
                    }
                });

                // Resize heat map if it exists
                if (heatMap && typeof heatMap.invalidateSize === 'function') {
                    setTimeout(() => {
                        heatMap.invalidateSize();
                    }, 100);
                }
            } catch (error) {
                console.error('Error resizing charts:', error);
            }
        }

        // Load initial data
        function loadInitialData() {
            // Load diseases list (top-N) for better UX first
            loadDiseases('');
            const filters = getCurrentFilters();
            fetchAnalyticsData(filters);
        }
        // Load diseases options (with search and top-N by frequency)
        function loadDiseases(query) {
            const select = document.getElementById('diseaseFilter');
            if (!select) return;

            const params = new URLSearchParams();
            if (query) params.append('q', query);
            params.append('limit', '200');

            // Abort previous list request if any
            if (window.__diseaseListController) {
                try { window.__diseaseListController.abort(); } catch (_) {}
            }
            window.__diseaseListController = new AbortController();

            fetch(`/api/diseases/available-diseases?${params}`, { signal: window.__diseaseListController.signal })
                .then(r => r.json())
                .then(json => {
                    if (!json.success) return;
                    const selected = select.value;
                    // Rebuild options
                    select.innerHTML = '<option value="">All Diseases</option>';
                    (json.diseases || []).forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        const label = d.code ? `${d.code} - ${d.name}` : d.name;
                        opt.textContent = label;
                        select.appendChild(opt);
                    });
                    // Restore selection if still present
                    if (selected && [...select.options].some(o => o.value === selected)) {
                        select.value = selected;
                    }
                })
                .catch(err => {
                    if (err.name !== 'AbortError') console.error('Disease list fetch error:', err);
                });
        }

        // Force refresh all charts (useful for fixing rendering issues)
        function refreshAllCharts() {
            if (currentData) {
                console.log('Refreshing all charts...');
                updateAllCharts(currentData);
            }
        }

        // Check if charts are properly rendered and fix if needed
        function validateChartRendering() {
            setTimeout(() => {
                const chartContainers = ['monthlyChart', 'genderChart', 'ageGroupChart', 'economicChart', 'districtChart'];
                let needsRefresh = false;

                chartContainers.forEach(containerId => {
                    const container = document.getElementById(containerId);
                    if (container) {
                        const svg = container.querySelector('svg');
                        if (!svg || svg.clientWidth === 0 || svg.clientHeight === 0) {
                            console.warn(`Chart ${containerId} not properly rendered`);
                            needsRefresh = true;
                        }
                    }
                });

                if (needsRefresh && currentData) {
                    console.log('Charts need refresh, re-rendering...');
                    setTimeout(() => refreshAllCharts(), 500);
                }
            }, 2000);
        }

        // Get current filter values
        function getCurrentFilters() {
            const diseaseSelect = document.getElementById('diseaseFilter');
            const selectedDisease = diseaseSelect.value;

            return {
                diseases: selectedDisease ? [selectedDisease] : [], // Convert single selection to array for API compatibility
                year_start: document.getElementById('yearStart').value,
                year_end: document.getElementById('yearEnd').value
            };
        }

        // Apply filters
        function applyFilters() {
            const filters = getCurrentFilters();
            fetchAnalyticsData(filters);
        }

        // Reset filters
        function resetFilters() {
            document.getElementById('diseaseFilter').value = ''; // Reset to "All Diseases"
            document.getElementById('yearStart').value = '{{ $startYear }}';
            document.getElementById('yearEnd').value = '{{ $currentYear }}';
            loadInitialData();
        }

        // Fetch analytics data from API
        function fetchAnalyticsData(filters) {
            showLoading(true);

            const params = new URLSearchParams();

            // Add filters to params
            if (filters.diseases && filters.diseases.length > 0) {
                filters.diseases.forEach(disease => params.append('diseases[]', disease));
            }
            if (filters.year_start) params.append('year_start', filters.year_start);
            if (filters.year_end) params.append('year_end', filters.year_end);

            // Cancel any prior in-flight analytics request to keep UI snappy
            if (window.__diseaseFetchController) { try { window.__diseaseFetchController.abort(); } catch (_) {} }
            window.__diseaseFetchController = new AbortController();
            fetch(`/api/diseases/analytics-data?${params}`, { signal: window.__diseaseFetchController.signal })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        currentData = data.charts;
                        updateAllCharts(data.charts);
                        updateSummaryTable(data.charts);
                        updateChartTitles(filters);
                        showSuccess('Analytics data updated successfully');

                        // Validate chart rendering after a short delay
                        validateChartRendering();
                    } else {
                        console.error('API Error:', data);
                        showError('Failed to load analytics data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    if (error.name !== 'AbortError') {
                        console.error('Fetch Error:', error);
                        showError('Network error: ' + error.message);
                    }
                })
                .finally(() => {
                    showLoading(false);
                });
        }

        // Show/hide loading indicator
        function showLoading(show) {
            const indicator = document.getElementById('loadingIndicator');
            if (show) {
                indicator.classList.remove('d-none');
            } else {
                indicator.classList.add('d-none');
            }
        }

        // Show error message
        function showError(message) {
            // Create a toast notification
            const toast = document.createElement('div');
            toast.className = 'alert alert-danger alert-dismissible fade show position-fixed';
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                <strong>Error:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(toast);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        }

        // Show success message
        function showSuccess(message) {
            const toast = document.createElement('div');
            toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                <strong>Success:</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 3000);
        }

        // Update all charts with new data
        function updateAllCharts(data) {
            try {
                // Check if ApexCharts is available
                if (typeof ApexCharts === 'undefined') {
                    console.warn('ApexCharts not available, using fallback display');
                    displayDataAsTables(data);
                    return;
                }

                // Update charts directly (no conflicts now)
                if (data.monthly_distribution) {
                    try { updateMonthlyChart(data.monthly_distribution); } catch (e) { console.error('Monthly chart error:', e); }
                }

                if (data.gender_distribution) {
                    try { updateGenderChart(data.gender_distribution); } catch (e) { console.error('Gender chart error:', e); }
                }

                if (data.age_group_distribution) {
                    try { updateAgeGroupChart(data.age_group_distribution); } catch (e) { console.error('Age chart error:', e); }
                }

                if (data.economic_status_distribution) {
                    try { updateEconomicChart(data.economic_status_distribution); } catch (e) { console.error('Economic chart error:', e); }
                }

                if (data.district_distribution) {
                    try { updateDistrictChart(data.district_distribution); } catch (e) { console.error('District chart error:', e); }
                }

                if (data.heatmap_data && data.heatmap_data.length) {
                    try { updateHeatMap(data.heatmap_data); } catch (e) { console.error('Heat map error:', e); }
                }

            } catch (error) {
                console.error('Error updating charts:', error);
                showError('Failed to update charts, showing data in tables');
                displayDataAsTables(data);
            }
        }

        // Fallback: Display data as tables if charts fail
        function displayDataAsTables(data) {
            const chartContainers = [
                { id: 'monthlyChart', data: data.monthly_distribution, title: 'Monthly Distribution' },
                { id: 'genderChart', data: data.gender_distribution, title: 'Gender Distribution' },
                { id: 'ageGroupChart', data: data.age_group_distribution, title: 'Age Group Distribution' },
                { id: 'economicChart', data: data.economic_status_distribution, title: 'Economic Status' },
                { id: 'districtChart', data: data.district_distribution, title: 'District Distribution' }
            ];

            chartContainers.forEach(container => {
                const element = document.getElementById(container.id);
                if (element && container.data) {
                    let tableHtml = `<h6>${container.title}</h6><table class="table table-sm">`;
                    container.data.labels.forEach((label, index) => {
                        tableHtml += `<tr><td>${label}</td><td>${container.data.data[index] || 0}</td></tr>`;
                    });
                    tableHtml += '</table>';
                    element.innerHTML = tableHtml;
                }
            });

            // Handle heat map data
            const heatMapElement = document.getElementById('heatMap');
            if (heatMapElement && data.heatmap_data) {
                let tableHtml = '<h6>District Heat Map Data</h6><table class="table table-sm">';
                tableHtml += '<tr><th>District</th><th>Cases</th><th>Population</th><th>Per Capita</th></tr>';
                data.heatmap_data.forEach(point => {
                    tableHtml += `<tr><td>${point.district}</td><td>${point.cases}</td><td>${point.population.toLocaleString()}</td><td>${point.cases_per_capita}</td></tr>`;
                });
                tableHtml += '</table>';
                heatMapElement.innerHTML = tableHtml;
            }
        }

        // Update monthly distribution chart
        function updateMonthlyChart(data) {
            const options = {
                chart: {
                    type: 'bar',
                    height: 400, // Increased to match row height
                    toolbar: { show: false }
                },
                series: [{
                    name: 'Cases',
                    data: data.data || []
                }],
                xaxis: {
                    categories: data.labels || [],
                    title: { text: 'Month' }
                },
                yaxis: {
                    title: { text: 'Number of Cases' }
                },
                colors: ['#022b70'],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '70%'
                    }
                },
                tooltip: {
                    y: { formatter: val => `${val} cases` }
                }
            };

            if (charts.monthly) {
                charts.monthly.destroy();
            }
            charts.monthly = new ApexCharts(document.querySelector("#monthlyChart"), options);
            charts.monthly.render();
        }

        // Update gender distribution chart
        function updateGenderChart(data) {
            const options = {
                chart: {
                    type: 'pie',
                    height: 400 // Increased to match monthly chart height
                },
                series: data.data || [],
                labels: data.labels || [],
                colors: ['#022b70', '#e91e63'],
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    y: { formatter: val => `${val} cases` }
                }
            };

            if (charts.gender) {
                charts.gender.destroy();
            }
            charts.gender = new ApexCharts(document.querySelector("#genderChart"), options);
            charts.gender.render();
        }

        // Update age group chart
        function updateAgeGroupChart(data) {
            const options = {
                chart: {
                    type: 'bar',
                    height: 380, // Consistent height for row 2
                    toolbar: { show: false }
                },
                series: [{
                    name: 'Cases',
                    data: data.data || []
                }],
                xaxis: {
                    categories: data.labels || [],
                    title: { text: 'Age Group' }
                },
                yaxis: {
                    title: { text: 'Number of Cases' }
                },
                colors: ['#022b70'],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '70%'
                    }
                },
                tooltip: {
                    y: { formatter: val => `${val} cases` }
                }
            };

            if (charts.ageGroup) {
                charts.ageGroup.destroy();
            }
            charts.ageGroup = new ApexCharts(document.querySelector("#ageGroupChart"), options);
            charts.ageGroup.render();
        }

        // Update economic status chart (Low vs High only)
        function updateEconomicChart(raw) {
            // Normalize incoming data to exactly two buckets: Low and High
            const labels = (raw && raw.labels) ? raw.labels : [];
            const values = (raw && raw.data) ? raw.data : [];
            let low = 0, high = 0;
            labels.forEach((lbl, idx) => {
                const val = Number(values[idx] || 0);
                const norm = String(lbl || '').trim().toLowerCase();
                if (norm === 'high') high += val; else low += val; // collapse anything not 'high' into low
            });
            const series = [low, high];
            const finalLabels = ['Low', 'High'];

            const options = {
                chart: {
                    type: 'donut',
                    height: 380
                },
                series: series,
                labels: finalLabels,
                colors: ['#dc3545', '#16a34a'], // red for Low, green for High
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    y: { formatter: val => `${val} cases` }
                }
            };

            if (charts.economic) {
                charts.economic.destroy();
            }
            charts.economic = new ApexCharts(document.querySelector('#economicChart'), options);
            charts.economic.render();
        }

        // Update district chart
        function updateDistrictChart(data) {
            const options = {
                chart: {
                    type: 'bar',
                    height: 400,
                    toolbar: { show: false }
                },
                series: [{
                    name: 'Cases',
                    data: data.data || []
                }],
                xaxis: {
                    categories: data.labels || [],
                    title: { text: 'District' },
                    labels: {
                        rotate: -45,
                        style: { fontSize: '12px' }
                    }
                },
                yaxis: {
                    title: { text: 'Number of Cases' }
                },
                colors: ['#022b70'],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '60%'
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '11px',
                        colors: ['#022b70']
                    },
                    offsetY: -20
                },
                tooltip: {
                    y: { formatter: val => `${val} cases` }
                }
            };

            if (charts.district) {
                charts.district.destroy();
            }
            charts.district = new ApexCharts(document.querySelector("#districtChart"), options);
            charts.district.render();
        }



        // Global heat layer variable
        let currentHeatLayer = null;
        let currentMarkersLayer = null;

        // Update professional heat map with realistic district coverage
        function updateHeatMap(data) {
            if (!heatMap) return;

            // Clear existing layers
            if (currentHeatLayer) {
                heatMap.removeLayer(currentHeatLayer);
            }
            if (currentMarkersLayer) {
                heatMap.removeLayer(currentMarkersLayer);
            }

            const perCapita = document.getElementById('perCapitaToggle').checked;

            // Prepare heat map data points with realistic district coverage
            const heatPoints = [];
            const maxValue = Math.max(...data.map(p => perCapita ? p.cases_per_capita : p.cases));

            data.forEach(point => {
                const value = perCapita ? point.cases_per_capita : point.cases;
                const intensity = Math.max(0.1, value / maxValue);

                // Generate realistic district heat distribution
                const districtHeatPoints = generateDistrictHeatPoints(point, intensity, maxValue);
                heatPoints.push(...districtHeatPoints);
            });

            // Create heat layer with realistic area coverage
            currentHeatLayer = L.heatLayer(heatPoints, {
                radius: 25,    // Optimized for district coverage
                blur: 15,      // Less blur for more defined areas
                maxZoom: 18,
                max: 1.2,      // Allow for higher intensity in urban areas
                minOpacity: 0.1,
                gradient: {
                    0.0: 'rgba(49, 54, 149, 0)',      // Transparent blue (very low)
                    0.1: 'rgba(49, 54, 149, 0.4)',    // Semi-transparent blue
                    0.2: 'rgba(69, 117, 180, 0.5)',   // Blue
                    0.3: 'rgba(116, 173, 209, 0.6)',  // Light blue
                    0.4: 'rgba(171, 217, 233, 0.7)',  // Very light blue
                    0.5: 'rgba(255, 255, 204, 0.8)',  // Light yellow
                    0.6: 'rgba(254, 217, 118, 0.85)', // Yellow
                    0.7: 'rgba(254, 178, 76, 0.9)',   // Orange
                    0.8: 'rgba(253, 141, 60, 0.95)',  // Dark orange
                    0.9: 'rgba(252, 78, 42, 1.0)',    // Red-orange
                    1.0: 'rgba(227, 26, 28, 1.0)'     // Deep red (high)
                }
            }).addTo(heatMap);

            // Add district markers for reference
            currentMarkersLayer = L.layerGroup();

            data.forEach(point => {
                const value = perCapita ? point.cases_per_capita : point.cases;
                const intensity = value / maxValue;

                // Create subtle district markers
                const marker = L.circleMarker([point.lat, point.lng], {
                    radius: 6,
                    fillColor: '#ffffff',
                    color: '#333333',
                    weight: 2,
                    opacity: 0.8,
                    fillOpacity: 0.9
                });

                // Enhanced popup with better styling
                const popupContent = `
                    <div style="font-family: Arial, sans-serif; min-width: 200px;">
                        <h6 style="margin: 0 0 10px 0; color: #333; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                            <strong>${point.district} District</strong>
                        </h6>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 13px;">
                            <div><strong>Total Cases:</strong></div>
                            <div style="color: #e31a1c; font-weight: bold;">${point.cases.toLocaleString()}</div>

                            <div><strong>Population:</strong></div>
                            <div>${point.population.toLocaleString()}</div>

                            <div><strong>Per 100k:</strong></div>
                            <div style="color: #fd8d3c; font-weight: bold;">${point.cases_per_capita}</div>

                            <div><strong>Intensity:</strong></div>
                            <div style="color: ${getIntensityColor(intensity)}; font-weight: bold;">
                                ${(intensity * 100).toFixed(1)}%
                            </div>
                        </div>
                    </div>
                `;

                marker.bindPopup(popupContent, {
                    maxWidth: 250,
                    className: 'custom-popup'
                });

                currentMarkersLayer.addLayer(marker);
            });

            currentMarkersLayer.addTo(heatMap);

            // Add legend
            addHeatMapLegend(perCapita, maxValue);
        }

        // Get realistic district radius based on actual district sizes
        function getDistrictRadius(districtName) {
            // Approximate district sizes in decimal degrees (based on Zanzibar geography)
            const districtSizes = {
                'Micheweni': 0.08,      // Large rural district
                'Chake Chake': 0.06,    // Medium district
                'Mkoani': 0.07,         // Large district
                'Wete': 0.05,           // Medium district
                'Konde': 0.04,          // Smaller district
                'Chakechake': 0.06,     // Medium district
                'Mkoani': 0.07,         // Large district
                'Vitongoji': 0.03,      // Small district
                'Donge Mchangani': 0.04, // Small district
                'Donge Mbiji': 0.03,    // Small district
                'Fujoni': 0.03          // Small district
            };

            return districtSizes[districtName] || 0.05; // Default medium size
        }

        // Get color for intensity display
        function getIntensityColor(intensity) {
            if (intensity < 0.3) return '#4575b4';
            if (intensity < 0.6) return '#fed976';
            if (intensity < 0.8) return '#fd8d3c';
            return '#e31a1c';
        }

        // Add realistic population density simulation
        function generateDistrictHeatPoints(point, intensity, maxValue) {
            const heatPoints = [];
            const districtRadius = getDistrictRadius(point.district);

            // Calculate number of points based on intensity and district size
            const basePoints = Math.floor(districtRadius * 1000); // More points for larger districts
            const intensityPoints = Math.floor(intensity * 100);
            const totalPoints = Math.min(basePoints + intensityPoints, 200); // Cap at 200 points

            // Generate points with realistic distribution patterns
            for (let i = 0; i < totalPoints; i++) {
                let lat, lng, pointIntensity;

                if (i < totalPoints * 0.4) {
                    // 40% concentrated in urban center (higher density)
                    const angle = Math.random() * 2 * Math.PI;
                    const distance = Math.random() * (districtRadius * 0.3);
                    lat = point.lat + Math.cos(angle) * distance;
                    lng = point.lng + Math.sin(angle) * distance;
                    pointIntensity = intensity * (0.8 + Math.random() * 0.4); // 80-120% of base intensity
                } else if (i < totalPoints * 0.7) {
                    // 30% in suburban areas (medium density)
                    const angle = Math.random() * 2 * Math.PI;
                    const distance = (districtRadius * 0.3) + Math.random() * (districtRadius * 0.4);
                    lat = point.lat + Math.cos(angle) * distance;
                    lng = point.lng + Math.sin(angle) * distance;
                    pointIntensity = intensity * (0.5 + Math.random() * 0.3); // 50-80% of base intensity
                } else {
                    // 30% in rural areas (lower density)
                    const angle = Math.random() * 2 * Math.PI;
                    const distance = (districtRadius * 0.7) + Math.random() * (districtRadius * 0.3);
                    lat = point.lat + Math.cos(angle) * distance;
                    lng = point.lng + Math.sin(angle) * distance;
                    pointIntensity = intensity * (0.2 + Math.random() * 0.3); // 20-50% of base intensity
                }

                heatPoints.push([lat, lng, Math.max(0.1, pointIntensity)]);
            }

            return heatPoints;
        }

        // Add professional heat map legend
        function addHeatMapLegend(perCapita, maxValue) {
            // Remove existing legend
            const existingLegend = document.querySelector('.heat-legend');
            if (existingLegend) {
                existingLegend.remove();
            }

            // Create legend
            const legend = L.control({position: 'bottomright'});

            legend.onAdd = function (map) {
                const div = L.DomUtil.create('div', 'heat-legend');
                const unit = perCapita ? 'per 100k' : 'cases';
                const title = perCapita ? 'Cases per 100k Population' : 'Total Cases';

                div.innerHTML = `
                    <div style="background: rgba(255,255,255,0.95); padding: 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); font-family: Arial, sans-serif;">
                        <h6 style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #333;">${title}</h6>
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <div style="width: 150px; height: 20px; background: linear-gradient(to right,
                                #313695 0%, #4575b4 10%, #74add1 20%, #abd9e9 30%, #e0f3f8 40%,
                                #ffffcc 50%, #fed976 60%, #feb24c 70%, #fd8d3c 80%, #fc4e2a 90%, #e31a1c 100%);
                                border: 1px solid #ccc; border-radius: 3px;">
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 11px; color: #666;">
                            <span>Low</span>
                            <span>${Math.round(maxValue/2)} ${unit}</span>
                            <span>${Math.round(maxValue)} ${unit}</span>
                        </div>
                        <div style="margin-top: 10px; font-size: 11px; color: #888; border-top: 1px solid #eee; padding-top: 8px;">
                            <div>🔵 District Centers</div>
                            <div style="margin-top: 3px;">Click markers for details</div>
                        </div>
                    </div>
                `;

                return div;
            };

            legend.addTo(heatMap);
        }

        // Enhanced color function for better gradients
        function getHeatColor(intensity) {
            // Professional color scale matching the heat layer
            if (intensity <= 0.1) return '#313695';
            if (intensity <= 0.2) return '#4575b4';
            if (intensity <= 0.3) return '#74add1';
            if (intensity <= 0.4) return '#abd9e9';
            if (intensity <= 0.5) return '#ffffcc';
            if (intensity <= 0.6) return '#fed976';
            if (intensity <= 0.7) return '#feb24c';
            if (intensity <= 0.8) return '#fd8d3c';
            if (intensity <= 0.9) return '#fc4e2a';
            return '#e31a1c';
        }

        // Update summary table
        function updateSummaryTable(data) {
            // This would typically be updated with more detailed data from the API
            // For now, we'll keep the existing table as it shows the basic disease summary
            console.log('Summary table update - data available:', data);
        }

        // Update chart titles based on selected disease
        function updateChartTitles(filters) {
            const diseaseSelect = document.getElementById('diseaseFilter');
            const selectedOption = diseaseSelect.options[diseaseSelect.selectedIndex];
            const diseaseName = selectedOption && selectedOption.value ? selectedOption.text.split(' (')[0] : null;

            const titleSuffix = diseaseName ? ` - ${diseaseName}` : ' - All Diseases';

            // Update card titles
            const titles = [
                { selector: '.card:has(#monthlyChart) .card-title', text: 'Monthly Distribution' + titleSuffix },
                { selector: '.card:has(#genderChart) .card-title', text: 'Cases by Gender' + titleSuffix },
                { selector: '.card:has(#ageGroupChart) .card-title', text: 'Cases by Age Group' + titleSuffix },
                { selector: '.card:has(#economicChart) .card-title', text: 'Cases by Economic Status' + titleSuffix },
                { selector: '.card:has(#districtChart) .card-title', text: 'Cases by District' + titleSuffix }
            ];

            titles.forEach(title => {
                const element = document.querySelector(title.selector);
                if (element) {
                    element.textContent = title.text;
                }
            });
        }

        })(); // End of isolation wrapper
    </script>
@endpush