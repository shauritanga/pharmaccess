@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <!-- Medication Chart -->
        <div class="card mb-4">
            <div class="card-header text-white d-flex justify-content-between align-items-center"
                style="background-color: #022b70;">
                <h6 class="mb-0">Medication Demand Forecast ({{ $selectedYear }})</h6>
            </div>
            <div class="card-body">
                <div id="medicationChart"></div>
            </div>
        </div>

        <!-- Medication Table -->
        <div class="card">
            <div class="card-header text-white d-flex justify-content-between align-items-center"
                style="background-color: #022b70;">
                <h6 class="mb-0">Medication Demand Data</h6>

                <div class="d-flex gap-2">
                    <!-- Search input -->
                    <input type="text" id="medicineSearch" class="form-control form-control-sm"
                        placeholder="Search medicine...">

                    <!-- Year dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="yearDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Year: <span id="selectedYear">{{ $selectedYear }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="yearDropdown">
                            @foreach ($availableYears as $year)
                                <li><a class="dropdown-item year-option" href="#" data-year="{{ $year }}">{{ $year }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center" id="medicationTable">
                        <thead class="table-light">
                            <tr>
                                <th>Medicine</th>
                                @foreach ($months as $month)
                                    <th>{{ $month }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($medications as $medicine)
                                <tr>
                                    <td>{{ $medicine }}</td>
                                    @foreach ($medicationData[$medicine] as $value)
                                        <td>{{ $value }}</td>
                                    @endforeach
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
    <!-- Include ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        // Chart for top 3 medications only (or change as needed)
        var options = {
            chart: {
                type: 'bar',
                height: 350
            },
            colors: ['#022b70', '#7d0c0e', '#2662ed'],
            series: [
                {
                    name: 'Paracetamol',
                    data: @json($medicationData['Paracetamol'] ?? array_fill(0, 12, 0))
                },
                {
                    name: 'Amoxicillin',
                    data: @json($medicationData['Amoxicillin'] ?? array_fill(0, 12, 0))
                },
                {
                    name: 'Insulin (Forecast)',
                    data: @json($medicationData['Insulin'] ?? array_fill(0, 12, 0))
                }
            ],
            xaxis: {
                categories: @json($months)
            }
        };

        var chart = new ApexCharts(document.querySelector("#medicationChart"), options);
        chart.render();

        // Search filter
        document.getElementById('medicineSearch').addEventListener('input', function () {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('#medicationTable tbody tr');

            rows.forEach(row => {
                const medicine = row.querySelector('td').textContent.toLowerCase();
                row.style.display = medicine.includes(query) ? '' : 'none';
            });
        });

        // Year selection
        document.querySelectorAll('.year-option').forEach(item => {
            item.addEventListener('click', function (e) {
                e.preventDefault();
                const year = this.dataset.year;
                window.location.href = `?year=${year}`;
            });
        });
    </script>
@endsection