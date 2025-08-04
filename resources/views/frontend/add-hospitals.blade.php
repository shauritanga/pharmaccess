@extends('layouts.app')
@section('content')
    <!-- App container starts -->
    <div class="app-container">

        <!-- App hero header starts -->
        <div class="app-hero-header d-flex align-items-center">

            <!-- Breadcrumb starts -->
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <i class="ri-home-8-line lh-1 pe-3 me-3 border-end"></i>
                    <a href="{{ route('add-hospitals') }}">Hospitals</a>
                </li>
                <li class="breadcrumb-item text-primary" aria-current="page">
                    Add Hospital
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
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Add Hospital</h5>
                        </div>
                        <div class="card-body">

                            <!-- Row starts -->
                            <div class="row gx-3">
                                <div class="col-xxl-3 col-lg-4 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="a1">Hospital Name</label>
                                        <input type="text" class="form-control" id="a1" placeholder="Enter Hospital Name">
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-4 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="a2">Hospital Email</label>
                                        <input type="email" class="form-control" id="a2" placeholder="Enter email address">
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-4 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="a3">Contact Personnel</label>
                                        <input type="text" class="form-control" id="a3" placeholder="Enter Hospital Name">
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-4 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="a4">Hospital Phone</label>
                                        <input type="text" class="form-control" id="a4" placeholder="Enter phone number">
                                    </div>
                                </div>


                                <div class="col-xxl-3 col-lg-4 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="inlineRadio1">Status</label>
                                        <div class="m-0">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                                    id="inlineRadio1" value="option1">
                                                <label class="form-check-label" for="inlineRadio1">Active</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                                    id="inlineRadio2" value="option2">
                                                <label class="form-check-label" for="inlineRadio2">Inactive</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('hospitals') }}" class="btn btn-outline-secondary">
                                            Cancel
                                        </a>
                                        <a href="{{ route('hospitals') }}" class="btn btn-primary">
                                            Add Department
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!-- Row ends -->

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