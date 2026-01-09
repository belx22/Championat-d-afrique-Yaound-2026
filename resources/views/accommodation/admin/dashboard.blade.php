@extends('adminTheme.default')

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Tableau de Bord - Hébergement</h1>

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Hôtels</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_hotels'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hotel fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Taux d'occupation</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['occupancy_rate'] }}%</div>
                            <div class="text-xs text-muted">{{ $stats['occupied_rooms'] }} / {{ $stats['total_rooms'] }} chambres</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bed fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Réservations Actives</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_reservations'] }}</div>
                            <div class="text-xs text-muted">Sur {{ $stats['total_reservations'] }} total</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Revenus Validés</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['validated_revenue']) }} FCFA</div>
                            <div class="text-xs text-muted">Sur {{ number_format($stats['total_revenue']) }} FCFA total</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Alerts --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">Paiements en Attente - 50%</h6>
                </div>
                <div class="card-body">
                    <div class="h3 mb-0">{{ $stats['pending_payment_50'] }}</div>
                    <small class="text-muted">Paiements de 50% en attente de validation</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Paiements en Attente - 100%</h6>
                </div>
                <div class="card-body">
                    <div class="h3 mb-0">{{ $stats['pending_payment_100'] }}</div>
                    <small class="text-muted">Paiements de 100% en attente de validation</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-4">
        {{-- Reservations by Status Pie Chart --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Réservations par Statut</h6>
                </div>
                <div class="card-body">
                    <canvas id="reservationsStatusChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Reservations Evolution (30 days) --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Évolution des Réservations (30 derniers jours)</h6>
                </div>
                <div class="card-body">
                    <canvas id="reservationsEvolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Charts Row --}}
    <div class="row mb-4">
        {{-- Monthly Revenue --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Revenus Mensuels (6 derniers mois)</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Revenue Evolution (30 days) --}}
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Évolution des Revenus (30 derniers jours)</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueEvolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Hotels by Revenue --}}
    @if(isset($stats['hotels_revenue']) && $stats['hotels_revenue']->count() > 0)
    <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Hôtels par Revenus</h6>
                </div>
                <div class="card-body">
                    <canvas id="hotelsRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Top Hotels by Occupancy --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Hôtels par Taux d'Occupation</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Hôtel</th>
                                    <th>Total Chambres</th>
                                    <th>Occupées</th>
                                    <th>Disponibles</th>
                                    <th>Taux d'Occupation</th>
                                    <th>Barre de progression</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['hotel_occupancy'] as $hotel)
                                    <tr>
                                        <td><strong>{{ $hotel['name'] }}</strong></td>
                                        <td>{{ $hotel['total'] }}</td>
                                        <td><span class="badge badge-warning">{{ $hotel['occupied'] }}</span></td>
                                        <td><span class="badge badge-success">{{ $hotel['available'] }}</span></td>
                                        <td><strong>{{ $hotel['occupancy_rate'] }}%</strong></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar 
                                                    @if($hotel['occupancy_rate'] >= 80) bg-danger
                                                    @elseif($hotel['occupancy_rate'] >= 50) bg-warning
                                                    @else bg-success
                                                    @endif" 
                                                    role="progressbar" 
                                                    style="width: {{ $hotel['occupancy_rate'] }}%"
                                                    aria-valuenow="{{ $hotel['occupancy_rate'] }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    {{ $hotel['occupancy_rate'] }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Aucun hôtel disponible</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Reservations --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Réservations Récentes</h6>
                    <a href="{{ route('reservations.index') }}" class="btn btn-sm btn-primary">
                        Voir toutes les réservations
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Délégation</th>
                                    <th>Hôtel</th>
                                    <th>Chambres</th>
                                    <th>Coût</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recent_reservations'] as $reservation)
                                    <tr class="{{ $reservation->is_cancelled ? 'table-danger' : '' }}">
                                        <td>#{{ $reservation->id }}</td>
                                        <td>{{ $reservation->delegation->country ?? 'N/A' }}</td>
                                        <td>{{ $reservation->room->hotel->name ?? 'N/A' }}</td>
                                        <td>{{ $reservation->rooms_reserved }}</td>
                                        <td>{{ number_format($reservation->total_cost) }} FCFA</td>
                                        <td>
                                            @if($reservation->is_cancelled)
                                                <span class="badge badge-danger">Annulée</span>
                                            @elseif($reservation->isFullyPaid())
                                                <span class="badge badge-success">Payée</span>
                                            @else
                                                <span class="badge badge-warning">En attente</span>
                                            @endif
                                        </td>
                                        <td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('reservations.show', $reservation) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Aucune réservation récente</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('accommodation.index') }}" class="btn btn-primary mr-2">
                        <i class="fas fa-hotel"></i> Gérer les Hôtels
                    </a>
                    <a href="{{ route('reservations.index') }}" class="btn btn-info mr-2">
                        <i class="fas fa-list"></i> Voir toutes les Réservations
                    </a>
                    <a href="{{ route('accommodation.export.reservations') }}" class="btn btn-success mr-2">
                        <i class="fas fa-file-excel"></i> Exporter les Réservations
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

