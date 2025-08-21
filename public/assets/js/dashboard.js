/**
 * Dynamic Dashboard JavaScript
 * Handles real-time data updates and time period filtering
 */

(function() {
    'use strict';

    let currentPeriod = '1m';
    let dashboardData = null;

    // Initialize dashboard when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
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
            'Today': 'today',
            '7d': '7d',
            '2w': '2w',
            '1m': '1m',
            '3m': '3m',
            '6m': '6m',
            '1y': '1y'
        };
        
        return periodMap[text] || '1m';
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
        // Update Total Patients
        updateMetricCard('.col-xl-3:nth-child(1) .card', metrics.total_patients);

        // Update Health Facilities
        updateMetricCard('.col-xl-3:nth-child(2) .card', metrics.health_facilities);

        // Update Prescriptions (was "Medication")
        updateMetricCard('.col-xl-3:nth-child(3) .card', metrics.prescriptions, 'Prescriptions');

        // Update Disease Cases (was "Chronic disease")
        updateMetricCard('.col-xl-3:nth-child(4) .card', metrics.disease_cases, 'Disease Cases');
    }

    /**
     * Update individual metric card
     */
    function updateMetricCard(selector, metric, customLabel = null) {
        const card = document.querySelector(selector);
        if (!card) return;
        
        // Update value
        const valueElement = card.querySelector('h2');
        if (valueElement) {
            valueElement.textContent = metric.value;
        }
        
        // Update label if provided
        if (customLabel) {
            const labelElement = card.querySelector('p');
            if (labelElement) {
                labelElement.textContent = customLabel;
            }
        }
        
        // Update change percentage
        const changeElement = card.querySelector('.text-end p');
        if (changeElement) {
            changeElement.textContent = metric.change;
            
            // Update color based on trend
            changeElement.className = `mb-0 text-${getTrendColor(metric.trend)}`;
        }
        
        // Update trend badge
        const badgeElement = card.querySelector('.badge');
        if (badgeElement) {
            const periodText = getPeriodText(currentPeriod);
            badgeElement.textContent = periodText;
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
            'today': 'today',
            '7d': 'last 7 days',
            '2w': 'last 2 weeks',
            '1m': 'this month',
            '3m': 'last 3 months',
            '6m': 'last 6 months',
            '1y': 'this year'
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
        if (window.ageDistributionChart && window.ageDistributionChart.updateSeries) {
            // Update the donut chart with new series data
            window.ageDistributionChart.updateSeries(data.series);

            // Update labels if needed
            if (data.labels) {
                window.ageDistributionChart.updateOptions({
                    labels: data.labels
                });
            }
        }
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
