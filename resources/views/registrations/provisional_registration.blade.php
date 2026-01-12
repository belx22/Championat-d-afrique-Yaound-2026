@extends('adminTheme.default')

@section('content')

<div class="container-fluid">

    <!-- TITRE -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Provisional Registration – Delegation Members
        </h1>
    </div>

    <!-- STATUT -->
    <div class="alert {{ $registration->status === 'valide' ? 'alert-success' : 'alert-warning' }}">
        <strong>Status :</strong>
        {{ strtoupper($registration->status) }}
    </div>


<div class="col-xl-4 col-md-6 mb-4">
    <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                Total personnes déclarées
            </div>
            <div class="h5 mb-0 font-weight-bold text-gray-800">
                {{ $registration->total_persons }}
            </div>
        </div>
    </div>
</div>

    <!-- MESSAGE SUCCESS -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- FORMULAIRE -->
    <form method="POST" action="{{ route('registrations.provisional_registration.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="card shadow mb-4">
            <div class="card-body">

                <!-- TABLE STYLE DOCUMENT OFFICIEL -->
                <table class="table table-bordered text-center align-middle">

                    <thead class="thead-light">
                        <tr>
                            <th rowspan="2">Category</th>
                            <th colspan="2">MAG</th>
                            <th colspan="2">WAG</th>
                        </tr>
                        <tr>
                            <th>Junior</th>
                            <th>Senior</th>
                            <th>Junior</th>
                            <th>Senior</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td><strong>Gymnasts</strong></td>

                            <td>
                                <input type="number" min="0"
                                    name="mag_junior"
                                    class="form-control text-center"
                                    value="{{ old('mag_junior', $registration->mag_junior) }}"
                                    {{ $registration->status === 'valide' ? 'disabled' : '' }}>
                            </td>

                            <td>
                                <input type="number" min="0"
                                    name="mag_senior"
                                    class="form-control text-center"
                                    value="{{ old('mag_senior', $registration->mag_senior) }}"
                                    {{ $registration->status === 'valide' ? 'disabled' : '' }}>
                            </td>

                            <td>
                                <input type="number" min="0"
                                    name="wag_junior"
                                    class="form-control text-center"
                                    value="{{ old('wag_junior', $registration->wag_junior) }}"
                                    {{ $registration->status === 'valide' ? 'disabled' : '' }}>
                            </td>

                            <td>
                                <input type="number" min="0"
                                    name="wag_senior"
                                    class="form-control text-center"
                                    value="{{ old('wag_senior', $registration->wag_senior) }}"
                                    {{ $registration->status === 'valide' ? 'disabled' : '' }}>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- AUTRES MEMBRES – STRUCTURE DOCUMENT -->
                <table class="table table-bordered mt-4">

                    <thead class="thead-light">
                        <tr>
                            <th>Delegation Members</th>
                            <th class="text-center">Number</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                        $fields = [
                            'gymnast_team' => 'Gymnast Team',
                            'gymnast_individuals' => 'Gymnast Individuals',
                            'coach' => 'Coach',
                            'judges_total' => 'Total Judges (Sn + Jn)',
                            'head_of_delegation' => 'Head of Delegation (Sn + Jn)',
                            'doctor_paramedics' => 'Doctor / Paramedics (Sn + Jn)',
                            'team_manager' => 'Team Manager',
                        ];
                        @endphp

                        @foreach($fields as $field => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td style="width: 150px;">
                                <input type="number" min="0"
                                    name="{{ $field }}"
                                    class="form-control text-center"
                                    value="{{ old($field, $registration->$field) }}"
                                    {{ $registration->status === 'valide' ? 'disabled' : '' }}>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
 <div class="form-group mt-3">
                <label class="font-weight-bold">
                    Document signé par la Fédération (PDF / Image)
                </label>

                <input type="file"
                    name="signed_document"
                    class="form-control"
                    accept=".pdf,.jpg,.jpeg,.png">

                @error('signed_document')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>







            </div>

           
            <!-- ACTIONS -->
            <div class="card-footer text-right">
                  @if($registration->status !== 'valide')
                <button  class="btn btn-primary">
                    Submit 
                </button>
            @else
                <div class="alert alert-info">
                    Cette étape a été validée. Les données sont en lecture seule.
                </div>
            @endif
            
            </div>
        </div>
    </form>



            @if($registration->signed_document)
         
            <div class="mt-3 d-flex gap-2">

                <!-- Aperçu -->
                <button class="btn btn-sm btn-primary"
                        data-toggle="modal"
                        data-target="#previewSignedDocumentModal">
                    <i class="fas fa-eye"></i> Visualiser
                </button>

                <!-- Téléchargement -->
                <a href="{{ asset('storage/'.$registration->signed_document) }}"
                download
                class="btn btn-sm btn-secondary">
                    <i class="fas fa-download"></i> Télécharger
                </a>

            </div>


            @else
            <div class="mt-3">
                <span class="badge badge-warning">
                    <i class="fas fa-exclamation-triangle"></i> Document manquant
                </span>
            </div>
            @endif

    <!-- VALIDATION ETAPE -->

</div>




@if($registration->signed_document)

<div class="modal fade"
     id="previewSignedDocumentModal"
     tabindex="-1"
     role="dialog"
     data-backdrop="static"
     data-keyboard="false">

    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">

            {{-- HEADER --}}
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-signature"></i>
                    Document signé – Provisional Registration
                </h5>
                <button type="button"
                        class="close text-white"
                        data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-0" style="height:90vh">

                @php
                    $previewUrl = route('secure.preview', [
                        'provisional',
                        $registration->id,
                        'signed_document'
                    ]);

                    $ext = strtolower(pathinfo(
                        $registration->signed_document,
                        PATHINFO_EXTENSION
                    ));
                @endphp

                {{-- PDF --}}
                @if($ext === 'pdf')
                    <iframe src="{{ $previewUrl }}"
                            style="width:100%;height:100%;border:none;"
                            loading="lazy">
                    </iframe>

                {{-- IMAGE --}}
                @elseif(in_array($ext, ['jpg','jpeg','png','webp']))
                    <div class="d-flex justify-content-center align-items-center h-100 bg-light">
                        <img src="{{ $previewUrl }}"
                             class="img-fluid rounded shadow"
                             style="max-height:100%;max-width:100%;object-fit:contain;"
                             alt="Document signé">
                    </div>

                {{-- AUTRE --}}
                @else
                    <div class="alert alert-warning m-4 text-center">
                        Aperçu non disponible pour ce format.
                    </div>
                @endif

            </div>

            {{-- FOOTER --}}
            <div class="modal-footer justify-content-between">
                <span class="text-muted small">
                    <i class="fas fa-lock"></i>
                    Consultation sécurisée – téléchargement désactivé
                </span>

                <button class="btn btn-outline-dark"
                        data-dismiss="modal">
                    Fermer
                </button>
            </div>

        </div>
    </div>
</div>

@endif




@endsection
