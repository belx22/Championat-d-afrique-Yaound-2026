@php
$color = match($status) {
    'valide'  => 'success',
    'rejete'  => 'danger',
    'en_attente' => 'warning',
    default   => 'secondary'
};
@endphp

<span class="badge badge-{{ $color }}">
    {{ strtoupper($status ?? 'N/A') }}
</span>
