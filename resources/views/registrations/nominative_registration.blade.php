@extends('adminTheme.default')

@section('content')

<h1 class="h3 mb-4 text-gray-800">
    Nominative Registration – Delegation Members
</h1>



@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
{{-- ================================================= --}}
{{-- DELEGATION INFORMATION (UNE SEULE FOIS) --}}
{{-- ================================================= --}}
<div class="card shadow mb-4">
    <div class="card-header font-weight-bold text-primary">
        Delegation Information
    </div>

      {{-- Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card-body">
        <form method="POST"
              action="{{ route('delegation.info.store') }}"
              enctype="multipart/form-data">
            @csrf

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Arrival Date</label>
                    <input type="date"
                           name="arrival_date"
                           class="form-control"
                           value="{{ $info->arrival_date ?? '' }}"
                           required>
                </div>

                <div class="form-group col-md-3">
                    <label>Departure Date</label>
                    <input type="date"
                           name="departure_date"
                           class="form-control"
                           value="{{ $info->departure_date ?? '' }}"
                           required>
                </div>

                <div class="form-group col-md-3">
                    <label>National Flag</label>
                    <input type="file"
                           name="flag_image"
                           class="form-control"
                           accept="image/*"
                           {{ $info ? '' : 'required' }}>
                </div>

                <div class="form-group col-md-3">
                    <label>National Anthem</label>
                    <input type="file"
                           name="national_anthem"
                           class="form-control"
                           accept=".mp3,.wav,.ogg"
                           {{ $info ? '' : 'required' }}>
                </div>
            </div>

            <button class="btn btn-success">
                Save Delegation Info
            </button>


        </form>
<br>

           @if($info?->flag_image)
                <button class="btn btn-sm btn-info preview-btn"
                        data-toggle="modal"
                        data-target="#previewModal"
                        data-type="image"
                        data-url="{{ route('secure.preview', [
                            'context' => 'delegation',
                            'id'      => $info->id,
                            'field'   => 'flag_image'
                        ]) }}">
                    <i class="fas fa-flag"></i> View Flag
                </button>
                @endif

                @if($info?->national_anthem)
                <button class="btn btn-sm btn-warning preview-btn"
                        data-toggle="modal"
                        data-target="#previewModal"
                        data-type="audio"
                        data-url="{{ route('secure.preview', [
                            'context' => 'delegation',
                            'id'      => $info->id,
                            'field'   => 'national_anthem'
                        ]) }}">
                    <i class="fas fa-music"></i> Play Anthem
                </button>
                @endif
    </div>
</div>

{{-- ================================================= --}}
{{-- ADD NOMINATIVE MEMBER --}}
{{-- ================================================= --}}
<div class="card shadow mb-4">
    <div class="card-header font-weight-bold text-primary">
        Add Delegation Member
    </div>

    <div class="card-body">
        <form method="POST"
              action="{{ route('nominative.store') }}"
              enctype="multipart/form-data">
            @csrf

            {{-- FUNCTION & GENDER --}}
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Function</label>
                    <select name="function" id="function" class="form-control" required>
                        <option value="">-- Select --</option>
                        <option value="gymnast">Gymnast</option>
                        <option value="coach">Coach</option>
                        <option value="judge">Judge</option>
                        <option value="doctor">Doctor / Paramedic</option>
                        <option value="manager">Team Manager</option>
                        <option value="head">Head of Delegation</option>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Gender</label>
                    <select name="gender" class="form-control" required>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                </div>
            </div>

            {{-- NAME --}}
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Family Name (as passport)</label>
                    <input type="text" name="family_name" class="form-control" required>
                </div>

                <div class="form-group col-md-6">
                    <label>Given Name</label>
                    <input type="text" name="given_name" class="form-control" required>
                </div>
            </div>

            {{-- BIRTH & NATIONALITY --}}
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" required>
                </div>

                <div class="form-group col-md-4">
                    <label>Nationality</label>
                    <input type="text" name="nationality" class="form-control" required>
                </div>
            </div>

            {{-- FIG ID --}}
            <div class="form-group d-none" id="figIdField">
                <label>FIG ID</label>
                <input type="text" name="fig_id" class="form-control">
            </div>

            {{-- GYMNAST ONLY --}}
            <div id="gymnastFields" class="d-none">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Discipline</label>
                        <select name="discipline" id="discipline" class="form-control">
                            <option value="">-- Select --</option>
                            <option value="GAM">GAM</option>
                            <option value="GAF">GAF</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Category</label>
                        <select name="category" class="form-control">
                            <option value="junior">Junior</option>
                            <option value="senior">Senior</option>
                        </select>
                    </div>
                </div>

                {{-- MUSIC GAF --}}
                <div class="form-group d-none" id="musicField">
                    <label>Music (GAF only)</label>
                    <input type="file"
                           name="music_file"
                           class="form-control"
                           accept=".mp3,.mp4,.wav">
                </div>
            </div>


                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Passport Number</label>
                        <input type="text"
                            name="passport_number"
                            class="form-control"
                            required>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Passport Expiry Date</label>
                        <input type="date"
                            name="passport_expiry_date"
                            class="form-control"
                            required>
                    </div>
                </div>


            {{-- FILES --}}
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Passport Scan</label>
                    <input type="file"
                           name="passport_scan"
                           class="form-control"
                           accept=".pdf,.jpg,.jpeg,.png"
                           required>
                </div>

                <div class="form-group col-md-4">
                    <label>Photo 4×4</label>
                    <input type="file"
                           name="photo_4x4"
                           class="form-control"
                           accept="image/*"
                           required>
                </div>
            </div>

            <button class="btn btn-primary mt-3">
                Add Member
            </button>
        </form>
    </div>
