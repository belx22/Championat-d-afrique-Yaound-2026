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
                    <button class="btn btn-sm btn-info"
                        data-toggle="modal"
                        data-target="#previewModal"
                        data-url="{{ asset('storage/'.$registration->signed_document) }}">
                        View Document
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


<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Document Preview</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="height:80vh">
                <iframe id="previewFrame"
                        src=""
                        style="width:100%;height:100%;border:none;"></iframe>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$('#previewModal').on('show.bs.modal', function (e) {
    $('#previewFrame').attr(
        'src',
        $(e.relatedTarget).data('url')
    );
});
</script>
@endpush

@endsection
