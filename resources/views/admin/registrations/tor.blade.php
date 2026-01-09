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
    <div class="card-header font-weight-bold text-primary">
        Delegation Information
    </div>
    <div class="card-body">
        <p><strong>Country :</strong> {{ $delegation->country }}</p>
        <p><strong>Federation :</strong> {{ $delegation->federation_name }}</p>
        <p><strong>Email :</strong> {{ $delegation->email }}</p>

        @if($delegation->delegationInfo)
            <hr>
            <p><strong>Arrival :</strong> {{ $delegation->delegationInfo->arrival_date }}</p>
            <p><strong>Departure :</strong> {{ $delegation->delegationInfo->departure_date }}</p>



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
        @else
            <span class="badge badge-danger">Delegation Info Missing</span>
        @endif
    </div>
</div>

{{-- ================================================= --}}
{{-- PROVISIONAL REGISTRATION --}}
{{-- ================================================= --}}
<div class="card shadow mb-4">
    <div class="card-header font-weight-bold">
        Provisional Registration
        @include('admin.registrations.partials.status-badge', [
            'status' => optional($delegation->provisionalRegistration)->status
        ])

        @if($delegation->provisionalRegistration)
<div class="mt-2">
    <form method="POST"
          action="{{ route('admin.registrations.validate', [$delegation,'provisional']) }}"
          class="d-inline">
        @csrf
        <button class="btn btn-sm btn-success">Valider</button>
    </form>

    <form method="POST"
          action="{{ route('admin.registrations.reject', [$delegation,'provisional']) }}"
          class="d-inline">
        @csrf
        <button class="btn btn-sm btn-danger">Rejeter</button>
    </form>
</div>
@endif

    </div>

    <div class="card-body">
        @if($delegation->provisionalRegistration)
            @foreach($delegation->provisionalRegistration->getAttributes() as $key => $value)
                @if(!in_array($key, ['id','delegation_id','created_at','updated_at']))
                    <p><strong>{{ ucfirst(str_replace('_',' ',$key)) }}</strong> : {{ $value }}</p>
                @endif
            @endforeach

            @if($delegation->provisionalRegistration->signed_document)
               

                <button class="btn btn-sm btn-info"
                                        data-toggle="modal"
                                        data-target="#previewModal"
                                        data-url="{{ asset('storage/'.$delegation->provisionalRegistration->signed_document) }}">
                                        Preview Signed Document
                                    </button>
            @endif
        @else
            <span class="badge badge-secondary">Not started</span>
        @endif
    </div>
</div>

{{-- ================================================= --}}
{{-- DEFINITIVE REGISTRATION --}}
{{-- ================================================= --}}
<div class="card shadow mb-4">
    <div class="card-header font-weight-bold">
        Definitive Registration
        @include('admin.registrations.partials.status-badge', [
            'status' => optional($delegation->definitiveRegistration)->status
        ])

        @if($delegation->definitiveRegistration)
<div class="mt-2">
    <form method="POST"
          action="{{ route('admin.registrations.validate', [$delegation,'definitive']) }}"
          class="d-inline">
        @csrf
        <button class="btn btn-sm btn-success">Valider</button>
    </form>

    <form method="POST"
          action="{{ route('admin.registrations.reject', [$delegation,'definitive']) }}"
          class="d-inline">
        @csrf
        <button class="btn btn-sm btn-danger">Rejeter</button>
    </form>
</div>
@endif

    </div>

    <div class="card-body">
        @if($delegation->definitiveRegistration)
            @foreach($delegation->definitiveRegistration->getAttributes() as $key => $value)
                @if(!in_array($key, ['id','delegation_id','created_at','updated_at','signed_document']))
                    <p><strong>{{ ucfirst(str_replace('_',' ',$key)) }}</strong> : {{ $value }}</p>
                @endif
            @endforeach

            @if($delegation->definitiveRegistration->signed_document)
               

                          


                <button class="btn btn-sm btn-info"
                                        data-toggle="modal"
                                        data-target="#previewModal"
                                       data-url="{{ asset('storage/'.$delegation->definitiveRegistration->signed_document) }}">
                                        Preview Signed Document
                                    </button>
            @endif
        @else
            <span class="badge badge-secondary">Not started</span>
        @endif
    </div>
</div>

{{-- ================================================= --}}
{{-- NOMINATIVE REGISTRATION --}}
{{-- ================================================= --}}
<div class="card shadow mb-4">
    <div class="card-header font-weight-bold">
        Nominative Registration (Members)
        <span class="badge badge-primary">
            {{ $delegation->nominativeRegistrations->count() }} members
        </span>
    </div>

    <div class="card-body">
        @if($delegation->nominativeRegistrations->count())
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Name</th>
                        <th>Function</th>
                        <th>Discipline</th>
                        <th>Files</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($delegation->nominativeRegistrations as $m)
                    <tr>
                        <td>{{ $m->family_name }} {{ $m->given_name }}</td>
                        <td>{{ ucfirst($m->function) }}</td>
                        <td>{{ $m->discipline ?? '-' }}</td>
                        <td>
                           
                                
                            
                                        <button class="btn btn-sm btn-info"
                                        data-toggle="modal"
                                        data-target="#previewModal"
                                      data-url="{{ asset('storage/'.$m->passport_scan) }}">
                                       <i class="fas fa-eye"></i> Preview Signed Document
                                    </button>
                           


                                  

             <button class="btn btn-sm btn-info"
                        data-toggle="modal"
                        data-target="#previewModal"
                        data-type="image"
                        data-url="{{ asset('storage/'.$m->photo_4x4) }}">
                    <i class="fas fa-flag"></i> PHOTO 4x4
                </button>


                            @if($m->music_file)


                                    <button class="btn btn-sm btn-warning"
                        data-toggle="modal"
                        data-target="#previewModal"
                        data-type="audio"
                       data-url="{{ asset('storage/'.$m->music_file) }}">
                    <i class="fas fa-music"></i> Play Audio
                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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




