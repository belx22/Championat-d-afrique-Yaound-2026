@extends('adminTheme.default')

@section('content')

<div class="container-fluid">

<h1 class="h3 mb-4 text-gray-800">
    Provisional Registrations – Validation
</h1>

<table class="table table-bordered table-hover">
    <thead class="thead-light">
        <tr>
            <th>Pays</th>
            <th>Fédération</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($registrations as $reg)
        <tr>
            <td>{{ $reg->delegation->country }}</td>
            <td>{{ $reg->delegation->federation_name }}</td>
            <td>
                <span class="badge badge-{{ 
                    $reg->status === 'valide' ? 'success' :
                    ($reg->status === 'rejete' ? 'danger' : 'warning')
                }}">
                    {{ strtoupper($reg->status) }}
                </span>
            </td>
            <td>
                <a href="{{ route('admin_local.provisional.show', $reg) }}"
                   class="btn btn-sm btn-primary">
                   Voir
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

</div>
@endsection
