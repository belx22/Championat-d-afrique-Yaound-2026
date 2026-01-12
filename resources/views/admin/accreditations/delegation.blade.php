@extends('adminTheme.default')

@section('content')

<h1 class="h3 mb-4">
    Accréditations – {{ $delegation->country }}
</h1>

<div class="row mb-4">

<div class="col-md-3">
    <div class="card border-left-info">
        <div class="card-body">
            <strong>Total membres</strong><br>
            {{ $delegation->nominativeRegistrations->count() }}
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="card border-left-success">
        <div class="card-body">
            <strong>Badges générés</strong><br>
            {{ $delegation->nominativeRegistrations->whereNotNull('accreditation')->count() }}
        </div>
    </div>
</div>

</div>

<a href="{{ route('admin.accreditations.badges.pdf',$delegation) }}"
   class="btn btn-danger mb-3"
   target="_blank">
   <i class="fas fa-file-pdf"></i> Export PDF (A4)
</a>

<form method="GET" class="mb-3">
    <div class="form-row">
        <div class="col-md-3">
            <select name="role" class="form-control">
                <option value="">-- Rôle --</option>
                <option value="gymnast">Gymnast</option>
                <option value="coach">Coach</option>
                <option value="judge">Judge</option>
            </select>
        </div>

        <div class="col-md-3">
            <select name="zone" class="form-control">
                <option value="">-- Zone --</option>
                <option value="training">Training</option>
                <option value="competition">Competition</option>
                <option value="restaurant">Restaurant</option>
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary">
                Filtrer
            </button>
        </div>
    </div>
</form>


@foreach($members as $role => $group)
<div class="card shadow mb-4">

    <div class="card-header bg-primary text-white">
        <strong>{{ strtoupper($role) }}</strong>
        <span class="badge badge-light ml-2">
            {{ $group->count() }} membres
        </span>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th>Nom</th>
                    <th>FIG ID</th>
                    <th>Zones d’accès</th>
                    <th>Badge ID</th>
                    <th>Badge</th>
                </tr>
            </thead>
            <tbody>
            @foreach($group as $m)
                <tr>
                    <td>{{ $m->family_name }} {{ $m->given_name }}</td>
                    <td>{{ $m->fig_id ?? '—' }}</td>

                    <td>

                      
                        @foreach($m->accreditation?->access_zones ?? [] as $zone)
                            <span class="badge badge-info">
                                {{ ucfirst($zone) }}
                            </span>
                        @endforeach
                    </td>
                    @if($m->accreditation)
                        <td>
                                 {{ $m->accreditation->badge_number }}
                        </td>
                    <td class="text-center">
                        
                            <span class="badge badge-{{ $m->accreditation?->status === 'valide' ? 'success' : 'warning' }}">
            {{ $m->accreditation?->status ?? 'non généré' }}
        </span>
                        @else
                            <span class="badge badge-secondary">Non généré</span>
                        @endif

                                @if(!$m->accreditation)
                                    <form method="POST" action="{{ route('admin.accreditations.generate',$m) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">Générer badge</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.accreditations.print',$m->accreditation) }}"
                                    class="btn btn-sm btn-secondary">Imprimer</a>
                                    
                                @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

</div>
@endforeach

@endsection
