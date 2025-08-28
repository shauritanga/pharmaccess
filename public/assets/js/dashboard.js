/**
 * Dynamic Dashboard JavaScript
 * Handles real-time data updates and time period filtering
 */

(function() {
    'use strict';

    let currentPeriod = 'this_year';
    let dashboardData = null;

    // Initialize dashboard when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        initializeDashboard();
        setupEventListeners();
    });

    /**
     * Initialize dashboard with default data
     */
    function initializeDashboard() {
        console.log('Initializing dynamic dashboard...');
        loadDashboardData(currentPeriod);
    }

    /**
     * Setup event listeners for time period buttons
     */
    function setupEventListeners() {
        const periodButtons = document.querySelectorAll('.day-sorting button');
        
        periodButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                periodButtons.forEach(btn => btn.classList.remove('btn-primary'));
                periodButtons.forEach(btn => btn.classList.add('btn-sm'));
                
                // Add active class to clicked button
                this.classList.add('btn-primary');
                this.classList.remove('btn-sm');
                
                // Get period from button text
                const period = getPeriodFromButtonText(this.textContent.trim());
                currentPeriod = period;
                
                // Load new data
                loadDashboardData(period);
            });
        });
    }

    /**
     * Convert button text to period code
     */
    function getPeriodFromButtonText(text) {
        const periodMap = {
            'This Year': 'this_year',
            '2 Years': '2y',
            '3 Years': '3y',
            'Since 2020': 'since_2020'
        };

        return periodMap[text] || 'this_year';
    }

    /**
     * Load dashboard data from API
     */
    function loadDashboardData(period) {
        console.log(`Loading dashboard data for period: ${period}`);
        
        // Show loading state
        showLoadingState();
        
        fetch(`/api/dashboard?period=${period}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    dashboardData = data.data;
                    updateDashboard(data.data);
                    console.log('Dashboard data loaded successfully');
                } else {
                    console.error('Failed to load dashboard data:', data.message);
                    showError('Failed to load dashboard data');
                }
            })
            .catch(error => {
                console.error('Dashboard API error:', error);
                showError('Failed to connect to dashboard API');
            })
            .finally(() => {
                hideLoadingState();
            });
    }

    /**
     * Update dashboard with new data
     */
    function updateDashboard(data) {
        updateMetrics(data.metrics);
        updateCharts(data.charts);
    }

    /**
     * Update metric cards with new data
     */
    function updateMetrics(metrics) {
        // Update metric values by ID to avoid brittle selectors
        setMetric('#metric-total-patients', metrics.total_patients);
        setMetric('#metric-health-facilities', metrics.health_facilities);
        setMetric('#metric-prescriptions', metrics.prescriptions, '#label-prescriptions', 'Prescriptions');
        setMetric('#metric-disease-cases', metrics.disease_cases, '#label-disease-cases', 'Disease Cases');
    }

    /**
     * Update metric by IDs
     */
    function setMetric(valueSelector, metric, labelSelector = null, customLabel = null) {
        const valueEl = document.querySelector(valueSelector);
        if (valueEl) valueEl.textContent = metric?.value ?? '--';
        if (labelSelector && customLabel) {
            const labelEl = document.querySelector(labelSelector);
            if (labelEl) labelEl.textContent = customLabel;
        }
    }

    /**
     * Initialize Apex charts if not already present
     */
    function initCharts() {
        // Top Diseases (bar)
        if (!window.topDiseasesChart) {
            const el = document.querySelector('#topDiseases');
            if (el) {
                window.topDiseasesChart = new ApexCharts(el, {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    series: [{ data: [] }],
                    xaxis: { categories: [] }
                });
                window.topDiseasesChart.render();
            }
        }

        // Medication Trends (line)
        if (!window.medicationTrendsChart) {
            const el = document.querySelector('#treatment');
            if (el) {
                window.medicationTrendsChart = new ApexCharts(el, {
                    chart: { type: 'line', height: 300, toolbar: { show: false } },
                    series: [],
                    xaxis: { categories: [] }
                });
                window.medicationTrendsChart.render();
            }
        }

        // Chronic Diseases (bar)
        if (!window.chronicDiseasesChart) {
            const el = document.querySelector('#claims');
            if (el) {
                window.chronicDiseasesChart = new ApexCharts(el, {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    series: [{ data: [] }],
                    xaxis: { categories: [] }
                });
                window.chronicDiseasesChart.render();
            }
        }

        // Facility Performance (bubble)
        if (!window.facilityPerformanceChart) {
            const el = document.querySelector('#facility');
            if (el) {
                window.facilityPerformanceChart = new ApexCharts(el, {
                    chart: { type: 'bubble', height: 300, toolbar: { show: false } },
                    series: [{ data: [] }],
                    xaxis: { title: { text: 'Quality' } },
                    yaxis: { title: { text: 'Outcome' } }
                });
                window.facilityPerformanceChart.render();
            }
        }

        // Disease Heatmap
        if (!window.diseaseHeatmapChart) {
            const el = document.querySelector('#diseaseHeatmap');
            if (el) {
                window.diseaseHeatmapChart = new ApexCharts(el, {
                    chart: { type: 'heatmap', height: 300, toolbar: { show: false } },
                    series: [],
                    xaxis: { categories: [] }
                });
                window.diseaseHeatmapChart.render();
            }
        }

        // Gender & Age line chart placeholder (optional)
        if (!window.genderAgeChart) {
            const el = document.querySelector('#genderAge');
            if (el) {
                window.genderAgeChart = new ApexCharts(el, {
                    chart: { type: 'line', height: 300, toolbar: { show: false } },
                    series: [],
                    xaxis: { categories: [] }
                });
                window.genderAgeChart.render();
            }
        }

        // Age Distribution (bar)
        if (!window.ageDistributionChart) {
            const el = document.querySelector('#ageDistribution');
            if (el) {
                window.ageDistributionChart = new ApexCharts(el, {
                    chart: { type: 'bar', height: 300, toolbar: { show: false } },
                    series: [{ name: 'Patients', data: [] }],
                    xaxis: { categories: [] }
                });
                window.ageDistributionChart.render();
            }
        }
    }

    /**
     * Get color class based on trend
     */
    function getTrendColor(trend) {
        switch (trend) {
            case 'up': return 'success';
            case 'down': return 'danger';
            case 'stable': return 'primary';
            default: return 'secondary';
        }
    }

    /**
     * Get human-readable period text
     */
    function getPeriodText(period) {
        const periodTexts = {
            'this_year': 'this year',
            '2y': 'last 2 years',
            '3y': 'last 3 years',
            'since_2020': 'since 2020'
        };

        return periodTexts[period] || 'this period';
    }

    /**
     * Update charts with new data
     */
    function updateCharts(charts) {
        // Update top diseases chart
        if (charts.top_diseases && window.topDiseasesChart) {
            updateTopDiseasesChart(charts.top_diseases);
        }

        // Update medication trends chart
        if (charts.medication_trends && window.medicationTrendsChart) {
            updateMedicationTrendsChart(charts.medication_trends);
        }

        // Update chronic diseases chart
        if (charts.chronic_diseases && window.chronicDiseasesChart) {
            updateChronicDiseasesChart(charts.chronic_diseases);
        }

        // Update facility performance chart
        if (charts.facility_performance && window.facilityPerformanceChart) {
            updateFacilityPerformanceChart(charts.facility_performance);
        }

        // Update disease heatmap
        if (charts.disease_heatmap && window.diseaseHeatmapChart) {
            updateDiseaseHeatmapChart(charts.disease_heatmap);
        }

        // Update age distribution
        if (charts.age_distribution && window.ageDistributionChart) {
            updateAgeDistributionChart(charts.age_distribution);
        }
    }

    /**
     * Update top diseases chart
     */
    function updateTopDiseasesChart(data) {
        if (window.topDiseasesChart && window.topDiseasesChart.updateOptions) {
            window.topDiseasesChart.updateOptions({
                xaxis: {
                    categories: data.labels
                }
            });
            window.topDiseasesChart.updateSeries([{
                data: data.data
            }]);
        }
    }

    /**
     * Update medication trends chart
     */
    function updateMedicationTrendsChart(data) {
        if (window.medicationTrendsChart && window.medicationTrendsChart.updateOptions) {
            window.medicationTrendsChart.updateOptions({
                xaxis: {
                    categories: data.labels
                }
            });
            window.medicationTrendsChart.updateSeries(data.series);
        }
    }

    /**
     * Update chronic diseases chart
     */
    function updateChronicDiseasesChart(data) {
        if (window.chronicDiseasesChart && window.chronicDiseasesChart.updateOptions) {
            window.chronicDiseasesChart.updateOptions({
                xaxis: {
                    categories: data.labels
                }
            });
            window.chronicDiseasesChart.updateSeries([{
                data: data.data
            }]);
        }
    }

    /**
     * Update facility performance chart
     */
    function updateFacilityPerformanceChart(data) {
        if (window.facilityPerformanceChart && window.facilityPerformanceChart.updateSeries) {
            const seriesData = data.map(item => ({
                x: item.quality,
                y: item.outcome,
                z: item.cases
            }));

            window.facilityPerformanceChart.updateSeries([{
                data: seriesData
            }]);
        }
    }

    /**
     * Update disease heatmap chart
     */
    function updateDiseaseHeatmapChart(data) {
        if (window.diseaseHeatmapChart && window.diseaseHeatmapChart.updateSeries) {
            // Update the heatmap with new series data
            window.diseaseHeatmapChart.updateSeries(data.series);

            // Update x-axis categories if needed
            if (data.districts) {
                window.diseaseHeatmapChart.updateOptions({
                    xaxis: {
                        categories: data.districts
                    }
                });
            }
        }
    }

    /**
     * Update age distribution chart
     */
    function updateAgeDistributionChart(data) {
        if (!window.ageDistributionChart) return;
        const labels = data.labels || [];
        const series = [{ name: 'Patients', data: data.series || [] }];
        window.ageDistributionChart.updateOptions({ xaxis: { categories: labels } });
        window.ageDistributionChart.updateSeries(series);
    }

    /**
     * Show loading state
     */
    function showLoadingState() {
        // Add loading class to metric cards
        const cards = document.querySelectorAll('.card h2');
        cards.forEach(card => {
            card.style.opacity = '0.5';
        });
    }

    /**
     * Hide loading state
     */
    function hideLoadingState() {
        // Remove loading class from metric cards
        const cards = document.querySelectorAll('.card h2');
        cards.forEach(card => {
            card.style.opacity = '1';
        });
    }

    /**
     * Show error message
     */
    function showError(message) {
        console.error('Dashboard Error:', message);
        // You can implement a toast notification here
    }

    // Expose functions globally for chart integration
    window.dashboardManager = {
        getCurrentPeriod: () => currentPeriod,
        getCurrentData: () => dashboardData,
        refreshData: () => loadDashboardData(currentPeriod)
    };

})();
