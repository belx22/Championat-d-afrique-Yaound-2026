<div class="form-group">
    <label>Pays</label>
    <input type="text"
           name="country"
           class="form-control"
           value="{{ old('country', $delegation->country ?? '') }}"
           required>
</div>

<div class="form-group">
    <label>Nom de la fédération</label>
    <input type="text"
           name="federation_name"
           class="form-control"
           value="{{ old('federation_name', $delegation->federation_name ?? '') }}"
           required>
</div>

<div class="form-group">
    <label>Personne de contact</label>
    <input type="text"
           name="contact_person"
           class="form-control"
           value="{{ old('contact_person', $delegation->contact_person ?? '') }}"
           required>
</div>

<div class="form-group">
    <label>Email</label>
    <input type="email"
           name="email"
           class="form-control"
           value="{{ old('email', $delegation->email ?? '') }}"
           required>
</div>

<div class="form-group">
    <label>Téléphone</label>
    <input type="text"
           name="phone"
           class="form-control"
           value="{{ old('phone', $delegation->phone ?? '') }}"
           required>
</div>

<div class="form-group">
    <label>Administrateur de la fédération</label>

    <select name="user_id"
            class="form-control @error('user_id') is-invalid @enderror"
            required>

        <option value="">-- Sélectionner un administrateur --</option>

        @foreach($federationAdmins as $admin)
            <option value="{{ $admin->id }}"
                {{ old('user_id', $delegation->user_id ?? '') == $admin->id ? 'selected' : '' }}>
                {{ $admin->email }}
            </option>
        @endforeach
    </select>

    @error('user_id')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>

