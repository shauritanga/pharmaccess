@extends('layouts.app')
@section('title', 'Health Facilities')

@section('content')
    <div class="app-container">
        <div class="app-body">
            <div class="row gx-3">
                <!-- Filters + Chart -->
                <div class="col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="card-title mb-0">Patient Attendance per Month</h5>
                            <select id="facilitySelect" class="form-select form-select-sm w-auto ms-auto"></select>
                            <select id="periodSelect" class="form-select form-select-sm w-auto">
                                <option value="this_year">This year</option>
                                <option value="2y">Last 2 years</option>
                                <option value="3y">Last 3 years</option>
                                <option value="since_2020" selected>Since 2020</option>
                            </select>
                        </div>
                        <div class="card-body">
                            <div class="chart-height-lg">
                                <div id="facilityAttendanceChart" class="auto-align-graph"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title">Health Facility List</h5>
                        </div>
                        <div class="card-body">
                            <!-- Search and Page Size -->
                            <div class="d-flex justify-content-between mb-3">
                                <input type="text" id="searchInput" class="form-control w-25" placeholder="Search...">
                                <select id="pageSize" class="form-select w-25">
                                    <option value="5">Show 5</option>
                                    <option value="10">Show 10</option>
                                    <option value="25">Show 25</option>
                                </select>
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle" id="hospitalTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Facility Name</th>
                                            <th>Contact Personnel</th>
                                            <th>Phone Number</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="delRowModal" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirm</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">Are you sure you want to delete this hospital?</div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">No</button>
                                            <button type="button" class="btn btn-danger" id="confirmDelete">Yes</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let attendanceChart;

        async function fetchFacilities() {
            const res = await fetch('{{ route('api.facilities.list') }}');
            const json = await res.json();
            if (!json.success) return [];
            return json.data || [];
        }

        async function fetchAttendance(facilityId, period) {
            const url = new URL('{{ route('api.facilities.attendance') }}', window.location.origin);
            url.searchParams.set('facility_id', facilityId);
            url.searchParams.set('period', period);
            const res = await fetch(url);
            return await res.json();
        }

        function initChart() {
            const el = document.querySelector('#facilityAttendanceChart');
            if (!el) return;
            attendanceChart = new ApexCharts(el, {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{ name: 'Attendance', data: [] }],
                xaxis: { categories: [] }
            });
            attendanceChart.render();
        }

        async function loadPage() {
            initChart();
            const facilities = await fetchFacilities();
            const select = document.getElementById('facilitySelect');
            select.innerHTML = facilities.map(f => `<option value="${f.id}">${f.name}</option>`).join('');

            select.addEventListener('change', refreshChart);
            document.getElementById('periodSelect').addEventListener('change', refreshChart);

            if (facilities.length) {
                await refreshChart();
            }
        }

        async function refreshChart() {
            const facilityId = document.getElementById('facilitySelect').value;
            const period = document.getElementById('periodSelect').value;
            const json = await fetchAttendance(facilityId, period);
            if (!json.success) return;
            const data = json.data;
            attendanceChart.updateOptions({ xaxis: { categories: data.labels } });
            attendanceChart.updateSeries(data.series);
        }

        document.addEventListener('DOMContentLoaded', loadPage);
    </script>
@endpush