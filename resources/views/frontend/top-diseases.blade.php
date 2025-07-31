@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Chart Section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Top Diseases in Zanzibar</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Bar Chart -->
                    <div class="col-md-6 mb-4">
                        <div id="topDiseasesChart" style="width: 100%; height: 400px;"></div>
                    </div>

                    <!-- Pie Chart -->
                    <div class="col-md-6 mb-4">
                        <div id="topDiseasesPieChart" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Table Section -->
        <div class="card">
            <div style="background-color: #022b70; color: white; padding: 1rem;" class="card-header text-white">
                <h6 class="mb-0">All Disease Records</h6>
            </div>
            <div class="card-body">
                <input type="text" id="searchInput" class="form-control mb-3"
                    placeholder="Search by disease name or number...">

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Disease Name</th>
                                <th>Number of Cases</th>
                            </tr>
                        </thead>
                        <tbody id="diseaseTable">
                            @foreach ($diseases as $disease)
                                <tr>
                                    <td>{{ $disease['name'] }}</td>
                                    <td>{{ $disease['cases'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Include ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        const diseaseNames = @json(array_column($diseases, 'name'));
        const diseaseCases = @json(array_column($diseases, 'cases'));

        // Bar Chart
        const barOptions = {
            chart: {
                type: 'bar',
                height: 400,
                toolbar: {
                    show: false
                }
            },
            series: [{
                name: 'Cases',
                data: diseaseCases
            }],
            xaxis: {
                categories: diseaseNames,
                title: {
                    text: 'Disease'
                }
            },
            yaxis: {
                title: {
                    text: 'Reported Cases'
                }
            },
            colors: ['#022b70'],
            plotOptions: {
                bar: {
                    distributed: true,
                    borderRadius: 6
                }
            },
            tooltip: {
                y: {
                    formatter: val => `${val} cases`
                }
            }
        };

        const barChart = new ApexCharts(document.querySelector("#topDiseasesChart"), barOptions);
        barChart.render();

        // Pie Chart
        const pieOptions = {
            chart: {
                type: 'pie',
                height: 400
            },
            labels: diseaseNames,
            series: diseaseCases,
            colors: ['#022b70', '#7d0c0e', '#2662ed', '#4e91e7', '#aabbee', '#516cc3'], // Add more if needed
            legend: {
                position: 'bottom'
            },
            tooltip: {
                y: {
                    formatter: val => `${val} cases`
                }
            }
        };

        const pieChart = new ApexCharts(document.querySelector("#topDiseasesPieChart"), pieOptions);
        pieChart.render();
    </script>

    <script>
        // Search Filter Script
        document.getElementById('searchInput').addEventListener('keyup', function () {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#diseaseTable tr');

            rows.forEach(row => {
                let name = row.cells[0].textContent.toLowerCase();
                let count = row.cells[1].textContent.toLowerCase();

                if (name.includes(filter) || count.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
@endsection