</div>

{{-- ================================================= --}}
{{-- LISTING --}}
{{-- ================================================= --}}
<div class="card shadow">
    <div class="card-header font-weight-bold">
        Registered Members
    </div>

    <div class="card-body">
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th>Name</th>
                    <th>Function</th>
                    <th>Discipline</th>
                    <th>Category</th>
                    <th>Documents</th>
                </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $m)
                        <tr>
                            <td>{{ $m->family_name }} {{ $m->given_name }}</td>
                            <td>{{ ucfirst($m->function) }}</td>
                            <td>{{ $m->discipline ?? '-' }}</td>
                            <td>{{ $m->category ?? '-' }}</td>
                        
                            <td class="text-center">
                                 
                                   <button class="btn btn-sm btn-info preview-btn"
                                            data-type="pdf"
                                            data-url="{{ route('secure.preview', [
                                                'context' => 'nominative',
                                                'id'      => $m->id,
                                                'field'   => 'passport_scan'
                                            ]) }}">
                                        Passport
                                    </button>
                                   
                                   <button class="btn btn-sm btn-secondary"
                                            data-toggle="modal"
                                            data-target="#previewModal"
                                            data-type="pdf"
                                            data-url="{{ route('secure.preview', [
                                                'context' => 'nominative',
                                                'id'      => $m->id,
                                                'field'   => 'photo_4x4'
                                            ]) }}">
                                        photo_4x4
                                    </button>
                                   

                                    @if($m->music_file)
                                    <button class="btn btn-sm btn-warning preview-btn"
                                            data-toggle="modal"
                                            data-target="#previewModal"
                                            data-type="audio"
                                            data-url="{{ route('secure.preview', [
                                                'context' => 'nominative',
                                                'id'      => $m->id,
                                                'field'   => 'music_file'
                                            ]) }}">
                                        Music
                                    </button>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary"
                                            data-toggle="modal"
                                            data-target="#editMemberModal{{ $m->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>


                                    <form method="POST"
                                        action="{{ route('nominative.destroy',$m) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Delete this member?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>



                </tr>

    <div class="modal fade" id="editMemberModal{{ $m->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <form method="POST"
                  action="{{ route('nominative.update', $m->id) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- HEADER --}}
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        Edit Member – {{ $m->family_name }} {{ $m->given_name }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                {{-- BODY --}}
                <div class="modal-body">

                    {{-- FUNCTION & GENDER --}}
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Function</label>
                            <select name="function" class="form-control" required>
                                @foreach(['gymnast','coach','judge','doctor','manager','head'] as $f)
                                    <option value="{{ $f }}"
                                        {{ $m->function === $f ? 'selected' : '' }}>
                                        {{ ucfirst($f) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Gender</label>
                            <select name="gender" class="form-control" required>
                                <option value="M" {{ $m->gender === 'M' ? 'selected' : '' }}>Male</option>
                                <option value="F" {{ $m->gender === 'F' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                    </div>

                    {{-- NAMES --}}
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Family Name</label>
                            <input type="text"
                                   name="family_name"
                                   value="{{ $m->family_name }}"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Given Name</label>
                            <input type="text"
                                   name="given_name"
                                   value="{{ $m->given_name }}"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    {{-- BIRTH & NATIONALITY --}}
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Date of Birth</label>
                            <input type="date"
                                   name="date_of_birth"
                                   value="{{ $m->date_of_birth }}"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Nationality</label>
                            <input type="text"
                                   name="nationality"
                                   value="{{ $m->nationality }}"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    {{-- PASSPORT --}}
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Passport Number</label>
                            <input type="text"
                                   name="passport_number"
                                   value="{{ $m->passport_number }}"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Passport Expiry</label>
                            <input type="date"
                                   name="passport_expiry_date"
                                   value="{{ $m->passport_expiry_date }}"
                                   class="form-control"
                                   required>
                        </div>
                    </div>

                    {{-- FIG ID --}}
                    <div class="form-group">
                        <label>FIG ID</label>
                        <input type="text"
                               name="fig_id"
                               value="{{ $m->fig_id }}"
                               class="form-control">
                    </div>

                    {{-- GYMNAST ONLY --}}
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Discipline</label>
                            <select name="discipline" class="form-control">
                                <option value="">—</option>
                                <option value="GAM" {{ $m->discipline === 'GAM' ? 'selected' : '' }}>GAM</option>
                                <option value="GAF" {{ $m->discipline === 'GAF' ? 'selected' : '' }}>GAF</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Category</label>
                            <select name="category" class="form-control">
                                <option value="">—</option>
                                <option value="junior" {{ $m->category === 'junior' ? 'selected' : '' }}>Junior</option>
                                <option value="senior" {{ $m->category === 'senior' ? 'selected' : '' }}>Senior</option>
                            </select>
                        </div>
                    </div>

                    {{-- FILES --}}
                    <hr>
                    <h6 class="text-primary">Replace Documents (optional)</h6>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Passport Scan</label>
                            <input type="file"
                                   name="passport_scan"
                                   class="form-control"
                                   accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Photo 4×4</label>
                            <input type="file"
                                   name="photo_4x4"
                                   class="form-control"
                                   accept="image/*">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Music (GAF only)</label>
                            <input type="file"
                                   name="music_file"
                                   class="form-control"
                                   accept=".mp3,.mp4,.wav">
                        </div>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">
                        Cancel
                    </button>
                    <button class="btn btn-success">
                        Save Changes
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


                @endforeach
            </tbody>
        </table>
    </div>
</div>






{{-- ======================= PREVIEW MODAL (SECURE) ======================= --}}
<div class="modal fade" id="previewModal"
     tabindex="-1"
     role="dialog"
     aria-hidden="true"
     data-backdrop="static"
     data-keyboard="false">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">

            {{-- HEADER --}}
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Aperçu sécurisé
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-0" style="height:80vh;">
                <div id="previewContainer"
                     class="w-100 h-100 d-flex justify-content-center align-items-center bg-light">

                    <div class="text-center text-muted">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <div class="mt-2">Chargement...</div>
                    </div>

                </div>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer">
                <button class="btn btn-outline-dark" data-dismiss="modal">
                    Fermer
                </button>
            </div>

        </div>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // On écoute tous les boutons qui ciblent le modal preview
    document.querySelectorAll('.preview-btn').forEach(btn => {
        btn.addEventListener('click', function () {

            const url  = this.dataset.url;
            const type = (this.dataset.type || 'pdf').toLowerCase();

            const container = document.getElementById('previewContainer');

            // Loader par défaut
            container.innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <div class="mt-2">Chargement...</div>
                </div>
            `;

            // IMAGE
            if (type === 'image') {
                container.innerHTML = `
                    <div class="w-100 h-100 d-flex justify-content-center align-items-center">
                        <img src="${url}"
                             alt="Preview"
                             class="img-fluid rounded shadow"
                             style="max-height:100%;max-width:100%;object-fit:contain;">
                    </div>
                `;
            }

            // PDF
            if (type === 'pdf') {
                container.innerHTML = `
                    <iframe src="${url}"
                            style="width:100%;height:100%;border:none;"
                            loading="lazy"
                            sandbox="allow-same-origin allow-scripts">
                    </iframe>
                `;
            }

            // AUDIO
            if (type === 'audio') {
                container.innerHTML = `
                    <div class="w-100 h-100 d-flex justify-content-center align-items-center">
                        <audio controls autoplay style="width:85%;">
                            <source src="${url}">
                            Votre navigateur ne supporte pas l'audio.
                        </audio>
                    </div>
                `;
            }

        });
    });

    // Nettoyage du modal quand il se ferme (important)
    $('#previewModal').on('hidden.bs.modal', function () {
        document.getElementById('previewContainer').innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-file fa-2x"></i>
                <div class="mt-2">Aucun fichier</div>
            </div>
        `;
    });

});
</script>
@endpush



@endsection
