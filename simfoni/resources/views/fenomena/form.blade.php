<div class="mb-3">
    <label class="form-label">Tanggal:</label>

    <!-- Checkbox untuk memilih mode periode -->
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" id="usePeriode" name="usePeriode"
            {{ old('usePeriode') ? 'checked' : '' }}>
        <label class="form-check-label" for="usePeriode">
            Gunakan Periode (Tanggal Awal - Tanggal Akhir)
        </label>
    </div>

    <!-- Input tanggal awal (single date) -->
    <input type="date" name="tanggal_awal" class="form-control" id="tanggal_awal_single"
        value="{{ old('tanggal_awal', $fenomena->tanggal_awal ?? '') }}"
        {{ old('usePeriode') ? 'disabled style=display:none;' : '' }}>

    <!-- Input periode (awal & akhir) -->
    <div id="periodeContainer" style="display: {{ old('usePeriode') ? 'block' : 'none' }};">
        <div class="row">
            <div class="col">
                <label for="tanggal_awal" class="form-label">Tanggal Awal:</label>
                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control"
                    value="{{ old('tanggal_awal', $fenomena->tanggal_awal ?? '') }}"
                    {{ old('usePeriode') ? '' : 'disabled' }}>
            </div>
            <div class="col">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir:</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control"
                    value="{{ old('tanggal_akhir', $fenomena->tanggal_akhir ?? '') }}"
                    {{ old('usePeriode') ? '' : 'disabled' }}>
            </div>
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="sumber" class="form-label">Sumber:</label>
    <select name="sumber" class="form-select" id="sumber" required>
        <option value="" disabled selected>Pilih Sumber</option>
        @foreach ($sumberFenomena as $sumber)
            <option value="{{ $sumber->sumber }}" {{ old('sumber', $fenomena->sumber ?? '') == $sumber->sumber ? 'selected' : '' }}>
                {{ $sumber->sumber }}
            </option>
        @endforeach
    </select>
</div>

