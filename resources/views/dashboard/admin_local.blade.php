@extends('adminTheme.default')

@section('content')

<h1 class="h3 mb-4 text-gray-800">Admin Local Dashboard</h1>

<div class="row">
    <div class="col-xl-6">
        <div class="card border-left-warning shadow">
            <div class="card-body">
                <div>Provisional en attente</div>
                <h3>{{ $pendingProvisional }}</h3>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card border-left-success shadow">
            <div class="card-body">
                <div>Provisional validées</div>
                <h3>{{ $validatedProvisional }}</h3>
            </div>
        </div>
    </div>
</div>


<h1 class="h3 mb-4">Admin Local – Validation Overview</h1>

<canvas id="adminLocalChart"></canvas>

@endsection






@push('charts')
<script>
new Chart(document.getElementById('adminLocalChart'), {
    type: 'pie',
    data: {
        labels: ['En attente', 'Validées', 'Rejetées'],
        datasets: [{
            data: [
                {{ $stats['en_attente'] }},
                {{ $stats['valide'] }},
                {{ $stats['rejete'] }}
            ],
            backgroundColor: ['#f6c23e','#1cc88a','#e74a3b']
        }]
    }
});
</script>
@endpush
