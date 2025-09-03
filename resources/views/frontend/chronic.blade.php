@extends('layouts.app')

@section('content')
<div class="app-wrapper">
  <div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-fluid">

      <!-- Filters Row -->
      <div class="row gx-3">
        <div class="col-12">
          <div class="card filter-section mb-3">
            <div class="card-header" style="background:#022b70;color:#fff">
              <h5 class="card-title mb-0">Chronic Diseases (Diabetes & Hypertension)</h5>
            </div>
            <div class="card-body">
              <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-lg-3">
                  <label for="yearStart" class="form-label">Start Year</label>
                  <select id="yearStart" class="form-select"></select>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                  <label for="yearEnd" class="form-label">End Year</label>
                  <select id="yearEnd" class="form-select"></select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts -->
      <div class="row gx-3">
        <div class="col-12">
          <div class="card mb-3">
            <div class="card-header">
              <h5 class="card-title">Monthly Patients (Distinct) - Jan to Dec</h5>
            </div>
            <div class="card-body">
              <div id="monthlyChronic"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="row gx-3">
        <div class="col-12 col-lg-6">
          <div class="card mb-3">
            <div class="card-header">
              <h5 class="card-title">Patients by Age Group</h5>
            </div>
            <div class="card-body">
              <div id="ageChronic"></div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <div class="card mb-3">
            <div class="card-header">
              <h5 class="card-title">Patients by Economic Status (PPI)</h5>
            </div>
            <div class="card-body">
              <div id="econChronic"></div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  #monthlyChronic, #ageChronic, #econChronic { height: 400px; width: 100%; }
</style>
@endpush

@push('scripts')

<script>
  const API = '/api/chronic-analytics';

  document.addEventListener('DOMContentLoaded', () => {
    loadYears().then(() => {
      const filters = getFilters();
      fetchAnalytics(filters);
    });

    document.getElementById('yearStart').addEventListener('change', onFilterChange);
    document.getElementById('yearEnd').addEventListener('change', onFilterChange);
  });

  function onFilterChange() {
    const f = getFilters();
    fetchAnalytics(f);
  }

  async function loadYears() {
    const res = await fetch(`${API}/available-years`);
    const json = await res.json();
    const startSel = document.getElementById('yearStart');
    const endSel = document.getElementById('yearEnd');
    startSel.innerHTML = ''; endSel.innerHTML = '';
    (json.years || []).forEach(y => {
      const o1 = document.createElement('option'); o1.value = y; o1.textContent = y; startSel.appendChild(o1);
      const o2 = document.createElement('option'); o2.value = y; o2.textContent = y; endSel.appendChild(o2);
    });
    if (json.years && json.years.length) {
      endSel.value = String(json.years[json.years.length-1]);
      startSel.value = String(json.years[0]);
    }
  }

  function getFilters() {
    return {
      year_start: document.getElementById('yearStart').value,
      year_end: document.getElementById('yearEnd').value,
    };
  }

  async function fetchAnalytics(filters) {
    const params = new URLSearchParams();
    if (filters.year_start) params.append('year_start', filters.year_start);
    if (filters.year_end) params.append('year_end', filters.year_end);

    const res = await fetch(`${API}?${params}`);
    const json = await res.json();
    if (!json.success) return;

    renderMonthly(json.charts.monthly_patients);
    renderAge(json.charts.age_group_patients);
    renderEcon(json.charts.economic_status_patients);
  }

  let chartMonthly, chartAge, chartEcon;

  function renderMonthly(data) {
    if (chartMonthly) chartMonthly.destroy();
    chartMonthly = new ApexCharts(document.querySelector('#monthlyChronic'), {
      chart: { type: 'bar', height: 400 },
      series: data.series || [],
      xaxis: { categories: data.labels || [] },
      colors: ['#2662ed', '#ef4444'],
      legend: { position: 'top' },
      plotOptions: { bar: { columnWidth: '50%' } }
    });
    chartMonthly.render();
  }

  function renderAge(data) {
    if (chartAge) chartAge.destroy();
    chartAge = new ApexCharts(document.querySelector('#ageChronic'), {
      chart: { type: 'bar', height: 400 },
      series: data.series || [],
      xaxis: { categories: data.labels || [] },
      colors: ['#2662ed', '#ef4444'],
      legend: { position: 'top' },
      plotOptions: { bar: { columnWidth: '55%' } }
    });
    chartAge.render();
  }

  function renderEcon(data) {
    if (chartEcon) chartEcon.destroy();
    chartEcon = new ApexCharts(document.querySelector('#econChronic'), {
      chart: { type: 'bar', height: 400 },
      series: data.series || [],
      xaxis: { categories: (data.labels || []) },
      colors: ['#2662ed', '#ef4444'],
      legend: { position: 'top' },
      plotOptions: { bar: { columnWidth: '55%' } }
    });
    chartEcon.render();
  }
</script>
@endpush

