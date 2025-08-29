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
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

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
  @if(auth()->check())
  <div class="page-wrapper d-flex flex-column" style="min-height:100vh;">
    @include('partials.header')

    <div class="main-container flex-grow-1 d-flex">
      @include('partials.sidebar')
      <main class="flex-grow-1">
        @yield('content')
        @yield('scripts')
      </main>
    </div>
    @include('partials.footer')
  </div>
  @else
    <main>
      @yield('content')
      @yield('scripts')
    </main>
  @endif


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

  <!-- Leaflet + Proj4 for GeoJSON EPSG:3395 support -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.9.2/proj4.min.js"></script>
  <!-- Use local Proj4Leaflet to avoid CDN/CSP issues -->
  <script src="{{ asset('assets/vendor/proj4leaflet/proj4leaflet.min.js') }}"></script>

  <!-- Conditional Chart Scripts - Only load on specific pages -->
  @if(request()->routeIs('home'))
    <!-- Remove demo charts; dynamic dashboard.js will drive charts from API data -->
    <!-- (If needed we can re-add minimal bootstraps that expose chart instances) -->
  @endif

  @if(request()->routeIs('departments.*'))
    <!-- Apex Charts: Department -->
    <script src="{{ asset('assets/vendor/apex/custom/department/department-list.js') }}"></script>
    <script src="{{ asset('assets/vendor/apex/custom/department/employees.js') }}"></script>
  @endif

  <!-- Data Tables -->
  <script src="{{ asset('assets/vendor/datatables/dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/datatables/custom/custom-datatables.js') }}"></script>

  <!-- Custom JS -->
  <script src="{{ asset('assets/js/custom.js') }}"></script>

  @stack('scripts')
</body>

</html>