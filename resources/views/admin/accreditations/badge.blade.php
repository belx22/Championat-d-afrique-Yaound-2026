<div style="width:350px;height:550px;border:2px solid #000;padding:15px;font-family:sans-serif;">
    <h4 style="text-align:center">AFRICAN GYMNASTICS 2026</h4>

    <img src="{{ asset('storage/'.$member->photo_4x4) }}"
         style="width:120px;height:160px;display:block;margin:auto;">

    <h5 style="text-align:center">
        {{ $member->family_name }} {{ $member->given_name }}
    </h5>

    <p style="text-align:center">
        {{ strtoupper($member->function) }}
    </p>

    <img src="{{ asset('storage/'.$accreditation->qr_code_path) }}"
         style="width:150px;display:block;margin:auto;">

    <p style="text-align:center">
        {{ $accreditation->badge_number }}
    </p>
</div>



<a href="{{ route('admin.accreditations.badges.pdf',$delegation) }}"
   class="btn btn-danger mb-3"
   target="_blank">
   <i class="fas fa-file-pdf"></i> Export PDF (A4)
</a>

