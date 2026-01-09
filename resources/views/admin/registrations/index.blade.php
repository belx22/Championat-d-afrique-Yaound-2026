@extends('adminTheme.default')

@section('content')

<h1 class="h3 mb-4 text-gray-800">
    <i class="fas fa-clipboard-list mr-2"></i>
    Registration Management
</h1>

{{-- ================= FILTERS ================= --}}
<div class="card shadow mb-3">
    <div class="card-body">
        <form method="GET" class="form-row align-items-end">

            <div class="col-md-3">
                <label class="small text-muted">Recherche</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control"
                       placeholder="Pays / Fédération">
            </div>

            <div class="col-md-3">
                <label class="small text-muted">Statut</label>
                <select name="status" class="form-control">
                    <option value="">Tous</option>
                    <option value="valide" {{ request('status')=='valide'?'selected':'' }}>Validé</option>
                    <option value="en_attente" {{ request('status')=='en_attente'?'selected':'' }}>En attente</option>
                    <option value="rejete" {{ request('status')=='rejete'?'selected':'' }}>Rejeté</option>
                </select>
            </div>

            <div class="col-md-3">
                <button class="btn btn-primary btn-sm mt-4">
                    <i class="fas fa-filter"></i> Filtrer
                </button>

                <a href="{{ route('admin.registrations.index') }}"
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
                    <th>Provisional</th>
                    <th>Definitive</th>
                    <th>Nominative</th>
                    <th style="width:150px;">Avancement</th>
                    <th>Documents</th>
                    <th style="width:110px;">Actions</th>
                </tr>
            </thead>

            <tbody>
            @forelse($rows as $row)
                @php $delegation = $row['delegation']; @endphp

                <tr>
                    {{-- COUNTRY --}}
                    <td><strong>{{ $delegation->country }}</strong></td>

                    {{-- FEDERATION --}}
                    <td>{{ $delegation->federation_name }}</td>

                    {{-- PROVISIONAL --}}
                    <td class="text-center">
                        @include('admin.registrations.partials.status-badge', [
                            'status' => optional($row['steps']['provisional'])->status
                        ])
                    </td>

                    {{-- DEFINITIVE --}}
                    <td class="text-center">
                        @include('admin.registrations.partials.status-badge', [
                            'status' => optional($row['steps']['definitive'])->status
                        ])
                    </td>

                    {{-- NOMINATIVE --}}
                    <td class="text-center">
                        @if($row['steps']['nominative']->count())
                            <span class="badge badge-success">
                                <i class="fas fa-check"></i> DONE
                            </span>
                        @else
                            <span class="badge badge-secondary">N/A</span>
                        @endif
                    </td>

                    {{-- COMPLETION --}}
                    <td>
                        <div class="progress progress-sm mb-1">
                            <div class="progress-bar bg-info"
                                 style="width:{{ $row['completion'] }}%">
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $row['completion'] }} %
                        </small>
                    </td>

                    {{-- MISSING FILES --}}
                    <td class="text-center">
                        @if(count($row['missing_files']))
                            <span class="badge badge-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ count($row['missing_files']) }}
                            </span>
                        @else
                            <span class="badge badge-success">
                                <i class="fas fa-check-circle"></i> OK
                            </span>
                        @endif
                    </td>

                    {{-- ACTIONS --}}
                    <td class="text-center">
                        <a href="{{ route('admin.registrations.show', $delegation->id) }}"
                           class="btn btn-sm btn-outline-primary"
                           title="Voir le dossier">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        Aucune délégation trouvée
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{-- ================= PAGINATION ================= --}}
        @if(method_exists($rows,'links'))
        <div class="d-flex justify-content-between align-items-center mt-3">
            <small class="text-muted">
                Affichage {{ $rows->firstItem() }} à {{ $rows->lastItem() }}
                sur {{ $rows->total() }} résultats
            </small>

            {{ $rows->withQueryString()->links() }}
        </div>
        @endif

    </div>
</div>

@endsection
