@extends('adminTheme.default')

@section('content')

@php
    // readonly si déjà validé
    $isReadOnly = ($registration->status === 'valide');
@endphp






<div class="container-fluid">


    <div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-left-info shadow">
            <div class="card-body">
                <strong>Status</strong><br>
                <span class="badge badge-{{ 
                    $registration->status === 'valide' ? 'success' :
                    ($registration->status === 'en_attente' ? 'warning' :
                    ($registration->status === 'rejete' ? 'danger' : 'secondary'))
                }}">
                    {{ strtoupper($registration->status) }}
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-left-primary shadow">
            <div class="card-body">
                <strong>Total Members</strong><br>
                <h4>{{ $registration->totalMembers() }}</h4>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-left-success shadow">
            <div class="card-body">
                <strong>Signed Document</strong><br>

                @if($registration->signed_document)
                    <button class="btn btn-sm btn-primary"
                        data-toggle="modal"
                        data-target="#previewSignedDocumentModal">
                    <i class="fas fa-eye"></i> Preview
                </button>




                    
                @else
                    <span class="text-danger">Not uploaded</span>
                @endif
            </div>
        </div>
    </div>
</div>


    <h1 class="h3 mb-4 text-gray-800">
        Definitive Registration – Delegation Members
    </h1>

    {{-- Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ================= FORM ================= --}}
    <form method="POST" action="{{ route('definitive') }}" enctype="multipart/form-data">
        @csrf

        {{-- ================= TABLE GYMNASTS ================= --}}
        <div class="card shadow mb-4">
            <div class="card-body">

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
                                <input type="number" min="0" name="mag_junior"
                                       class="form-control text-center"
                                       value="{{ old('mag_junior', $registration->mag_junior) }}"
                                       {{ $isReadOnly ? 'readonly' : '' }}>
                            </td>
                            <td>
                                <input type="number" min="0" name="mag_senior"
                                       class="form-control text-center"
                                       value="{{ old('mag_senior', $registration->mag_senior) }}"
                                       {{ $isReadOnly ? 'readonly' : '' }}>
                            </td>
                            <td>
                                <input type="number" min="0" name="wag_junior"
                                       class="form-control text-center"
                                       value="{{ old('wag_junior', $registration->wag_junior) }}"
                                       {{ $isReadOnly ? 'readonly' : '' }}>
                            </td>
                            <td>
                                <input type="number" min="0" name="wag_senior"
                                       class="form-control text-center"
                                       value="{{ old('wag_senior', $registration->wag_senior) }}"
                                       {{ $isReadOnly ? 'readonly' : '' }}>
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- ================= DELEGATION MEMBERS ================= --}}
                <table class="table table-bordered mt-4">
                    <thead class="thead-light">
                        <tr>
                            <th>Delegation Members</th>
                            <th class="text-center" style="width:150px;">Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $fields = [
                                'gymnast_team'        => 'Gymnast Team',
                                'gymnast_individuals' => 'Gymnast Individuals',
                                'coach'               => 'Coach',
                                'judges_total'        => 'Total Judges (Sn + Jn)',
                                'head_of_delegation'  => 'Head of Delegation (Sn + Jn)',
                                'doctor_paramedics'   => 'Doctor / Paramedics (Sn + Jn)',
                                'team_manager'        => 'Team Manager',
                            ];
                        @endphp

                        @foreach($fields as $field => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td>
                                    <input type="number" min="0"
                                           name="{{ $field }}"
                                           class="form-control text-center"
                                           value="{{ old($field, $registration->$field) }}"
                                           {{ $isReadOnly ? 'readonly' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                                @if(!$isReadOnly)
                                <hr>
                                <div class="form-group">
                                    <label>Signed Definitive Registration (PDF / Image)</label>
                                    <input type="file"
                                        name="signed_document"
                                        class="form-control"
                                        accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                @endif


            </div>
        </div>

        {{-- ================= ACTIONS ================= --}}
        @if($registration->status !== 'valide')
            <button type="submit" class="btn btn-primary">
                Enregistrer / Soumettre
            </button>
        @else
            <div class="alert alert-info">
                Cette étape a été validée. Les données sont en lecture seule.
            </div>
        @endif

    </form>

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
                    Document signé – Definitive Registration
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
                        'definitive',
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
