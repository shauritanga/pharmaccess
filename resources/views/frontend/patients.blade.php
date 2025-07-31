@extends('layoutS.app')

@section('content')
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Total Patients Per Month</h5>
        </div>
        <div class="card-body">
            <div id="patientsChart" style="width: 100%; height: 400px;"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        var patientsOptions = {
            chart: {
                type: 'line',
                height: 400,
                toolbar: { show: false }
            },
            series: [{
                name: 'Total Patients',
                data: @json($patientData['totals'])
            }],
            xaxis: {
                categories: @json($patientData['months']),
                title: { text: 'Month' }
            },
            yaxis: {
                title: { text: 'Number of Patients' }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            markers: {
                size: 5,
                colors: ['#1E90FF'],
                strokeColors: '#fff',
                strokeWidth: 2,
            },
            colors: ['#1E90FF'],
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " patients";
                    }
                }
            }
        };

        var patientsChart = new ApexCharts(document.querySelector("#patientsChart"), patientsOptions);
        patientsChart.render();
    </script>
@endsection