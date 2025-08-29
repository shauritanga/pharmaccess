@extends('layouts.app')
@section('content')
    <div class="container mt-4">
        <h3>Customize Sidebar & App Theme</h3>
        <div class="row">
            <div class="col-md-6">
                <form id="menuForm">
                    <div class="mb-3">
                        <label>Menu Name</label>
                        <input type="text" class="form-control" id="menuName" required>
                    </div>
                    <div class="mb-3">
                        <label>Menu URL</label>
                        <input type="text" class="form-control" id="menuURL" required>
                    </div>
                    <div class="mb-3">
                        <label>Submenu (comma-separated)</label>
                        <input type="text" class="form-control" id="subMenus" placeholder="e.g. Add, View, Reports">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Menu</button>
                </form>

                <hr>
                <h5>Theme Settings</h5>
                <div class="mb-3">
                    <label>Sidebar Background Color</label>
                    <input type="color" class="form-control form-control-color" id="sidebarColor" value="#022b70">
                </div>
                <div class="mb-3">
                    <label>Header Text Color</label>
                    <input type="color" class="form-control form-control-color" id="headerTextColor" value="#ffffff">
                </div>
                <button class="btn btn-success" id="applyTheme">Apply Theme</button>
            </div>

            <div class="col-md-6">
                <h5>Live Sidebar Preview</h5>
                <ul id="sidebarPreview" class="list-group">
                    <li class="list-group-item"><i class="ri-home-2-line"></i> <a href="/">Home</a></li>
                </ul>
            </div>
            <hr>
            <h5>Change Password</h5>
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="post" action="{{ route('settings.password') }}" class="mt-2">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Current password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New password</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm new password</label>
                    <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                </div>
                <button class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('menuForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const name = document.getElementById('menuName').value;
            const url = document.getElementById('menuURL').value;
            const subMenus = document.getElementById('subMenus').value.split(',').map(i => i.trim()).filter(i => i);

            let subList = '';
            if (subMenus.length > 0) {
                subList = '<ul class="ms-3">';
                subMenus.forEach(sub => {
                    subList += `<li><a href="#">${sub}</a></li>`;
                });
                subList += '</ul>';
            }

            const item = `<li class="list-group-item"><i class="ri-file-line"></i> <a href="${url}">${name}</a>${subList}</li>`;
            document.getElementById('sidebarPreview').innerHTML += item;
            this.reset();
        });

        document.getElementById('applyTheme').addEventListener('click', () => {
            const sidebarColor = document.getElementById('sidebarColor').value;
            const headerTextColor = document.getElementById('headerTextColor').value;

            document.querySelector('.sidebar').style.backgroundColor = sidebarColor;
            document.querySelectorAll('.card-header').forEach(header => {
                header.style.backgroundColor = sidebarColor;
                header.style.color = headerTextColor;
            });
        });
    </script>
@endpush