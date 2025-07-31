@extends('layouts.app')
@section('title', 'Hospitals List')
@section('content')
    <!-- App container starts -->
    <div class="app-container">

        <!-- App hero header starts -->
        <div class="app-hero-header d-flex align-items-center">

            <!-- Breadcrumb starts -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <i class="ri-home-8-line lh-1 pe-3 me-3 border-end"></i>
                    <a href="{{ route('hospitals') }}">Hospitals</a>
                </li>
                <li class="breadcrumb-item text-primary" aria-current="page">
                    Hospitals
                </li>
            </ol>
            <!-- Breadcrumb ends -->

            <!-- Sales stats starts -->
            <div class="ms-auto d-lg-flex d-none flex-row">
                <div class="d-flex flex-row gap-1 day-sorting">
                    <button class="btn btn-sm btn-primary">Today</button>
                    <button class="btn btn-sm">7d</button>
                    <button class="btn btn-sm">2w</button>
                    <button class="btn btn-sm">1m</button>
                    <button class="btn btn-sm">3m</button>
                    <button class="btn btn-sm">6m</button>
                    <button class="btn btn-sm">1y</button>
                </div>
            </div>
            <!-- Sales stats ends -->

        </div>
        <!-- App Hero header ends -->

        <!-- App body starts -->
        <div class="app-body">

            <!-- Row starts -->
            <div class="row gx-3">
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
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title">Hospital List</h5>
                            <a href="{{ route('add-hospitals') }}" class="btn btn-primary ms-auto">Add Hospital</a>
                        </div>
                        <div class="card-body">

                            <!-- Table starts -->
                            <div class="table-responsive">
                                <table id="basicExample" class="table m-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Hospital Name</th>
                                            <th>Contact Personnel</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>001</td>
                                            <td>Al Rahma Hospital</td>
                                            <td>
                                                <img src="assets/images/user.png" class="img-shadow img-2x rounded-5 me-1"
                                                    alt="Doctors Admin Template">
                                                Deena Cooley
                                            </td>

                                            <td>
                                                <div class="d-inline-flex gap-1">
                                                    <button class="btn btn-outline-danger btn-sm rounded-5"
                                                        data-bs-toggle="modal" data-bs-target="#delRow">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <a href="{{ route('edit-hospitals') }}"
                                                        class="btn btn-outline-success btn-sm rounded-5"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        data-bs-title="Edit Department">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>002</td>
                                            <td>Tasakhtaa Global Hospital </td>
                                            <td>
                                                <img src="assets/images/user2.png" class="img-shadow img-2x rounded-5 me-1"
                                                    alt="Doctors Admin Template">
                                                Hector Banks
                                            </td>

                                            <td>
                                                <div class="d-inline-flex gap-1">
                                                    <button class="btn btn-outline-danger btn-sm rounded-5"
                                                        data-bs-toggle="modal" data-bs-target="#delRow">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <a href="{{ route('edit-hospitals') }}"
                                                        class="btn btn-outline-success btn-sm rounded-5"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        data-bs-title="Edit Department">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>003</td>
                                            <td>Mnazi Mmoja Hospital</td>
                                            <td>
                                                <img src="assets/images/user3.png" class="img-shadow img-2x rounded-5 me-1"
                                                    alt="Doctors Admin Template">
                                                Owen Scott
                                            </td>

                                            <td>
                                                <div class="d-inline-flex gap-1">
                                                    <button class="btn btn-outline-danger btn-sm rounded-5"
                                                        data-bs-toggle="modal" data-bs-target="#delRow">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <a href="{{ route('edit-hospitals') }}"
                                                        class="btn btn-outline-success btn-sm rounded-5"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        data-bs-title="Edit Department">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>004</td>
                                            <td>Dr.Mehta’s Hospital</td>
                                            <td>
                                                <img src="assets/images/user5.png" class="img-shadow img-2x rounded-5 me-1"
                                                    alt="Doctors Admin Template">
                                                Alison Estrada
                                            </td>

                                            <td>
                                                <div class="d-inline-flex gap-1">
                                                    <button class="btn btn-outline-danger btn-sm rounded-5"
                                                        data-bs-toggle="modal" data-bs-target="#delRow">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <a href="{{ route('edit-hospitals') }}"
                                                        class="btn btn-outline-success btn-sm rounded-5"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        data-bs-title="Edit Department">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>005</td>
                                            <td>Tawakal Hospital</td>
                                            <td>
                                                <img src="assets/images/user4.png" class="img-shadow img-2x rounded-5 me-1"
                                                    alt="Doctors Admin Template">
                                                Mitchel Alvarez
                                            </td>

                                            <td>
                                                <div class="d-inline-flex gap-1">
                                                    <button class="btn btn-outline-danger btn-sm rounded-5"
                                                        data-bs-toggle="modal" data-bs-target="#delRow">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <a href="{{ route('edit-hospitals') }}"
                                                        class="btn btn-outline-success btn-sm rounded-5"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        data-bs-title="Edit Department">
                                                        <i class="ri-edit-box-line"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Table ends -->

                            <!-- Modal Delete Row -->
                            <div class="modal fade" id="delRow" tabindex="-1" aria-labelledby="delRowLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="delRowLabel">
                                                Confirm
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to delete the hospital?
                                        </div>
                                        <div class="modal-footer">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="departments-list.html" class="btn btn-secondary"
                                                    data-bs-dismiss="modal" aria-label="Close">No</a>
                                                <a href="departments-list.html" class="btn btn-danger"
                                                    data-bs-dismiss="modal" aria-label="Close">Yes</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- Row ends -->

        </div>
        <!-- App footer starts -->
        <div class="app-footer bg-white">
            <span>© Pharmaccess 2025</span>
        </div>
        <!-- App footer ends -->
    </div>
@endsection
<!-- App container ends -->