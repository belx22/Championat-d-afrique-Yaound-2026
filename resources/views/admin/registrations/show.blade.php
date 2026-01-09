@extends('adminTheme.default')

@section('content')

<h1 class="h3 mb-4 text-gray-800">
    Delegation – {{ $delegation->country }}
</h1>




@if($delegation->hasAnyFile())
    <a href="{{ route('admin.registrations.download', $delegation) }}"
       class="btn btn-primary">
        <i class="fas fa-file-archive"></i>
        Télécharger dossier
    </a>
@else
    <button class="btn btn-secondary" disabled>
        Aucun fichier disponible
    </button>
@endif


{{-- ================================================= --}}
{{-- INFORMATIONS DÉLÉGATION --}}
{{-- ================================================= --}}
<div class="card shadow mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-flag mr-1"></i> Delegation Information
        </h6>
    </div>

    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <strong>Country</strong>
                <div class="text-muted">{{ $delegation->country }}</div>
            </div>
            <div class="col-md-4">
                <strong>Federation</strong>
                <div class="text-muted">{{ $delegation->federation_name }}</div>
            </div>
            <div class="col-md-4">
                <strong>Email</strong>
                <div class="text-muted">{{ $delegation->email }}</div>
            </div>
        </div>

        @if($delegation->delegationInfo)
            <hr>

            <div class="row align-items-center mb-3">
                <div class="col-md-3">
                    <strong>Arrival</strong>
                    <div>{{ $delegation->delegationInfo->arrival_date }}</div>
                </div>

                <div class="col-md-3">
                    <strong>Departure</strong>
                    <div>{{ $delegation->delegationInfo->departure_date }}</div>
                </div>

                <div class="col-md-6 text-right">
                      <button class="btn btn-sm btn-info"
                        data-toggle="modal"
                        data-target="#previewModal"
                        data-type="image"
                        data-url="{{ asset('storage/'.$delegation->delegationInfo->flag_image) }}">
                    <i class="fas fa-flag"></i> View Flag
                </button>


            <button class="btn btn-sm btn-warning"
                        data-toggle="modal"
                        data-target="#previewModal"
                        data-type="audio"
                        data-url="{{ asset('storage/'.$delegation->delegationInfo->national_anthem) }}">
                    <i class="fas fa-music"></i> Play Anthem
                </button>
                </div>
            </div>
        @else
            <span class="badge badge-danger">
                <i class="fas fa-exclamation-triangle"></i> Delegation info missing
            </span>
        @endif
    </div>
</div>

{{-- ================================================= --}}
{{-- PROVISIONAL REGISTRATION --}}
{{-- ================================================= --}}
<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="font-weight-bold mb-0">
            <i class="fas fa-clipboard-check mr-1"></i> Provisional Registration
        </h6>

        @include('admin.registrations.partials.status-badge', [
            'status' => optional($delegation->provisionalRegistration)->status
        ])
    </div>

    <div class="card-body">

        @if($delegation->provisionalRegistration)

            <div class="row">
                @foreach($delegation->provisionalRegistration->getAttributes() as $key => $value)
                    @continue(in_array($key, ['id','delegation_id','created_at','updated_at','signed_document']))

                    <div class="col-md-4 mb-2">
                        <small class="text-muted">
                            {{ ucfirst(str_replace('_',' ',$key)) }}
                        </small>
                        <div class="font-weight-bold">{{ $value }}</div>
                    </div>
                @endforeach
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center">
                @if($delegation->provisionalRegistration->signed_document)
                   
                     <button class="btn btn-outline-info btn-sm preview-btn"
                                        data-toggle="modal"
                                        data-target="#previewModal"
                                        data-url="{{ asset('storage/'.$delegation->provisionalRegistration->signed_document) }}">
                                       <i class="fas fa-file-pdf"></i> Preview Signed Document
                                    </button>
                @endif

                <div>
                    <form method="POST"
                          action="{{ route('admin.registrations.validate', [$delegation,'provisional']) }}"
                          class="d-inline">
                        @csrf
                        <button class="btn btn-success btn-sm">
                            <i class="fas fa-check"></i> Validate
                        </button>
                    </form>

                    <form method="POST"
                          action="{{ route('admin.registrations.reject', [$delegation,'provisional']) }}"
                          class="d-inline">
                        @csrf
                        <button class="btn btn-danger btn-sm">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </form>
                </div>
            </div>

        @else
            <span class="badge badge-secondary">Not started</span>
        @endif
    </div>
</div>


