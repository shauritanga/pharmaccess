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
                    <a href="index.html">Home</a>
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
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Edit Department</h5>
                        </div>
                        <div class="card-body">

                            <!-- Row starts -->
                            <div class="row gx-3">
                                <div class="col-xxl-3 col-lg-4 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="a1">Department Name</label>
                                        <input type="text" class="form-control" id="a1" value="Gynecologist">
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-4 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="a2">Department Email</label>
                                        <input type="email" class="form-control" id="a2" value="test@test.com">
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-4 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="a3">Department Head</label>
                                        <input type="text" class="form-control" id="a3" value="Deena Cooley">
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-4 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="a4">Department Phone</label>
                                        <input type="text" class="form-control" id="a4" value="0009876541">
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-4 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="a5">Number of Rooms</label>
                                        <select class="form-select" id="a5">
                                            <option value="0">3</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-4 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="a6">Number of Beds</label>
                                        <select class="form-select" id="a6">
                                            <option value="0">8</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xxl-3 col-lg-4 col-sm-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="inlineRadio1">Status</label>
                                        <div class="m-0">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="inlineRadioOptions"
                                                    id="inlineRadio1" value="option1" checked="">
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
                                    <div class="mb-3">
                                        <label class="form-label" for="a7">Message</label>
                                        <textarea class="form-control" id="a7" placeholder="Enter message"
                                            rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="departments-list.html" class="btn btn-outline-secondary">
                                            Cancel
                                        </a>
                                        <a href="departments-list.html" class="btn btn-primary">
                                            Update Department
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