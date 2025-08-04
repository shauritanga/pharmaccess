<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AI-Powered Predictive Health Intelligence System</title>

  <!-- Meta -->
  <meta name="description" content="Marketplace for Bootstrap Admin Dashboards">
  <meta property="og:title" content="Admin Templates - Dashboard Templates">
  <meta property="og:description" content="Marketplace for Bootstrap Admin Dashboards">
  <meta property="og:type" content="Website">
  <link rel="shortcut icon" href="{{ asset('assets/images/favicon.svg') }}">

  <!-- Fonts and Main CSS -->
  <link rel="stylesheet" href="{{ asset('assets/fonts/remix/remixicon.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/main.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/overlay-scroll/OverlayScrollbars.min.css') }}">

  @stack('styles')
</head>

<body>
  <!-- Loading -->
  <div id="loading-wrapper">
    <div class='spin-wrapper'>
      @for ($i = 0; $i < 6; $i++)
      <div class='spin'>
      <div class='inner'></div>
      </div>
    @endfor
    </div>
  </div>

  <!-- Page wrapper -->
  <div class="page-wrapper">
    @include('partials.header')

    <div class="main-container">
      @include('partials.sidebar')
      <main>
        @yield('content')
        @yield('scripts')
      </main>
      @include('partials.footer')
    </div>


  </div>


  <!-- ************ JavaScript Files ************ -->
  <!-- Required jQuery first, then Bootstrap Bundle JS -->
  <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/moment.min.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


  <!-- ************ Vendor JS Files ************ -->

  <!-- Overlay Scroll JS -->
  <script src="{{ asset('assets/vendor/overlay-scroll/jquery.overlayScrollbars.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/overlay-scroll/custom-scrollbar.js') }}"></script>

  <!-- Apex Charts Core -->
  <script src="{{ asset('assets/vendor/apex/apexcharts.min.js') }}"></script>

  <!-- Apex Charts: Department -->
  <script src="{{ asset('assets/vendor/apex/custom/department/department-list.js') }}"></script>
  <script src="{{ asset('assets/vendor/apex/custom/department/employees.js') }}"></script>

  <!-- Apex Charts: Home -->
  <script src="{{ asset('assets/vendor/apex/custom/home/patients.js') }}"></script>
  <script src="{{ asset('assets/vendor/apex/custom/home/treatment.js') }}"></script>
  <script src="{{ asset('assets/vendor/apex/custom/home/available-beds.js') }}"></script>
  <script src="{{ asset('assets/vendor/apex/custom/home/earnings.js') }}"></script>
  <script src="{{ asset('assets/vendor/apex/custom/home/gender-age.js') }}"></script>
  <script src="{{ asset('assets/vendor/apex/custom/home/claims.js') }}"></script>

  <!-- Data Tables -->
  <script src="{{ asset('assets/vendor/datatables/dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/datatables/custom/custom-datatables.js') }}"></script>

  <!-- Custom JS -->
  <script src="{{ asset('assets/js/custom.js') }}"></script>

  @stack('scripts')
</body>

</html>