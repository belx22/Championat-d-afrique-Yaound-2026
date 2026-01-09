@php
    $isReadOnly = $disabled ?? ($registration->status === 'valide');
@endphp

<div class="card shadow mb-4">
    <div class="card-body">

        <!-- TABLE GYMNASTS -->
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
                               {{ $isReadOnly ? 'readonly' : '' }}>
                    </td>

                    <td>
                        <input type="number" min="0"
                               name="mag_senior"
                               class="form-control text-center"
                               value="{{ old('mag_senior', $registration->mag_senior) }}"
                               {{ $isReadOnly ? 'readonly' : '' }}>
                    </td>

                    <td>
                        <input type="number" min="0"
                               name="wag_junior"
                               class="form-control text-center"
                               value="{{ old('wag_junior', $registration->wag_junior) }}"
                               {{ $isReadOnly ? 'readonly' : '' }}>
                    </td>

                    <td>
                        <input type="number" min="0"
                               name="wag_senior"
                               class="form-control text-center"
                               value="{{ old('wag_senior', $registration->wag_senior) }}"
                               {{ $isReadOnly ? 'readonly' : '' }}>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- TABLE DELEGATION MEMBERS -->
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

    </div>
</div>
