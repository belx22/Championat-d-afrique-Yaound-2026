@extends('adminTheme.default')

@section('content')

<h1 class="h3 mb-3">
    Accréditations – {{ $delegation->country }}
</h1>

<a href="{{ route('admin.accreditations.print.delegation',$delegation) }}"
   class="btn btn-success mb-3">
   Imprimer tous les badges
</a>

<table class="table table-bordered table-sm">
<thead>
<tr>
    <th>Nom</th>
    <th>Fonction</th>
    <th>Badge</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>
@foreach($members as $m)
<tr>
    <td>{{ $m->family_name }} {{ $m->given_name }}</td>
    <td>{{ ucfirst($m->function) }}</td>

    <td>
        @if($m->accreditation)
            {{ $m->accreditation->badge_number }}
        @else
            —
        @endif
    </td>

    <td>
        <span class="badge badge-{{ $m->accreditation?->status === 'valide' ? 'success' : 'warning' }}">
            {{ $m->accreditation?->status ?? 'non généré' }}
        </span>
    </td>

    <td>
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

@endsection
