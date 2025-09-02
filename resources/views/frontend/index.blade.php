@extends('layouts.app')

@section('title', 'Home Page')

@section('content')
    <!-- App container starts -->
    <div class="app-container px-3">

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
                    <button class="btn btn-sm btn-primary">This Year</button>
                    <button class="btn btn-sm">2 Years</button>
                    <button class="btn btn-sm">3 Years</button>
                    <button class="btn btn-sm">Since 2020</button>
                </div>
            </div>
            <!-- Sales stats ends -->

        </div>
        <!-- App Hero header ends -->

        <!-- App body starts -->
        <div class="app-body">


            <style>
                /* Five cards per row on lg+ screens */
                @media (min-width: 992px) {
                    .col-5th { flex: 0 0 20%; max-width: 20%; }
                }
                /* Tighten gaps below cards */
                .summary-row + .heatmap-row {
                    margin-top: 36px !important;
                }
            </style>

            <style>
                /* Five cards per row on lg+ screens */
                @media (min-width: 992px) {
                    .col-5th { flex: 0 0 20%; max-width: 20%; }
                }
            </style>


            <!-- Row starts -->
            <div class="row gx-3 summary-row">
                <div class="col-xl-3 col-sm-6 col-12 col-5th">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="p-2 border border-success rounded-circle me-3">
                                    <div class="icon-box md bg-success-subtle rounded-5">
                                        <i class="ri-surgical-mask-line fs-4 text-success"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <h2 class="lh-1" id="metric-total-patients">--</h2>
                                    <p class="m-0" id="label-total-patients">Total Patients</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 col-5th">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="p-2 border border-primary rounded-circle me-3">
                                    <div class="icon-box md bg-primary-subtle rounded-5">
                                        <i class="ri-building-2-line fs-4 text-primary"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <h2 class="lh-1" id="metric-health-facilities">--</h2>
                                    <p class="m-0" id="label-health-facilities">Health Facilities</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 col-5th">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="p-2 border border-danger rounded-circle me-3">
                                    <div class="icon-box md bg-danger-subtle rounded-5">
                                        <i class="ri-microscope-line fs-4 text-danger"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <h2 class="lh-1" id="metric-prescriptions">--</h2>
                                    <p class="m-0" id="label-prescriptions">Under-5 (Malaria/Pneumonia/Diarrhea)</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <!-- Extra summary cards: Pregnancy Cases and Chronic Diseases Cases -->

                    <div class="col-xl-3 col-sm-6 col-12 col-5th">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 border border-pink rounded-circle me-3">
                                        <div class="icon-box md bg-pink-subtle rounded-5">
                                            <i class="ri-heart-pulse-line fs-4 text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <h2 class="lh-1" id="metric-pregnancy-cases">--</h2>
                                        <p class="m-0" id="label-pregnancy-cases">Pregnancy Cases</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 col-12 col-5th">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="p-2 border border-secondary rounded-circle me-3">
                                        <div class="icon-box md bg-secondary-subtle rounded-5">
                                            <i class="ri-pulse-line fs-4 text-secondary"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <h2 class="lh-1" id="metric-chronic-cases">--</h2>
                                        <p class="m-0" id="label-chronic-cases">Chronic Diseases Cases</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <!-- Row ends -->



            <!-- Shehia Heatmap (GeoJSON) below summary cards -->
            <!-- <style>
                /* Reduce vertical spacing between summary cards and heatmap */
                .app-body > .row.gx-3 + .row.gx-3 { margin-top: 0.5rem !important; }
                .card.mb-3 { margin-bottom: 0.75rem !important; }
            </style> -->

            <div class="row gx-3 heatmap-row">
                <div class="col-12">
                    <div class="card mb-3">
                        <div class="card-header d-flex align-items-center">
                            <h5 class="card-title mb-0">Shehia Heatmap</h5>
                           
                        </div>
                        <div class="card-body">
                            <div id="shehiaMap"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row starts -->
            <div class="row gx-3">
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
                            <h5 class="card-title">Facility Quality VS. Disease Outcome</h5>
                        </div>
                        <div class="card-body">
                            <div id="facility"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Chronic Disease Analysis</h5>
                        </div>
                        <div class="card-body">
                            <div id="claims"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-6 col-sm-12">
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
                <div class="col-xxl-6 col-sm-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Patient Age Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div id="ageDistribution"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Charts Row ends -->



        </div>
        <!-- App body ends -->

    </div>
    <!-- App container ends -->
@endsection

@push('scripts')
    <!-- Dashboard Dynamic JavaScript -->
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('assets/js/geo-heatmaps.js') }}?v={{ time() }}"></script>
@endpush