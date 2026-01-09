@extends('adminTheme.default')

@section('content')

<h1 class="h3 mb-4 text-gray-800">
    <i class="fas fa-id-badge mr-2"></i>
    Accréditations – Délégations
</h1>

{{-- ================= FILTERS ================= --}}
<div class="card shadow mb-3">
    <div class="card-body">
        <form method="GET" class="form-row align-items-end">
            <div class="col-md-4">
                <label class="small text-muted">Recherche</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control"
                       placeholder="Pays ou fédération">
            </div>

            <div class="col-md-3">
                <label class="small text-muted">Pays</label>
                <select name="country" class="form-control">
                    <option value="">Tous les pays</option>
                    @foreach($countries as $country)
                        <option value="{{ $country }}"
                            {{ request('country') === $country ? 'selected' : '' }}>
                            {{ $country }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <button class="btn btn-primary btn-sm mt-4">
                    <i class="fas fa-filter"></i> Filtrer
                </button>

                <a href="{{ route('admin.accreditations.index') }}"
                   class="btn btn-outline-secondary btn-sm mt-4">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ================= TABLE ================= --}}
<div class="card shadow">
    <div class="card-body table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="thead-light text-center">
                <tr>
                    <th>Pays</th>
                    <th>Fédération</th>
                    <th>Membres inscrits</th>
                    <th style="width:140px;">Actions</th>
                </tr>
            </thead>

            <tbody>
            @forelse($delegations as $d)
                <tr>
                    <td>
                        <strong>{{ $d->country }}</strong>
                    </td>

                    <td>
                        {{ $d->federation_name }}
                    </td>

                    <td class="text-center">
                        <span class="badge badge-info">
                            {{ $d->nominative_registrations_count }}
                        </span>
                    </td>

                    <td class="text-center">
                        @if($d->nominative_registrations_count > 0)
                            <a href="{{ route('admin.accreditations.show', $d) }}"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-id-card"></i>
                                Gérer badges
                            </a>
                        @else
                            <span class="badge badge-secondary">
                                Aucun membre
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        Aucune délégation trouvée
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{-- ================= PAGINATION ================= --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-muted">
                Affichage {{ $delegations->firstItem() ?? 0 }}
                à {{ $delegations->lastItem() ?? 0 }}
                sur {{ $delegations->total() }} résultats
            </small>

            {{ $delegations->withQueryString()->links() }}
        </div>
    </div>
</div>

@endsection
