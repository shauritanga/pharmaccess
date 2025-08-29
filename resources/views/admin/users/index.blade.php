@extends('layouts.app')

@section('content')
<div class="app-content">
  <div class="container-fluid">
    <div class="row gx-3">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="card-title m-0">Users</h5>
            <div class="d-flex gap-2 ms-auto">
              <form method="get" class="d-flex" action="{{ route('admin.users.index') }}">
                <input type="text" name="q" class="form-control form-control-sm me-2" placeholder="Search name or email" value="{{ $q }}" />
                <button class="btn btn-sm btn-outline-primary" type="submit">Search</button>
              </form>
              <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">Add User</a>
            </div>
          </div>
          <div class="card-body">
            @if (session('status'))
              <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($users as $user)
                    <tr>
                      <td>{{ $user->name }}</td>
                      <td>{{ $user->email }}</td>
                      <td><span class="badge bg-{{ $user->role === 'admin' ? 'primary' : 'secondary' }}">{{ ucfirst($user->role ?? 'user') }}</span></td>
                      <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                        @if(auth()->id() !== $user->id)
                        <form action="{{ route('admin.users.destroy', $user) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this user?');">
                          @csrf
                          @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                        </form>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr><td colspan="4" class="text-center">No users found</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            {{ $users->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

