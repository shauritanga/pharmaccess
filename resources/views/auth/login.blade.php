@extends('layouts.app')
@section('title', 'Sign In')

@push('styles')
<style>
  body { background: #f5f7fb; }
  .login-hero {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
  }
  .login-left {
    background: linear-gradient(135deg, #003b5c 0%, #0077b6 100%);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    padding: 60px 40px;
  }
  .login-left-inner {
    max-width: 460px;
  }
  .login-left h1 { font-weight: 700; margin-bottom: 10px; }
  .login-left p { opacity: .9; }
  .login-right { display: flex; align-items: center; justify-content: center; padding: 40px; }
  .brand-logo { height: 56px;}
  .shadow-card { box-shadow: 0 10px 30px rgba(0,0,0,.08); border: 0; }
  @media (max-width: 992px) {
    .login-hero { grid-template-columns: 1fr; }
    .login-left { display: none; }
  }
</style>
@endpush

@section('content')
<div class="login-hero">
  <section class="login-left">
    <div class="login-left-inner">
      <img src="{{ asset('assets/images/Asset 2.png') }}" class="brand-logo mb-4" alt="PharmAccess Logo">
      <h1>AI-Powered Predictive Health Intelligence</h1>
      <p>Visualize patient data, monitor trends, and make informed decisions. Sign in to access your analytics dashboard.</p>
      <ul class="mt-4">
        <li>Facility performance and attendance</li>
        <li>Medication usage trends</li>
        <li>Chronic disease monitoring</li>
      </ul>
    </div>
  </section>

  <section class="login-right">
    <div class="card shadow-card" style="max-width: 420px; width: 100%;">
      <div class="card-body p-4">
        <div class="text-center mb-3">
          <img src="{{ asset('assets/images/Asset 2.png') }}" class="brand-logo" alt="PharmAccess Logo">
          <h5 class="mt-3 mb-0">Welcome back</h5>
          <small class="text-muted">Sign in to your account</small>
        </div>

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="m-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="post" action="{{ route('login.post') }}" novalidate>
          @csrf
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="admin@pharmaccess.co.tz" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="********" required>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="remember" id="remember">
              <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a href="#" class="small text-muted">Forgot password?</a>
          </div>
          <button class="btn btn-primary w-100" type="submit">Sign In</button>
        </form>
      </div>
    </div>
  </section>
</div>
@endsection

