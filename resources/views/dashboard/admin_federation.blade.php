@extends('adminTheme.default')
@section('content')

<h1 class="h3 mb-4 text-gray-800">Admin Fédération Dashboard</h1>
<div class="row mb-4">

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                    Total Membres for nominative registration
                </div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    {{ $totalMembers }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                    Total Gymnastes 
                </div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    {{ $totalGymnasts }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                    MAG (Jr / Sr)
                </div>
                <div class="h6 mb-0">
                    {{ $magJunior }} / {{ $magSenior }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                    GAF (Jr / Sr)
                </div>
                <div class="h6 mb-0">
                    {{ $gafJunior }} / {{ $gafSenior }}
                </div>
            </div>
        </div>
    </div>

</div>
 {{-- PROVISIONAL 

<div class="card shadow mb-4">
    <div class="card-body">
        <h6 class="font-weight-bold">Avancement global</h6>
        <div class="progress mb-2">
            <div class="progress-bar bg-info"
                 role="progressbar"
                 style="width: {{ $progress }}%"
                 aria-valuenow="{{ $progress }}"
                 aria-valuemin="0"
                 aria-valuemax="100">
                {{ $progress }} %
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header font-weight-bold">
        Répartition des Gymnastes
    </div>
    <div class="card-body">
        <canvas id="gymnastChart" height="120"></canvas>
    </div>
</div>
--}}

<div class="row mb-4">

    {{-- PROVISIONAL --}}
    <div class="col-md-4 text-center">
        <a href="/provisional-registration"
           class="btn btn-primary btn-sm">
            Aller à Provisional
        </a>
    </div>

    {{-- DEFINITIVE --}}
    <div class="col-md-4 text-center">
        @if($provisionalStatus === 'valide')
            <a href="/definitive-registration"
               class="btn btn-warning btn-sm">
                Aller à Definitive
            </a>
        @else
            <button class="btn btn-warning btn-sm" disabled>
                Definitive bloquée
            </button>
        @endif
    </div>

    {{-- NOMINATIVE --}}
    <div class="col-md-4 text-center">
        @if($definitiveStatus === 'valide')
            <a href="/nominative-registration"
               class="btn btn-success btn-sm">
                Aller à Nominative
            </a>
        @else
            <button class="btn btn-success btn-sm" disabled>
                Nominative bloquée
            </button>
        @endif
    </div>

</div>


<div class="row">

    {{-- PROVISIONAL --}}
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                    Provisional Registration
                </div>
                <span class="badge badge-{{ statusBadge($provisionalStatus) }}">
                    {{ strtoupper($provisionalStatus) }}
                </span>
            </div>
        </div>
    </div>

    {{-- DEFINITIVE --}}
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                    Definitive Registration
                </div>
                <span class="badge badge-{{ statusBadge($definitiveStatus) }}">
                    {{ strtoupper($definitiveStatus) }}
                </span>
            </div>
        </div>
    </div>


    {{-- NOMINATIVE 
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                    Nominative Registration
                </div>
                <span class="badge badge-{{ statusBadge($nominativeStatus) }}">
                    {{ strtoupper($nominativeStatus) }}
                </span>
            </div>
        </div>
    </div>--}}

</div>


<h1 class="h3 mb-4">Admin Fédération – Delegation Statistics</h1>

@if($prov)
<canvas id="delegationChart"></canvas>
@endif
@endsection


@push('charts')
@if($prov)
<script>
new Chart(document.getElementById('delegationChart'), {
    type: 'bar',
    data: {
        labels: [
            'MAG Junior','MAG Senior','WAG Junior','WAG Senior',
            'Coaches','Judges','Managers'
        ],
        datasets: [{
            label: 'Delegation Members',
            data: [
                {{ $prov->mag_junior }},
                {{ $prov->mag_senior }},
                {{ $prov->wag_junior }},
                {{ $prov->wag_senior }},
                {{ $prov->coach }},
                {{ $prov->judges_total }},
                {{ $prov->team_manager }}
            ],
            backgroundColor: '#36b9cc'
        }]
    }
});
</script>
@endif
@endpush



@push('charts')
<script>
new Chart(document.getElementById('gymnastChart'), {
    type: 'bar',
    data: {
        labels: ['MAG Junior','MAG Senior','GAF Junior','GAF Senior'],
        datasets: [{
            label: 'Nombre de gymnastes',
            data: [
                {{ $magJunior }},
                {{ $magSenior }},
                {{ $gafJunior }},
                {{ $gafSenior }}
            ],
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#f6c23e'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>
@endpush

