@extends('layouts.app')

@section('title', 'Home Page')

@section('content')
    <!-- App container starts -->
    <div class="app-container">

        <!-- App hero header starts -->
        <div class="app-hero-header d-flex align-items-center">

            <!-- Breadcrumb starts -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <i class="ri-home-8-line lh-1 pe-3 me-3 border-end"></i>
                    <a href="{{ route('home') }}">Home</a>
                </li>
                <li class="breadcrumb-item text-primary" aria-current="page">
                    Dashboard
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
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="p-2 border border-success rounded-circle me-3">
                                    <div class="icon-box md bg-success-subtle rounded-5">
                                        <i class="ri-surgical-mask-line fs-4 text-success"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <h2 class="lh-1">1890</h2>
                                    <p class="m-0">Total Patients</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-1">
                                <a class="text-success" href="{{ route('patients') }}">
                                    <span>View All</span>
                                    <i class="ri-arrow-right-line text-success ms-1"></i>
                                </a>
                                <div class="text-end">
                                    <p class="mb-0 text-success">+40%</p>
                                    <span class="badge bg-success-subtle text-success small">this month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="p-2 border border-primary rounded-circle me-3">
                                    <div class="icon-box md bg-primary-subtle rounded-5">
                                        <i class="ri-building-2-line fs-4 text-primary"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <h2 class="lh-1">360</h2>
                                    <p class="m-0">Health Facilities</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-1">
                                <a class="text-primary" href="{{ route('hospitals') }}">
                                    <span>View All</span>
                                    <i class="ri-arrow-right-line ms-1"></i>
                                </a>
                                <div class="text-end">
                                    <p class="mb-0 text-primary">+30%</p>
                                    <span class="badge bg-primary-subtle text-primary small">for the last 2 yaers</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="p-2 border border-danger rounded-circle me-3">
                                    <div class="icon-box md bg-danger-subtle rounded-5">
                                        <i class="ri-microscope-line fs-4 text-danger"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <h2 class="lh-1">980</h2>
                                    <p class="m-0">Medication</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-1">
                                <a class="text-danger" href="{{ route('medication') }}">
                                    <span>View All</span>
                                    <i class="ri-arrow-right-line ms-1"></i>
                                </a>
                                <div class="text-end">
                                    <p class="mb-0 text-danger">+60%</p>
                                    <span class="badge bg-danger-subtle text-danger small">this month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="p-2 border border-warning rounded-circle me-3">
                                    <div class="icon-box md bg-warning-subtle rounded-5">
                                        <i class="ri-stethoscope-line fs-4 text-warning"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <h2 class="lh-1">1.3000</h2>
                                    <p class="m-0">Chronic disease</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-end justify-content-between mt-1">
                                <a class="text-warning" href="javascript:void(0);">
                                    <span>View All</span>
                                    <i class="ri-arrow-right-line ms-1"></i>
                                </a>
                                <div class="text-end">
                                    <p class="mb-0 text-warning">+20%</p>
                                    <span class="badge bg-warning-subtle text-warning small">this month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Row ends -->



            <!-- Row starts -->
            <div class="row gx-3">
                <div class="col-xxl-12 col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Disease Heatmap of Zanzibar</h5>
                        </div>
                        <div class="card-body">
                            <div id="availableBeds"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Top Diseases</h5>
                        </div>
                        <div class="card-body">
                            <div id="topDiseases"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Medication Demand Forecast</h5>
                        </div>
                        <div class="card-body">
                            <div id="treatment"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Facility Quality VS. Discase Outcome</h5>
                        </div>
                        <div class="card-body">
                            <div id="facility"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-sm-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Chronic Disease Analysis</h5>
                        </div>
                        <div class="card-body">
                            <div id="claims"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-sm-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">High-Risk Pregnancy Trends</h5>
                        </div>
                        <div class="card-body">
                            <div class="auto-align-graph">
                                <div id="genderAge"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Row ends -->

        </div>
        <!-- App body ends -->

    </div>
    <!-- App container ends -->
@endsection