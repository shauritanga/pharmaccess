@extends('layouts.app')

@section('content')
    <div class="app-container">

        <!-- App hero header starts -->
        <div class="app-hero-header d-flex align-items-center">

            <!-- Breadcrumb starts -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <i class="ri-capsule-line lh-1 pe-3 me-3 border-end"></i>
                    <a href="{{ route('medication') }}">Medication Analytics</a>
                </li>
                <li class="breadcrumb-item text-primary" aria-current="page">
                    Medication Usage & Trends
                </li>
            </ol>
            <!-- Breadcrumb ends -->

            <!-- Quick stats starts -->
            <div class="ms-auto d-lg-flex d-none flex-row">
                <div class="d-flex flex-row gap-3">
                    <div class="text-center">
                        <small class="text-muted">Total Medications</small>
                        <div class="fw-bold text-primary">{{ $availableMedications->count() }}</div>
                    </div>
                    <div class="text-center">
                        <small class="text-muted">Active Prescriptions</small>
                        <div class="fw-bold text-success">{{ number_format($totalPrescriptions ?? 0) }}</div>
                    </div>
                    <div class="text-center">
                        <small class="text-muted">Year Range</small>
                        <div class="fw-bold text-info">{{ $startYear }}-{{ $currentYear }}</div>
                    </div>
                </div>
            </div>
            <!-- Quick stats ends -->

        </div>
        <!-- App Hero header ends -->
        <!-- App body starts -->
        <div class="app-body">

            <!-- Filters Section -->
            <div class="row gx-3">
                <div class="col-12">
                    <div class="card mb-3 filter-section">
                        <div class="card-header">
                            <h5 class="card-title text-white">
                                <i class="ri-capsule-line me-2"></i>Medication Analytics Filters
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <!-- Medication Filter -->
                                <div class="col-lg-4 col-md-6">
                                    <label for="medicationFilter" class="form-label text-white">Select Medication</label>
                                    <select id="medicationFilter" class="form-select">
                                        <option value="">All Medications</option>
                                        @foreach($availableMedications as $medication)
                                            <option value="{{ $medication->id }}">{{ $medication->name }} ({{ ucfirst($medication->category) }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Year Range Filter -->
                                <div class="col-lg-2 col-md-3">
                                    <label for="yearStart" class="form-label text-white">Start Year</label>
                                    <select id="yearStart" class="form-select">
                                        @foreach($availableYears as $year)
                                            <option value="{{ $year }}" {{ $year == $startYear ? 'selected' : '' }}>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-3">
                                    <label for="yearEnd" class="form-label text-white">End Year</label>
                                    <select id="yearEnd" class="form-select">
                                        @foreach($availableYears as $year)
                                            <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="col-lg-4 col-md-12">
                                    <button id="applyFilters" class="btn btn-light me-2">
                                        <i class="ri-search-line"></i> Apply Filters
                                    </button>
                                    <button id="resetFilters" class="btn btn-outline-light me-2">
                                        <i class="ri-refresh-line"></i> Reset
                                    </button>
                                    <div id="loadingIndicator" class="spinner-border spinner-border-sm text-light d-none" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <!-- First Row: Monthly Trends (Line) and Gender Distribution -->
            <div class="row gx-3 chart-row-1">
                <!-- Monthly Prescription Trends Chart (Line Chart) -->
                <div class="col-xxl-8 col-lg-8 col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Monthly Prescription Trends</h5>
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
                            <h5 class="card-title">Prescriptions by Gender</h5>
                        </div>
                        <div class="card-body">
                            <div id="genderChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row: Age Group and Medication Category -->
            <div class="row gx-3 chart-row-2">
                <!-- Age Group Distribution Chart -->
                <div class="col-xxl-6 col-lg-6 col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Prescriptions by Age Group</h5>
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
                            <h5 class="card-title">Prescriptions by Economic Status</h5>
                        </div>
                        <div class="card-body">
                            <div id="economicChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Third Row: District Distribution - Full Width -->
            <div class="row gx-3 chart-row-3">
                <div class="col-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Prescriptions by District - All Zanzibar Districts</h5>
                        </div>
                        <div class="card-body">
                            <div id="districtChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Heat Map - Full Width -->
            <div class="row gx-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Geographic Heat Map - Medication Prescriptions Across Zanzibar</h5>
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
            </div>

        </div>
        <!-- App body ends -->
    </div>
@endsection

@push('styles')
    <!-- Include Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
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
            if (window.medicationAnalyticsScriptLoaded) {
                return;
            }
            window.medicationAnalyticsScriptLoaded = true;

            // Local variables (not global to prevent conflicts)
            let charts = {};
            let heatMap = null;
            let currentData = null;

            // API endpoints
            const API_BASE = '/api/medication-analytics';

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
                document.getElementById('perCapitaToggle').addEventListener('change', updateHeatMap);

                // Add medication selection change listener
                document.getElementById('medicationFilter').addEventListener('change', function() {
                    const filters = getCurrentFilters();
                    showLoading(true);
                    fetchAnalyticsData(filters);
                });

                // Add year selection change listeners
                document.getElementById('yearStart').addEventListener('change', function() {
                    const filters = getCurrentFilters();
                    showLoading(true);
                    fetchAnalyticsData(filters);
                });

                document.getElementById('yearEnd').addEventListener('change', function() {
                    const filters = getCurrentFilters();
                    showLoading(true);
                    fetchAnalyticsData(filters);
                });

                // Add window resize handler to fix chart rendering issues
                window.addEventListener('resize', debounce(function() {
                    resizeAllCharts();
                }, 250));
            }

            // Simple initialization like other pages
            document.addEventListener('DOMContentLoaded', function() {
                if (!document.getElementById('monthlyChart') || window.medicationAnalyticsInitialized) {
                    return;
                }

                window.medicationAnalyticsInitialized = true;

                console.log('Initializing medication analytics...');
                initializeHeatMap(); // Initialize heat map
                setupEventListeners();
                loadInitialData();
            });

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

            // Get current filter values
            function getCurrentFilters() {
                return {
                    medications: document.getElementById('medicationFilter').value ? [document.getElementById('medicationFilter').value] : [],
                    year_start: document.getElementById('yearStart').value,
                    year_end: document.getElementById('yearEnd').value
                };
            }

            // Apply filters
            function applyFilters() {
                const filters = getCurrentFilters();
                showLoading(true);
                fetchAnalyticsData(filters);
            }

            // Reset filters
            function resetFilters() {
                document.getElementById('medicationFilter').value = '';
                document.getElementById('yearStart').value = '{{ $startYear }}';
                document.getElementById('yearEnd').value = '{{ $currentYear }}';

                const filters = getCurrentFilters();
                showLoading(true);
                fetchAnalyticsData(filters);
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

            // Show success message
            function showSuccess(message) {
                // You can implement toast notifications here
                console.log('Success:', message);
            }

            // Show error message
            function showError(message) {
                // You can implement toast notifications here
                console.error('Error:', message);
            }

            // Load initial data
            function loadInitialData() {
                const filters = getCurrentFilters();
                fetchAnalyticsData(filters);
            }

            // Fetch analytics data from API
            function fetchAnalyticsData(filters) {
                const params = new URLSearchParams(filters);

                fetch(`${API_BASE}?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            currentData = data.charts;
                            updateAllCharts(data.charts);
                            updateChartTitles(filters);
                            showSuccess('Medication analytics updated successfully');
                        } else {
                            console.error('API Error:', data);
                            showError('Failed to load medication analytics: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        showError('Failed to fetch medication analytics data');
                    })
                    .finally(() => {
                        showLoading(false);
                    });
            }

            // Update chart titles based on selected medication
            function updateChartTitles(filters) {
                const medicationSelect = document.getElementById('medicationFilter');
                const selectedOption = medicationSelect.options[medicationSelect.selectedIndex];
                const medicationName = selectedOption && selectedOption.value ? selectedOption.text.split(' (')[0] : null;

                const titleSuffix = medicationName ? ` - ${medicationName}` : ' - All Medications';

                // Update card titles
                const titles = [
                    { selector: '.card:has(#monthlyChart) .card-title', text: 'Monthly Prescription Trends' + titleSuffix },
                    { selector: '.card:has(#genderChart) .card-title', text: 'Prescriptions by Gender' + titleSuffix },
                    { selector: '.card:has(#ageGroupChart) .card-title', text: 'Prescriptions by Age Group' + titleSuffix },
                    { selector: '.card:has(#economicChart) .card-title', text: 'Prescriptions by Economic Status' + titleSuffix },
                    { selector: '.card:has(#districtChart) .card-title', text: 'Prescriptions by District' + titleSuffix }
                ];

                titles.forEach(title => {
                    const element = document.querySelector(title.selector);
                    if (element) {
                        element.textContent = title.text;
                    }
                });
            }

            // Update all charts with new data
            function updateAllCharts(data) {
                try {
                    // Check if ApexCharts is available
                    if (typeof ApexCharts === 'undefined') {
                        console.warn('ApexCharts not available, using fallback display');
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

                    if (data.heatmap_data) {
                        try { updateHeatMap(data.heatmap_data); } catch (e) { console.error('Heat map error:', e); }
                    }

                } catch (error) {
                    console.error('Error updating charts:', error);
                    showError('Failed to update charts');
                }
            }

            // Update monthly trends chart (LINE CHART as requested)
            function updateMonthlyChart(data) {
                const container = document.getElementById('monthlyChart');
                if (!container) return;

                // Destroy existing chart
                if (charts.monthly) {
                    charts.monthly.destroy();
                }

                const options = {
                    chart: {
                        type: 'line', // LINE CHART for trends as requested
                        height: 400,
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: false,
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: false,
                                reset: true
                            }
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    series: [{
                        name: 'Prescriptions',
                        data: data.data || [],
                        color: '#667eea'
                    }],
                    xaxis: {
                        categories: data.labels || [],
                        title: {
                            text: 'Month/Year'
                        },
                        labels: {
                            rotate: -45,
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Number of Prescriptions'
                        },
                        labels: {
                            formatter: function(value) {
                                return Math.round(value).toLocaleString();
                            }
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 5,
                        colors: ['#667eea'],
                        strokeColors: '#fff',
                        strokeWidth: 2,
                        hover: {
                            size: 7
                        }
                    },
                    grid: {
                        borderColor: '#e7e7e7',
                        row: {
                            colors: ['#f3f3f3', 'transparent'],
                            opacity: 0.5
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value.toLocaleString() + ' prescriptions';
                            }
                        }
                    },
                    title: {
                        text: 'Monthly Prescription Trends',
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold'
                        }
                    }
                };

                charts.monthly = new ApexCharts(container, options);
                charts.monthly.render();
            }

            // Update gender distribution chart
            function updateGenderChart(data) {
                const container = document.getElementById('genderChart');
                if (!container) return;

                // Destroy existing chart
                if (charts.gender) {
                    charts.gender.destroy();
                }

                const options = {
                    chart: {
                        type: 'donut',
                        height: 400
                    },
                    series: data.data || [],
                    labels: data.labels || [],
                    colors: ['#3b82f6', '#ec4899'], // Blue for male, pink for female
                    legend: {
                        position: 'bottom',
                        fontSize: '14px'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        formatter: function(w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value.toLocaleString() + ' prescriptions';
                            }
                        }
                    },
                    title: {
                        text: 'Gender Distribution',
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold'
                        }
                    }
                };

                charts.gender = new ApexCharts(container, options);
                charts.gender.render();
            }

            // Update age group chart
            function updateAgeGroupChart(data) {
                const container = document.getElementById('ageGroupChart');
                if (!container) return;

                // Destroy existing chart
                if (charts.ageGroup) {
                    charts.ageGroup.destroy();
                }

                const options = {
                    chart: {
                        type: 'bar',
                        height: 380
                    },
                    series: [{
                        name: 'Prescriptions',
                        data: data.data || [],
                        color: '#10b981'
                    }],
                    xaxis: {
                        categories: data.labels || [],
                        title: {
                            text: 'Age Groups'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Number of Prescriptions'
                        },
                        labels: {
                            formatter: function(value) {
                                return Math.round(value).toLocaleString();
                            }
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: false
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value.toLocaleString() + ' prescriptions';
                            }
                        }
                    },
                    title: {
                        text: 'Age Group Distribution',
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold'
                        }
                    }
                };

                charts.ageGroup = new ApexCharts(container, options);
                charts.ageGroup.render();
            }

            // Update economic status chart
            function updateEconomicChart(data) {
                const container = document.getElementById('economicChart');
                if (!container) return;

                // Destroy existing chart
                if (charts.economic) {
                    charts.economic.destroy();
                }

                const options = {
                    chart: {
                        type: 'bar',
                        height: 380
                    },
                    series: [{
                        name: 'Prescriptions',
                        data: data.data || [],
                        color: '#f59e0b'
                    }],
                    xaxis: {
                        categories: data.labels || [],
                        title: {
                            text: 'Economic Status'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Number of Prescriptions'
                        },
                        labels: {
                            formatter: function(value) {
                                return Math.round(value).toLocaleString();
                            }
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: false
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value.toLocaleString() + ' prescriptions';
                            }
                        }
                    },
                    title: {
                        text: 'Economic Status Distribution',
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold'
                        }
                    }
                };

                charts.economic = new ApexCharts(container, options);
                charts.economic.render();
            }

            // Update district distribution chart
            function updateDistrictChart(data) {
                const container = document.getElementById('districtChart');
                if (!container) return;

                // Destroy existing chart
                if (charts.district) {
                    charts.district.destroy();
                }

                const options = {
                    chart: {
                        type: 'bar',
                        height: 400
                    },
                    series: [{
                        name: 'Prescriptions',
                        data: data.data || [],
                        color: '#6366f1'
                    }],
                    xaxis: {
                        categories: data.labels || [],
                        title: {
                            text: 'Districts'
                        },
                        labels: {
                            rotate: -45,
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Number of Prescriptions'
                        },
                        labels: {
                            formatter: function(value) {
                                return Math.round(value).toLocaleString();
                            }
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: false
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value.toLocaleString() + ' prescriptions';
                            }
                        }
                    },
                    title: {
                        text: 'District Distribution',
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold'
                        }
                    }
                };

                charts.district = new ApexCharts(container, options);
                charts.district.render();
            }

            // Global heat layer variables
            let currentHeatLayer = null;
            let currentMarkersLayer = null;

            // Update professional heat map with realistic district coverage
            function updateHeatMap(data) {
                if (!heatMap || !data) return;

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
                const maxValue = Math.max(...data.map(p => perCapita ? p.prescriptions_per_capita : p.prescriptions));

                data.forEach(point => {
                    const value = perCapita ? point.prescriptions_per_capita : point.prescriptions;
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
                    const value = perCapita ? point.prescriptions_per_capita : point.prescriptions;
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
                                <div><strong>Total Prescriptions:</strong></div>
                                <div style="color: #e31a1c; font-weight: bold;">${point.prescriptions.toLocaleString()}</div>

                                <div><strong>Population:</strong></div>
                                <div>${point.population.toLocaleString()}</div>

                                <div><strong>Per 100k:</strong></div>
                                <div style="color: #fd8d3c; font-weight: bold;">${point.prescriptions_per_capita}</div>

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
                    const unit = perCapita ? 'per 100k' : 'prescriptions';
                    const title = perCapita ? 'Prescriptions per 100k Population' : 'Total Prescriptions';

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

        })(); // End of IIFE
    </script>
@endpush