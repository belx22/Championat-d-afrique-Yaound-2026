@extends('adminTheme.default')

@section('title', 'Gestion des utilisateurs & rôles')

@section('content')

<div class="container-fluid">

    <!-- PAGE HEADER -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Gestion des utilisateurs & rôles
        </h1>

        <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#createUserModal">
            <i class="fas fa-user-plus fa-sm text-white-50"></i>
            Nouvel utilisateur
        </button>
    </div>

    <!-- USERS TABLE -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Liste des utilisateurs
            </h6>
            @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Erreur de validation :</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
@endif

        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Statut</th>
                            <th>Date de création</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($users as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>{{ $user->email }}</td>

                            <td>
                                @if($user->role === 'super-admin')
                                    <span class="badge badge-danger">Super Admin</span>
                                @elseif($user->role === 'admin-local')
                                    <span class="badge badge-primary">Admin Local</span>
                                @else
                                    <span class="badge badge-info">Admin Fédération</span>
                                @endif
                            </td>

                            <td>
                                @if($user->status === 'actif')
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge badge-secondary">Désactivé</span>
                                @endif
                            </td>

                            <td>{{ $user->created_at->format('d/m/Y') }}</td>

                            <td class="text-center">

                                <!-- EDIT -->
                                <button class="btn btn-sm btn-warning"
                                        data-toggle="modal"
                                        data-target="#editUserModal{{ $user->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <!-- DELETE -->
                                <button class="btn btn-sm btn-danger"
                                        data-toggle="modal"
                                        data-target="#deleteUserModal{{ $user->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>

                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- ================= CREATE USER MODAL ================= -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Créer un utilisateur
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form id="createRoleForm"
                  method="POST"
                  action="{{ route('role_inscription.store') }}">
                @csrf

                <div class="modal-body">

                    <!-- EMAIL -->
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               required>
                               @error('email')
    <div class="invalid-feedback">
        {{ $message }}
    </div>
@enderror
                    </div>

                    <!-- PASSWORD -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Mot de passe</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Confirmation</label>
                            <input type="password"
                                   name="password_confirmation"
                                   class="form-control"
                                   required>

                                   @error('password')
    <div class="invalid-feedback">
        {{ $message }}
    </div>
@enderror
                        </div>
                    </div>
                                    
                    <!-- ROLE -->
                    <div class="form-group">
                        <label>Rôle</label>
                        <select name="role" class="form-control" required>
                            <option value="">-- Sélectionner --</option>
                            <option value="super-admin">Super Admin</option>
                            <option value="admin-local">Admin Local</option>
                            <option value="admin-federation">Admin Fédération</option>
                        </select>
                    </div>

                    <!-- STATUT -->
                    <div class="form-group">
                        <label>Statut du compte</label>
                        <select name="status" class="form-control" required>
                            <option value="actif" selected>Actif</option>
                            <option value="desactiver">Désactivé</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">
                        Annuler
                    </button>
                    <button class="btn btn-primary">
                        Créer
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- ================= EDIT USER MODALS ================= -->
@foreach($users as $user)
<div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Modifier l’utilisateur
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form method="POST" action="{{ route('role_inscription.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="modal-body">

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email"
                               class="form-control"
                               value="{{ $user->email }}" required>
                    </div>

                    <div class="form-group">
                        <label>Rôle</label>
                        <select name="role" class="form-control">
                            <option value="super-admin" @selected($user->role=='super-admin')>Super Admin</option>
                            <option value="admin-local" @selected($user->role=='admin-local')>Admin Local</option>
                            <option value="admin-federation" @selected($user->role=='admin-federation')>Admin Fédération</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Nouveau mot de passe (optionnel)</label>
                        <input type="password" name="password" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Statut</label>
                        <select name="status" class="form-control">
                            <option value="actif" @selected($user->status=='actif')>Actif</option>
                            <option value="desactiver" @selected($user->status=='desactiver')>Désactivé</option>
                        </select>
                    </div>

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

<!-- ================= DELETE USER MODALS ================= -->
@foreach($users as $user)
<div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash"></i> Supprimer l’utilisateur
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form method="POST" action="{{ route('role_inscription.destroy', $user) }}">
                @csrf
                @method('DELETE')

                <div class="modal-body text-center">
                    <p>Supprimer l’utilisateur :</p>
                    <strong>{{ $user->email }}</strong>
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

@endsection
