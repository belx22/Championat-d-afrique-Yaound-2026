<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
@page { margin: 10mm; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
}

.grid {
    display: table;
    width: 100%;
}

.row {
    display: table-row;
}

.cell {
    display: table-cell;
    width: 50%;
    padding: 5mm;
}

.badge {
    border: 2px solid #1f3c88;
    height: 85mm;
    padding: 5mm;
}

.header {
    background: #1f3c88;
    color: #fff;
    text-align: center;
    font-size: 9px;
    padding: 3mm;
}

.photo {
    text-align: center;
    margin: 5mm 0;
}

.photo img {
    width: 25mm;
    height: 32mm;
    object-fit: cover;
    border: 1px solid #000;
}

.role {
    background: #1f3c88;
    color: #fff;
    text-align: center;
    padding: 1mm;
    font-size: 9px;
}

.qr {
    text-align: center;
    margin-top: 3mm;
}
</style>
</head>

<body>

<div class="grid">
@foreach($members->chunk(2) as $row)
    <div class="row">
        @foreach($row as $m)
        <div class="cell">
            <div class="badge">

                <div class="header">
                    AFRICAN ARTISTIC GYMNASTICS CHAMPIONSHIP 2026<br>
                    YAOUNDÉ – CAMEROON
                </div>

                <div class="photo">
                    <img src="{{ public_path('storage/'.$m->photo_4x4) }}">
                </div>

                <strong>{{ strtoupper($m->family_name) }}</strong><br>
                {{ ucfirst($m->given_name) }}<br>

                <div class="role">
                    {{ strtoupper($m->function) }}
                </div>

                @if($m->discipline)
                    {{ $m->discipline }} – {{ strtoupper($m->category) }}<br>
                @endif

                @if($m->fig_id)
                    FIG ID: {{ $m->fig_id }}<br>
                @endif

                {{ $delegation->country }}

                <div class="qr">
                    {!! QrCode::size(70)->generate('ACCREDITATION-'.$m->id) !!}
                </div>

            </div>
        </div>
        @endforeach
    </div>
@endforeach
</div>

</body>
</html>
