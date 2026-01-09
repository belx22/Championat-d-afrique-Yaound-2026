@extends('adminTheme.default')

@section('content')
<h1 class="h3 mb-4 text-gray-800">Super Admin – Global Statistics</h1>


<div class="row">

{{-- UTILISATEURS --}}
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                Utilisateurs
            </div>
            <div class="h5 mb-0 font-weight-bold">{{ $totalUsers }}</div>
            <small>Admins fédération : {{ $totalAdminFederation }}</small>
        </div>
    </div>
</div>

{{-- DÉLÉGATIONS --}}
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                Délégations
            </div>
            <div class="h5 mb-0 font-weight-bold">{{ $totalDelegations }}</div>
            <small class="text-success">Validées : {{ $delegationsValidated }}</small><br>
            <small class="text-warning">En cours : {{ $delegationsInProgress }}</small><br>
            <small class="text-secondary">Non commencées : {{ $delegationsNotStarted }}</small>
        </div>
    </div>
</div>

{{-- MEMBRES --}}
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                Membres inscrits
            </div>
            <div class="h5 mb-0 font-weight-bold">{{ $totalMembers }}</div>
            <small>Gymnastes : {{ $totalGymnasts }}</small>
        </div>
    </div>
</div>

{{-- GYMNASTES --}}
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-warning shadow h-100 py-2">
        <div class="card-body">
            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                Gymnastes déclarés
            </div>
            <div class="small">Provisional : <strong>{{ $provisionalGymnasts }}</strong></div>
            <div class="small">Definitive : <strong>{{ $definitiveGymnasts }}</strong></div>
        </div>
    </div>
</div>

</div>

<div class="row">
    

<div class="row">
    <div class="col-xl-6">
        <canvas id="usersChart"></canvas>
    </div>
    <div class="col-xl-6">
        <canvas id="provisionalChart"></canvas>
    </div>
</div>




@endsection
