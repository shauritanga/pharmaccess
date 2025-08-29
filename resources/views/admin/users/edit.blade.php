@extends('layouts.app')

@section('content')
<div class="app-content">
  <div class="container-fluid">
    <div class="row gx-3">
      <div class="col-12 col-md-6">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Edit User</h5>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
          </div>
          <div class="card-body">
            <form method="post" action="{{ route('admin.users.update', $user) }}">
              @csrf
              <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" />
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" />
              </div>
              <div class="mb-3">
                <label class="form-label">Password <small class="text-muted">(leave blank to keep)</small></label>
                <input type="password" name="password" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                  <option value="user" {{ ($user->role ?? 'user') === 'user' ? 'selected' : '' }}>User (read-only)</option>
                  <option value="admin" {{ ($user->role ?? 'user') === 'admin' ? 'selected' : '' }}>Admin (full control)</option>
                </select>
                @error('role') <div class="text-danger small">{{ $message }}</div> @enderror
              </div>
              <button class="btn btn-primary" type="submit">Save</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

