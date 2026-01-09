<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .badge {
            border: 2px solid #000;
            padding: 10px;
            width: 100%;
            height: 100%;
        }
        .photo { width: 80px; height: 100px; object-fit: cover; }
    </style>
</head>
<body>

<div class="badge">
    <h4></h4>

    <img src="{{ storage_path('app/public/'.$member->photo_4x4) }}"
         class="photo">

    <p><strong>{{ $member->family_name }} {{ $member->given_name }}</strong></p>
    <p>{{ strtoupper($member->function) }}</p>

</div>

</body>
</html>
