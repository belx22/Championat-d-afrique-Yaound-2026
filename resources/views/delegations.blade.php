@extends('adminTheme.default')

@section('title', 'Gestion des délégations')

@section('content')

<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestion des délégations</h1>
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createDelegationModal">
            <i class="fas fa-plus"></i> Nouvelle délégation
        </button>
    </div>



    <!-- FLASH SUCCESS -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <!-- FLASH ERROR -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Erreur :</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif


<div class="card shadow mb-4">
    <div class="card-body">

     <form method="GET" class="form-inline mb-3">
    <div class="input-group w-100">
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               class="form-control"
               placeholder="Rechercher par pays, fédération ou admin…">

        <div class="input-group-append">
            <button class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>

            @if(request('search'))
                <a href="{{ route('delegations') }}"
                   class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </div>
    </div>
</form>

    </div>
</div>
    <!-- LISTING -->
<div class="card shadow mb-4">



    {{-- HEADER --}}
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-flag mr-1"></i> Liste des délégations
        </h6>

        <span class="badge badge-info">
            {{ $delegations->count() }} délégation(s)
        </span>
    </div>

    {{-- BODY --}}
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="thead-light">
                    <tr class="text-uppercase text-xs text-gray-600">
                        <th>#</th>
                        <th>Pays</th>
                        <th>Fédération</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Admin fédération</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($delegations as $index => $delegation)
                    <tr>

                        {{-- INDEX --}}
                        <td class="font-weight-bold text-muted">
                            {{ $index + 1 }}
                        </td>

                        {{-- COUNTRY --}}
                        <td>
                            <i class="fas fa-flag text-primary mr-1"></i>
                            <strong>{{ $delegation->country }}</strong>
                        </td>

                        {{-- FEDERATION --}}
                        <td>
                            {{ $delegation->federation_name }}
                        </td>

                        {{-- CONTACT --}}
                        <td>
                            {{ $delegation->contact_person ?? '—' }}
                        </td>

                        {{-- EMAIL --}}
                        <td>
                            <a href="mailto:{{ $delegation->email }}"
                               class="text-decoration-none">
                                <i class="fas fa-envelope text-muted mr-1"></i>
                                {{ $delegation->email }}
                            </a>
                        </td>

                        {{-- PHONE --}}
                        <td>
                            <i class="fas fa-phone text-muted mr-1"></i>
                            {{ $delegation->phone ?? '—' }}
                        </td>

                        {{-- ADMIN FEDERATION --}}
                        <td>
                            @if($delegation->user)
                                <span class="badge badge-success">
                                    <i class="fas fa-user-shield mr-1"></i>
                                    {{ $delegation->user->email }}
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    Aucun
                                </span>
                            @endif
                        </td>

                        {{-- ACTIONS --}}
                        <td class="text-center">
                            <div class="btn-group" role="group">

                                <button class="btn btn-sm btn-outline-warning"
                                        data-toggle="modal"
                                        data-target="#editDelegation{{ $delegation->id }}"
                                        title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button class="btn btn-sm btn-outline-danger"
                                        data-toggle="modal"
                                        data-target="#deleteDelegation{{ $delegation->id }}"
                                        title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>

                            </div>
                        </td>

                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>

<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-muted">
        Affichage {{ $delegations->firstItem() }}
        à {{ $delegations->lastItem() }}
        sur {{ $delegations->total() }} délégations
    </small>

    {{ $delegations->links() }}
</div>






       
<!-- ================= CREATE MODAL ================= -->
<div class="modal fade" id="createDelegationModal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" action="{{ route('delegations.store') }}">
                @csrf

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Nouvelle délégation</h5>
                    <button class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    @include('partials.form', ['delegation' => null])
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button class="btn btn-primary">Enregistrer</button>
                </div>
            </form>

           


        </div>
    </div>
</div>

<!-- ================= EDIT MODALS ================= -->
@foreach($delegations as $delegation)
<div class="modal fade" id="editDelegation{{ $delegation->id }}">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" action="{{ route('delegations.update', $delegation) }}">
                @csrf
                @method('PUT')

                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier la délégation</h5>
                    <button class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    @include('partials.form', ['delegation' => $delegation])
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button class="btn btn-warning">Mettre à jour</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endforeach

<!-- ================= DELETE MODALS ================= -->
@foreach($delegations as $delegation)
<div class="modal fade" id="deleteDelegation{{ $delegation->id }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" action="{{ route('delegations.destroy', $delegation) }}">
                @csrf
                @method('DELETE')

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash"></i> Supprimer</h5>
                    <button class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body text-center">
                    <p>Supprimer la délégation :</p>
                    <strong>{{ $delegation->federation_name }} ({{ $delegation->country }})</strong>
                </div>

                <div class="modal-footer justify-content-center">
                    <button class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button class="btn btn-danger">Supprimer</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endforeach



@push('scripts')
<script>
function generatePassword(length = 10) {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#';
    let pass = '';
    for (let i = 0; i < length; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return pass;
}

$('#createDelegationModal').on('shown.bs.modal', function () {
    $('#generatedPassword').val(generatePassword());
});
</script>
@endpush

@endsection