{{-- ================================================= --}}
{{-- DEFINITIVE REGISTRATION --}}
{{-- ================================================= --}}
{{-- ================================================= --}}
{{-- DEFINITIVE REGISTRATION --}}
{{-- ================================================= --}}
<div class="card shadow mb-4">

    {{-- HEADER --}}
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-warning">
            <i class="fas fa-clipboard-list mr-1"></i> Definitive Registration
        </h6>

        @include('admin.registrations.partials.status-badge', [
            'status' => optional($delegation->definitiveRegistration)->status
        ])
    </div>

    {{-- BODY --}}
    <div class="card-body">

        @if($delegation->definitiveRegistration)

            {{-- DONNÉES CHIFFRÉES --}}
            <div class="row">
                @foreach($delegation->definitiveRegistration->getAttributes() as $key => $value)

                    @continue(in_array($key, [
                        'id','delegation_id',
                        'created_at','updated_at',
                        'signed_document','status'
                    ]))

                    <div class="col-md-4 mb-3">
                        <small class="text-muted text-uppercase">
                            {{ str_replace('_',' ',$key) }}
                        </small>
                        <div class="font-weight-bold">
                            {{ $value }}
                        </div>
                    </div>

                @endforeach
            </div>

            <hr>

            {{-- DOCUMENT SIGNÉ + ACTIONS --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap">

                {{-- DOCUMENT --}}
                @if($delegation->definitiveRegistration->signed_document)
                  
                    <button class="btn btn-outline-info btn-sm preview-btn"
                                        data-toggle="modal"
                                        data-target="#previewModal"
                                       data-url="{{ asset('storage/'.$delegation->definitiveRegistration->signed_document) }}">
                                        <i class="fas fa-file-pdf"></i>Preview Signed Document
                                    </button>
                @else
                    <span class="badge badge-danger">
                        <i class="fas fa-exclamation-circle"></i> Signed document missing
                    </span>
                @endif

                {{-- ACTIONS ADMIN --}}
                <div class="mt-2 mt-md-0">

                    <form method="POST"
                          action="{{ route('admin.registrations.validate', [$delegation,'definitive']) }}"
                          class="d-inline">
                        @csrf
                        <button class="btn btn-success btn-sm">
                            <i class="fas fa-check"></i> Validate
                        </button>
                    </form>

                    <form method="POST"
                          action="{{ route('admin.registrations.reject', [$delegation,'definitive']) }}"
                          class="d-inline">
                        @csrf
                        <button class="btn btn-danger btn-sm">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </form>

                </div>
            </div>

        @else
            <span class="badge badge-secondary">
                <i class="fas fa-clock"></i> Not started
            </span>
        @endif

    </div>
</div>


{{-- ================================================= --}}
{{-- NOMINATIVE REGISTRATION --}}
{{-- ================================================= --}}<div class="card shadow mb-4">
    <div class="card-header d-flex justify-content-between">
        <h6 class="font-weight-bold">
            <i class="fas fa-users mr-1"></i> Nominative Registration
        </h6>
        <span class="badge badge-primary">
            {{ $delegation->nominativeRegistrations->count() }} members
        </span>
    </div>

    <div class="card-body">
        @if($delegation->nominativeRegistrations->count())
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Name</th>
                            <th>Function</th>
                            <th>Discipline</th>
                            <th class="text-center">Documents</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($delegation->nominativeRegistrations as $m)
                        <tr>
                            <td>{{ $m->family_name }} {{ $m->given_name }}</td>
                            <td>
                                <span class="badge badge-info">
                                    {{ strtoupper($m->function) }}
                                </span>
                            </td>
                            <td>{{ $m->discipline ?? '-' }}</td>
                            <td class="text-center">

                                <button class="btn btn-outline-info btn-xs preview-btn"
                                        data-toggle="modal"
                                        data-target="#previewModal"
                                      data-url="{{ asset('storage/'.$m->passport_scan) }}">
                                    <i class="fas fa-passport"></i>
                                </button>

                                <button class="btn btn-outline-secondary btn-xs preview-btn"
                                        data-toggle="modal"
                                        data-target="#previewModal"
                                        data-type="image"
                                        data-url="{{ asset('storage/'.$m->photo_4x4) }}">
                                    <i class="fas fa-image"></i>
                                </button>

                                @if($m->music_file)
                                    

                                    <button class="btn btn-outline-warning btn-xs preview-btn"
                        data-toggle="modal"
                        data-target="#previewModal"
                        data-type="audio"
                       data-url="{{ asset('storage/'.$m->music_file) }}">
                    <i class="fas fa-music"></i>
                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <span class="badge badge-secondary">No members added</span>
        @endif
    </div>
</div>


{{-- ================================================= --}}
{{-- PREVIEW MODAL --}}
{{-- ================================================= --}}



<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Document Preview</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body" style="height:80vh">
                <iframe id="previewFrame"
                        src=""
                        style="width:100%;height:100%;border:none;">
                </iframe>
            </div>

        </div>
    </div>
</div>



<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Preview</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body p-0 d-flex justify-content-center align-items-center"
                 style="height:80vh;">

                <!-- IMAGE -->
                <img id="previewImage"
                     src=""
                     class="img-fluid d-none"
                     style="max-height:100%;max-width:100%;" />

                <!-- AUDIO -->
                <audio id="previewAudio"
                       controls
                       class="w-75 d-none">
                </audio>

            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
$('#previewModal').on('show.bs.modal', function (event) {

    let button = $(event.relatedTarget);
    let url = button.data('url');
    let type = button.data('type');

    let modal = $(this);

    // Reset
    modal.find('#previewImage').addClass('d-none').attr('src','');
    modal.find('#previewAudio').addClass('d-none').attr('src','');

    if (type === 'image') {
        modal.find('#previewImage')
            .removeClass('d-none')
            .attr('src', url);
    }

    if (type === 'audio') {
        modal.find('#previewAudio')
            .removeClass('d-none')
            .attr('src', url)[0].load();
    }
});

// Nettoyage à la fermeture
$('#previewModal').on('hidden.bs.modal', function () {
    $('#previewAudio').trigger('pause').attr('src','');
});
</script>
@endpush


@endsection




