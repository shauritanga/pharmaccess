@extends('layouts.app')
@section('content')
    <div class="container mt-4">
        <h3>Customize Sidebar</h3>
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
                    <button type="submit" class="btn btn-primary">Add Menu</button>
                </form>
            </div>
            <div class="col-md-6">
                <h5>Live Sidebar Preview</h5>
                <ul id="sidebarPreview" class="list-group">
                    <li class="list-group-item"><i class="ri-home-2-line"></i> Home</li>
                </ul>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        document.getElementById('menuForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const name = document.getElementById('menuName').value;
            const url = document.getElementById('menuURL').value;
            const item = `<li class="list-group-item"><i class="ri-file-line"></i> <a href="${url}">${name}</a></li>`;
            document.getElementById('sidebarPreview').innerHTML += item;
            this.reset();
        });
    </script>
@endpush