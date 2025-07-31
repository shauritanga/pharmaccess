<!-- App header starts -->
<div style="background-color: #00446D;" class="app-header d-flex align-items-center">

  <!-- Toggle buttons starts -->
  <div class="d-flex">
    <button class="toggle-sidebar">
      <i class="ri-menu-line"></i>
    </button>
    <button class="pin-sidebar">
      <i class="ri-menu-line"></i>
    </button>
  </div>
  <!-- Toggle buttons ends -->

  <!-- App brand starts -->
  <div class="app-brand ms-3">
    <a href="{{ route('home') }}" class="d-lg-block d-none">
      <img src="{{ asset('assets/images/Asset 2.png') }}" class="logo" alt="Medicare Admin Template">
    </a>
    <a href="{{ route('home') }}" class="d-lg-none d-md-block">
      <img src="{{ asset('assets/images/Asset 2.svg') }}" class="logo" alt="Medicare Admin Template">
    </a>
  </div>
  <!-- App brand ends -->

  <!-- App header actions starts -->
  <div class="header-actions">

    <!-- Search container starts -->
    <div class="search-container d-lg-block d-none mx-3">
      <input type="text" class="form-control" id="searchId" placeholder="Search">
      <i class="ri-search-line"></i>
    </div>
    <!-- Search container ends -->

    <!-- Header actions starts -->
    <div class="d-lg-flex d-none gap-2">

      <!-- country show -->
      <div class="dropdown">
        <a class="dropdown-toggle header-icon" href="#!" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="assets/images/flags/1x1/tz.svg" class="header-country-flag" alt="Bootstrap Dashboards">
        </a>
      </div>
      <!-- country show ends -->
    </div>
    <!-- Header actions ends -->

    <!-- Header user settings starts -->
    <div class="dropdown ms-2">
      <a id="userSettings" class="dropdown-toggle d-flex align-items-center" href="#!" role="button"
        data-bs-toggle="dropdown" aria-expanded="false">
        <div class="avatar-box">NM<span class="status busy"></span></div>
      </a>
      <div class="dropdown-menu dropdown-menu-end shadow-lg">
        <div class="px-3 py-2">
          <span class="small">Admin</span>
          <h6 class="m-0">Nicholaus Maheri</h6>
        </div>
        <div class="mx-3 my-2 d-grid">
          <a href="login.html" class="btn btn-danger">Logout</a>
        </div>
      </div>
    </div>
    <!-- Header user settings ends -->

  </div>
  <!-- App header actions ends -->

</div>
<!-- App header ends -->