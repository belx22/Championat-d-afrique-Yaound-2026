@extends('adminTheme.default')

@section('content')

<div class="container-fluid">

<h1 class="h3 mb-4 text-gray-800">
    Provisional Registration – {{ $registration->delegation->country }}
</h1>

@include('registrations.partials.provisional_table_readonly', [
    'registration' => $registration
])

@if($registration->status === 'en_attente')

<div class="mt-4 d-flex">
    <form method="POST"
          action="{{ route('admin_local.provisional.validate', $registration) }}">
        @csrf
        <button class="btn btn-success mr-2">
            Valider
        </button>
    </form>

    <form method="POST"
          action="{{ route('admin_local.provisional.reject', $registration) }}">
        @csrf
        <button class="btn btn-danger">
            Rejeter
        </button>
    </form>
</div>

@endif

</div>
@endsection