<!-- Container untuk Link Portal Berita Online, disembunyikan jika sumber bukan "Portal Berita Online" -->
<div id="portalBeritaContainer" class="mb-3" style="display: none;">
    <label for="sumberportal" class="form-label">Pilih Portal Berita Online:</label>
    <select name="sumberportal" class="form-select" id="sumberportal">
        <option value="" disabled selected>Pilih Portal Berita</option>
        <!-- Opsi Portal Berita Online akan dimuat di sini melalui AJAX -->
        @foreach ($portalBeritas as $portal)
            <option value="{{ $portal->portalberita }}" {{ old('sumberportal', $fenomena->sumberPortal ?? '') == $portal->portalberita ? 'selected' : '' }}>
                {{ $portal->portalberita }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="cakupan" class="form-label">Cakupan:</label>
    <select name="cakupan" class="form-select" id="cakupan" required>
        <option value="" disabled selected>Pilih Cakupan</option>
        @foreach ($cakupan as $ckp)
            <option value="{{ $ckp->cakupan }}" {{ old('cakupan', $fenomena->cakupan ?? '') == $ckp->cakupan ? 'selected' : '' }}>
                {{ $ckp->cakupan }}
            </option>
        @endforeach
    </select>
</div>

<div id="kabupatenContainer" class="mb-3" style="display:none;">
    <label for="kabupaten" class="form-label">Kabupaten/Kota:</label>
    <select name="cakupankabkot" class="form-select" id="cakupankabkot">
        <option value="" disabled selected>Pilih Kabupaten/Kota</option>
        <!-- Kabupaten/Kota options akan dimuat di sini melalui AJAX -->
        @foreach ($kabkots as $kabkot)
            <option value="{{ $kabkot->wilayah }}" {{ old('cakupankabkot', $fenomena->cakupankabkot ?? '') == $kabkot->wilayah ? 'selected' : '' }}>
                {{ $kabkot->wilayah }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="subjek" class="form-label">Subjek:</label>
    <select name="subjek" class="form-select" id="subjek" required>
        <option value="" disabled selected>Pilih Subjek</option>
        @foreach ($subjects as $subject)
            <option value="{{ $subject->subjek }}" {{ old('subjek', $fenomena->subjek ?? '') == $subject->subjek ? 'selected' : '' }}>
                {{ $subject->subjek }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="tema" class="form-label">Tema:</label>
    <select name="tema" class="form-select" id="tema" required>
        <option value="" disabled selected>Pilih Tema</option>
        <!-- Opsi Tema akan dimuat di sini melalui AJAX -->
    </select>
</div>

<div id="periodicity" class="mb-3">
    <label class="form-label d-block">Jenis Periode:</label>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="periodicity[]" id="qtq" value="qtq"
            {{ in_array('qtq', old('periodicity', isset($fenomena->periodicity) ? (array)$fenomena->periodicity : [])) ? 'checked' : '' }}>
        <label class="form-check-label" for="qtq">
            Quarter-to-Quarter (Q-to-Q)
        </label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="periodicity[]" id="mtm" value="mtm"
            {{ in_array('mtm', old('periodicity', isset($fenomena->periodicity) ? (array)$fenomena->periodicity : [])) ? 'checked' : '' }}>
        <label class="form-check-label" for="mtm">
            Month-to-Month (M-to-M)
        </label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="periodicity[]" id="yoy" value="yoy"
            {{ in_array('yoy', old('periodicity', isset($fenomena->periodicity) ? (array)$fenomena->periodicity : [])) ? 'checked' : '' }}>
        <label class="form-check-label" for="yoy">
            Year-on-Year (Y-on-Y)
        </label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="periodicity[]" id="ctc" value="ctc"
            {{ in_array('ctc', old('periodicity', isset($fenomena->periodicity) ? (array)$fenomena->periodicity : [])) ? 'checked' : '' }}>
        <label class="form-check-label" for="ctc">
            Cumulative-to-Cumulative (C-to-C)
        </label>
    </div>
</div>


<div class="mb-3">
    <label for="resume" class="form-label">Resume:</label>
    <textarea name="resume" class="form-control" id="resume" rows="10"
        required>{{ old('resume', $fenomena->resume ?? '') }}</textarea>
</div>

<!-- Arah Radio Button -->
<div id="arah" class="mb-3">
    <label class="form-label d-block">Arah:</label>

    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="arah" id="arahNetral" value="Netral"
            {{ old('arah', $fenomena->arah ?? '') == 'Netral' ? 'checked' : '' }}>
        <label class="form-check-label" for="arahNetral">Netral</label>
    </div>

    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="arah" id="arahPositif" value="Positif"
            {{ old('arah', $fenomena->arah ?? '') == 'Positif' ? 'checked' : '' }}>
        <label class="form-check-label" for="arahPositif">Positif</label>
    </div>

    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="arah" id="arahNegatif" value="Negatif"
            {{ old('arah', $fenomena->arah ?? '') == 'Negatif' ? 'checked' : '' }}>
        <label class="form-check-label" for="arahNegatif">Negatif</label>
    </div>
</div>

<div class="mb-3">
    <label for="linkbukti" class="form-label">Link Bukti:</label>
    <input type="url" name="linkbukti" class="form-control" id="linkbukti"
        value="{{ old('linkbukti', $fenomena->linkbukti ?? '') }}" required maxlength="200">
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.getElementById('usePeriode');
        const singleTanggalAwal = document.getElementById('tanggal_awal_single');
        const periodeContainer = document.getElementById('periodeContainer');
        const periodeAwal = document.getElementById('tanggal_awal');
        const periodeAkhir = document.getElementById('tanggal_akhir');

        checkbox.addEventListener('change', function () {
            if (this.checked) {
                singleTanggalAwal.style.display = 'none';
                singleTanggalAwal.disabled = true;

                periodeContainer.style.display = 'block';
                periodeAwal.disabled = false;
                periodeAkhir.disabled = false;
            } else {
                singleTanggalAwal.style.display = 'block';
                singleTanggalAwal.disabled = false;

                periodeContainer.style.display = 'none';
                periodeAwal.disabled = true;
                periodeAkhir.disabled = true;
            }
        });

        const sumberSelect = document.getElementById('sumber');
        const portalBeritaContainer = document.getElementById('portalBeritaContainer');

        // Ketika sumber berubah
        sumberSelect.addEventListener('change', function () {
            const selectedSumber = this.value;

            // Jika sumber adalah "Portal Berita Online", tampilkan dropdown portal berita
            if (selectedSumber === 'Portal Berita Online') {
                portalBeritaContainer.style.display = 'block';
            } else {
                portalBeritaContainer.style.display = 'none';
            }
        });

        const cakupanSelect = document.getElementById('cakupan');
        const kabupatenContainer = document.getElementById('kabupatenContainer');

        cakupanSelect.addEventListener('change', function () {
            const selectedCakupan = this.value;

            if (selectedCakupan === 'Kabupaten/Kota') {
                kabupatenContainer.style.display = 'block';
            } else {
                kabupatenContainer.style.display = 'none';
            }
        });
        
        const subjectSelect = document.getElementById('subjek');
        const themeSelect = document.getElementById('tema');
        
        subjectSelect.addEventListener('change', function () {
            const selectedSubjectId = this.value;

            fetch(`/simfoni/get-tema/${encodeURIComponent(selectedSubjectId)}`)
                .then(response => response.json())
                .then(data => {
                    themeSelect.innerHTML = '<option value="" disabled selected>Pilih Tema</option>';

                    data.forEach(theme => {
                        const option = document.createElement('option');
                        option.value = theme.tema;
                        option.textContent = theme.tema;
                        themeSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching themes:', error);
                });
        });
    });
</script>
