<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label" for="name">Nama Pegawai</label>
        <input id="name" class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name', $employee->name) }}" maxlength="150" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label" for="bidang">Bidang</label>
        <select id="bidang" class="form-select @error('bidang') is-invalid @enderror" name="bidang" required>
            <option value="">Pilih Bidang</option>
            @foreach ($bidangOptions as $option)
                <option value="{{ $option }}" @selected(old('bidang', $employee->bidang) === $option)>{{ $option }}</option>
            @endforeach
        </select>
        @error('bidang')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="d-flex flex-column gap-2 mb-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked((bool) old('is_active', $employee->is_active))>
                <label class="form-check-label" for="is_active">Pegawai aktif</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_pppk" value="1" id="is_pppk" @checked((bool) old('is_pppk', $employee->is_pppk))>
                <label class="form-check-label" for="is_pppk">PPPK</label>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a class="btn btn-outline-secondary" href="{{ route('employees.index') }}">Batal</a>
    <button class="btn btn-primary" type="submit">Simpan</button>
</div>
