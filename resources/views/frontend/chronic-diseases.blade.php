@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Chart Section -->
        <div class="card mb-4">
            <div class="card-header text-white" style="background-color: #022b70;">
                <h5 class="card-title mb-0">Chronic Diseases in Zanzibar</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Bar Chart -->
                    <div class="col-md-6 mb-4">
                        <div id="chronicDiseasesBarChart" style="width: 100%; height: 400px;"></div>
                    </div>

                    <!-- Pie Chart -->
                    <div class="col-md-6 mb-4">
                        <div id="chronicDiseasesPieChart" style="width: 100%; height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Table Section -->
        <div class="card">
            <div style="background-color: #022b70; color: white; padding: 1rem;" class="card-header text-white">
                <h6 class="mb-0">Chronic Disease Records</h6>
            </div>
            <div class="card-body">
                <input type="text" id="chronicSearchInput" class="form-control mb-3"
                    placeholder="Search by disease name or number...">

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Disease Name</th>
                                <th>Number of Cases</th>
                            </tr>
                        </thead>
                        <tbody id="chronicDiseaseTable">
                            @foreach ($chronicDiseases as $disease)
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        const chronicNames = @json(array_column($chronicDiseases, 'name'));
        const chronicCases = @json(array_column($chronicDiseases, 'cases'));

        // Bar Chart for Chronic Diseases
        const chronicBarOptions = {
            chart: {
                type: 'bar',
                height: 400,
                toolbar: { show: false }
            },
            series: [{
                name: 'Cases',
                data: chronicCases
            }],
            xaxis: {
                categories: chronicNames,
                title: { text: 'Disease' }
            },
            yaxis: {
                title: { text: 'Reported Cases' }
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

        const chronicBarChart = new ApexCharts(document.querySelector("#chronicDiseasesBarChart"), chronicBarOptions);
        chronicBarChart.render();

        // Pie Chart for Chronic Diseases
        const chronicPieOptions = {
            chart: {
                type: 'pie',
                height: 400
            },
            labels: chronicNames,
            series: chronicCases,
            colors: ['#022b70', '#7d0c0e', '#2662ed', '#4e91e7', '#aabbee', '#516cc3'],
            legend: { position: 'bottom' },
            tooltip: {
                y: {
                    formatter: val => `${val} cases`
                }
            }
        };

        const chronicPieChart = new ApexCharts(document.querySelector("#chronicDiseasesPieChart"), chronicPieOptions);
        chronicPieChart.render();
    </script>

    <script>
        // Filter chronic diseases table
        document.getElementById('chronicSearchInput').addEventListener('keyup', function () {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#chronicDiseaseTable tr');

            rows.forEach(row => {
                let name = row.cells[0].textContent.toLowerCase();
                let count = row.cells[1].textContent.toLowerCase();

                row.style.display = (name.includes(filter) || count.includes(filter)) ? '' : 'none';
            });
        });
    </script>
@endsection