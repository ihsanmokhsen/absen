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
    <div class="col-md-6">
        <label class="form-label" for="position_after">Posisi Nomor Urut</label>
        <select id="position_after" class="form-select @error('position_after') is-invalid @enderror" name="position_after">
            @if ($employee->exists)
                <option value="__keep" @selected(old('position_after', '__keep') === '__keep') data-bidang="__all">Pertahankan posisi sekarang</option>
            @endif
            <option value="__last" @selected(old('position_after', $employee->exists ? '__keep' : '__last') === '__last') data-bidang="__all">Otomatis di akhir bidang</option>
            <option value="__first" @selected(old('position_after') === '__first') data-bidang="__all">Jadikan nomor 01</option>
            @foreach ($bidangOptions as $bidangOption)
                @foreach (($positionEmployees->get($bidangOption) ?? collect()) as $positionEmployee)
                    <option value="{{ $positionEmployee->id }}" @selected((string) old('position_after') === (string) $positionEmployee->id) data-bidang="{{ $bidangOption }}">
                        Setelah {{ $positionEmployee->attendanceLabel() }}
                    </option>
                @endforeach
            @endforeach
        </select>
        <div class="form-text">Nomor pegawai lain akan digeser otomatis sesuai posisi yang dipilih.</div>
        @error('position_after')
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

@push('scripts')
<script>
    (() => {
        const bidang = document.getElementById('bidang');
        const position = document.getElementById('position_after');

        if (!bidang || !position) {
            return;
        }

        const syncPositionOptions = () => {
            const selectedBidang = bidang.value;
            let selectedStillVisible = false;

            Array.from(position.options).forEach((option) => {
                const optionBidang = option.dataset.bidang || '__all';
                const isVisible = optionBidang === '__all' || optionBidang === selectedBidang;
                option.hidden = !isVisible;
                option.disabled = !isVisible;

                if (option.selected && isVisible) {
                    selectedStillVisible = true;
                }
            });

            if (!selectedStillVisible) {
                const fallback = Array.from(position.options).find((option) => !option.disabled && option.value === '__last')
                    || Array.from(position.options).find((option) => !option.disabled);

                if (fallback) {
                    position.value = fallback.value;
                }
            }
        };

        bidang.addEventListener('change', syncPositionOptions);
        syncPositionOptions();
    })();
</script>
@endpush