@include('accommodation._responsive-styles')

@push('charts')
<script>
// Auto-refresh statistics every 30 seconds
let autoRefreshInterval;
const autoRefreshEnabled = true; // Can be made configurable

function refreshStats() {
    // Reload the page to get fresh stats
    // In a production environment, you might want to use AJAX instead
    if (autoRefreshEnabled && document.visibilityState === 'visible') {
        location.reload();
    }
}

// Start auto-refresh if enabled
if (autoRefreshEnabled) {
    autoRefreshInterval = setInterval(refreshStats, 30000); // 30 seconds
    
    // Pause when tab is not visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(autoRefreshInterval);
        } else {
            autoRefreshInterval = setInterval(refreshStats, 30000);
        }
    });
}
</script>
<script>
// Reservations by Status Pie Chart
new Chart(document.getElementById('reservationsStatusChart'), {
    type: 'doughnut',
    data: {
        labels: ['En attente', 'Validées', 'Rejetées', 'Annulées'],
        datasets: [{
            data: [
                {{ $stats['reservations_by_status']['en_attente'] }},
                {{ $stats['reservations_by_status']['valide'] }},
                {{ $stats['reservations_by_status']['rejete'] }},
                {{ $stats['reservations_by_status']['cancelled'] }}
            ],
            backgroundColor: ['#f6c23e', '#1cc88a', '#e74a3b', '#858796'],
            hoverBackgroundColor: ['#f4b619', '#17a673', '#c23321', '#6c757d'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }]
    },
    options: {
        maintainAspectRatio: false,
        tooltips: {
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            caretPadding: 10,
        },
        legend: {
            display: true,
            position: 'bottom'
        },
        cutoutPercentage: 70,
    },
});

// Reservations Evolution (30 days)
const reservationLabels = [];
for (let i = 29; i >= 0; i--) {
    const date = new Date();
    date.setDate(date.getDate() - i);
    reservationLabels.push(date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' }));
}

new Chart(document.getElementById('reservationsEvolutionChart'), {
    type: 'line',
    data: {
        labels: reservationLabels,
        datasets: [{
            label: 'Réservations',
            data: [{{ implode(',', $stats['reservations_evolution'] ?? []) }}],
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    stepSize: 1
                }
            }]
        },
        legend: {
            display: true,
            position: 'top'
        }
    }
});

// Monthly Revenue Chart
new Chart(document.getElementById('monthlyRevenueChart'), {
    type: 'bar',
    data: {
        labels: [@foreach($stats['monthly_labels'] ?? [] as $label)'{{ $label }}',@endforeach],
        datasets: [{
            label: 'Revenus (FCFA)',
            data: [{{ implode(',', $stats['monthly_revenue'] ?? []) }}],
            backgroundColor: '#1cc88a',
            hoverBackgroundColor: '#17a673',
            borderColor: '#1cc88a',
            borderWidth: 1
        }]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    callback: function(value) {
                        return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                    }
                }
            }]
        },
        legend: {
            display: false
        },
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    return new Intl.NumberFormat('fr-FR').format(tooltipItem.yLabel) + ' FCFA';
                }
            }
        }
    }
});

// Revenue Evolution (30 days)
new Chart(document.getElementById('revenueEvolutionChart'), {
    type: 'line',
    data: {
        labels: reservationLabels,
        datasets: [{
            label: 'Revenus (FCFA)',
            data: [{{ implode(',', $stats['revenue_evolution'] ?? []) }}],
            borderColor: '#1cc88a',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    callback: function(value) {
                        return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                    }
                }
            }]
        },
        legend: {
            display: true,
            position: 'top'
        },
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    return new Intl.NumberFormat('fr-FR').format(tooltipItem.yLabel) + ' FCFA';
                }
            }
        }
    }
});

@if(isset($stats['hotels_revenue']) && $stats['hotels_revenue']->count() > 0)
// Top Hotels by Revenue (Horizontal Bar Chart)
new Chart(document.getElementById('hotelsRevenueChart'), {
    type: 'horizontalBar',
    data: {
        labels: [@foreach($stats['hotels_revenue'] as $hotel)'{{ $hotel['name'] }}',@endforeach],
        datasets: [{
            label: 'Revenus (FCFA)',
            data: [{{ implode(',', $stats['hotels_revenue']->pluck('revenue')->toArray()) }}],
            backgroundColor: '#36b9cc',
            hoverBackgroundColor: '#2c9faf',
            borderColor: '#36b9cc',
            borderWidth: 1
        }]
    },
    options: {
        maintainAspectRatio: false,
        scales: {
            xAxes: [{
                ticks: {
                    beginAtZero: true,
                    callback: function(value) {
                        return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                    }
                }
            }]
        },
        legend: {
            display: false
        },
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    return new Intl.NumberFormat('fr-FR').format(tooltipItem.xLabel) + ' FCFA';
                }
            }
        }
    }
});
@endif
</script>
@endpush

@endsection
