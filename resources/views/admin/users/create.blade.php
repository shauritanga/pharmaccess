@extends('layouts.app')

@section('content')
<div class="app-content">
  <div class="container-fluid">
    <div class="row gx-3">
      <div class="col-12 col-md-7 col-lg-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Add User</h5>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
          </div>
          <div class="card-body">
            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="m-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form method="post" action="{{ route('admin.users.store') }}">
              @csrf
              <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                  <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User (read-only)</option>
                  <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin (full control)</option>
                </select>
              </div>
              <button class="btn btn-primary" type="submit">Create</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

