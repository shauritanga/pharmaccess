@extends('layout.app')

@section('content')
    <div class="card p-4">
        <h4>{{ $detail['name'] }} - Case Breakdown</h4>
        <ul class="list-group mt-3">
            <li class="list-group-item">Reported Cases: <strong>{{ $detail['reported'] }}</strong></li>
            <li class="list-group-item">Treated Cases: <strong>{{ $detail['solved'] }}</strong></li>
            <li class="list-group-item">Untreated Cases: <strong>{{ $detail['unsolved'] }}</strong></li>
        </ul>
    </div>
@endsection