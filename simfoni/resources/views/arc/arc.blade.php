<div class="mb-3">
    <label for="periode" class="form-label">Periode:</label>
    <input type="date" name="periode" class="form-control" id="periode"
        value="{{ old('periode', $fenomena->periode ?? '') }}" required maxlength="10">
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
    </select>
</div>

<div class="mb-3">
    <label for="subjek" class="form-label">Subjek:</label>
    <select name="subjek" class="form-select" id="subjek" required>
        <option value="" disabled selected>Pilih Subjek</option>
        @foreach ($subjects as $subject)
            <option value="{{ $subject->subjek }}" {{ old('subjek', $fenomena->idsubjek ?? '') == $subject->id ? 'selected' : '' }}>
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

<div class="mb-3">
    <label for="resume" class="form-label">Resume:</label>
    <textarea name="resume" class="form-control" id="resume" rows="10"
        required>{{ old('resume', $fenomena->resume ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label for="linkbukti" class="form-label">Link Bukti:</label>
    <input type="url" name="linkbukti" class="form-control" id="linkbukti"
        value="{{ old('linkbukti', $fenomena->linkbukti ?? '') }}" required maxlength="200">
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sumberSelect = document.getElementById('sumber');
        const portalBeritaContainer = document.getElementById('portalBeritaContainer');
        const portalBeritaSelect = document.getElementById('sumberportal');

        // Ketika sumber berubah
        sumberSelect.addEventListener('change', function () {
            const selectedSumber = this.value;

            // Jika sumber adalah "Portal Berita Online", tampilkan dropdown portal berita
            if (selectedSumber === 'Portal Berita Online') {
                portalBeritaContainer.style.display = 'block';

                // Memuat data Portal Berita Online melalui AJAX
                fetch('/simfoni/get-portal')
                    .then(response => response.json())
                    .then(data => {
                        // Bersihkan opsi sebelumnya
                        portalBeritaSelect.innerHTML = '<option value="" disabled selected>Pilih Portal Berita</option>';

                        // Tambahkan opsi baru dari database
                        data.forEach(portal => {
                            const option = document.createElement('option');
                            option.value = portal.portalberita; // atau sesuai dengan kolom id di database
                            option.textContent = portal.portalberita; // atau sesuai dengan kolom nama di database
                            portalBeritaSelect.appendChild(option);
                        });
                    });
            } else {
                portalBeritaContainer.style.display = 'none';
            }
        });

        const cakupanSelect = document.getElementById('cakupan');
        const kabupatenContainer = document.getElementById('kabupatenContainer');
        const kabupatenSelect = document.getElementById('cakupankabkot');

        cakupanSelect.addEventListener('change', function () {
            const selectedCakupan = this.value;

            if (selectedCakupan === 'Kabupaten/Kota') {
                kabupatenContainer.style.display = 'block';

                // Memuat data Kabupaten/Kota melalui AJAX
                fetch('/simfoni/get-kabupaten')
                    .then(response => response.json())
                    .then(data => {
                        // Bersihkan opsi sebelumnya
                        kabupatenSelect.innerHTML = '<option value="" disabled selected>Pilih Kabupaten/Kota</option>';

                        // Tambahkan opsi baru dari database
                        data.forEach(kabupaten => {
                            const option = document.createElement('option');
                            option.value = kabupaten.wilayah; // atau sesuai dengan kolom id di database
                            option.textContent = kabupaten.wilayah; // atau sesuai dengan kolom nama di database
                            kabupatenSelect.appendChild(option);
                        });
                    });
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
