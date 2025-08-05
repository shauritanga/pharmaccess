@extends('layouts.app')
@section('title', 'Hospitals List')

@section('content')
    <div class="app-container">
        <div class="app-body">
            <div class="row gx-3">
                <!-- Charts -->
                <div class="col-sm-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Health Facilities</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-height-lg">
                                <div id="total-department" class="auto-align-graph"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Employees</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-height-lg">
                                <div id="employees" class="auto-align-graph"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title">Hospital List</h5>
                            <a href="{{ route('add-hospitals') }}" class="btn btn-primary ms-auto">Add Hospital</a>
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
                                            <th>Hospital Name</th>
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
        const defaultHospitals = [
            { id: 1, name: "Al Rahma Hospital", person: "Deena Cooley", img: "assets/images/user.png", phone: "+255 678 980 123" },
            { id: 2, name: "Tasakhtaa Global Hospital", person: "Hector Banks", img: "assets/images/user2.png", phone: "+255 678 980 123" },
            { id: 3, name: "Mnazi Mmoja Hospital", person: "Owen Scott", img: "assets/images/user3.png", phone: "+255 678 980 123" },
            { id: 4, name: "Dr.Mehta’s Hospital", person: "Alison Estrada", img: "assets/images/user5.png", phone: "+255 678 980 123" },
            { id: 5, name: "Tawakal Hospital", person: "Mitchel Alvarez", img: "assets/images/user4.png", phone: "+255 678 980 123" }
        ];

        function loadHospitals() {
            const data = localStorage.getItem('hospitals');
            return data ? JSON.parse(data) : defaultHospitals;
        }

        function saveHospitals(hospitals) {
            localStorage.setItem('hospitals', JSON.stringify(hospitals));
        }

        let hospitals = loadHospitals();
        let deleteHospitalId = null;

        function renderHospitals() {
            const tbody = document.querySelector('#hospitalTable tbody');
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const pageSize = parseInt(document.getElementById('pageSize').value);

            tbody.innerHTML = '';

            let filtered = hospitals.filter(h =>
                h.name.toLowerCase().includes(searchTerm) ||
                h.person.toLowerCase().includes(searchTerm)
            );

            filtered.slice(0, pageSize).forEach((h, index) => {
                const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${h.name}</td>
                                <td><img src="${h.img}" class="img-shadow img-2x rounded-5 me-1" alt="User">${h.person}</td>
                                <td>${h.phone}</td>
                                <td>
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-outline-danger btn-sm rounded-5 deleteBtn" data-id="${h.id}" data-bs-toggle="modal" data-bs-target="#delRowModal">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        <a href="{{ route('edit-hospitals') }}" class="btn btn-outline-success btn-sm rounded-5"><i class="ri-edit-box-line"></i></a>
                                    </div>
                                </td>
                            </tr>
                        `;
                tbody.innerHTML += row;
            });

            document.querySelectorAll('.deleteBtn').forEach(btn => {
                btn.addEventListener('click', function () {
                    deleteHospitalId = parseInt(this.getAttribute('data-id'));
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            renderHospitals();

            document.getElementById('searchInput').addEventListener('input', renderHospitals);
            document.getElementById('pageSize').addEventListener('change', renderHospitals);

            document.getElementById('confirmDelete').addEventListener('click', function () {
                if (deleteHospitalId !== null) {
                    hospitals = hospitals.filter(h => h.id !== deleteHospitalId);
                    saveHospitals(hospitals);
                    location.reload();
                }
            });
        });
    </script>
@endpush