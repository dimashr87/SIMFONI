@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])
@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Rangkum Fenomena'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Rangkum Fenomena Otomatis Menggunakan AI</h6>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda.<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Form pilih tema -->
                        <form id="summarizeForm">
                            <div class="form-group mb-3">
                                <label for="tema">Pilih Tema:</label>
                                <select name="tema" id="tema" class="form-select" required>
                                    <option value="" disabled selected>Pilih Tema</option>
                                    @foreach ($temas as $tema)
                                        <option value="{{ $tema }}">{{ $tema }}</option>
                                    @endforeach
                                </select>
                            </div>

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

                            <!-- Tombol rangkum -->
                            <button type="button" id="btnSummarize" class="btn btn-primary">
                                <span id="btnText">🔍 Rangkum</span>
                                <span id="btnLoading" class="d-none">
                                    <span class="spinner-border spinner-border-sm"></span> Merangkum...
                                </span>
                            </button>
                        </form>

                        <!-- Textarea hasil -->
                        <div class="form-group mt-3">
                            <label for="summary">Ringkasan:</label>
                            <textarea name="summary" id="summary" rows="10" class="form-control" readonly></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const temaSelect = document.getElementById('tema');
        const summaryTextarea = document.getElementById('summary');
        const btnSummarize = document.getElementById('btnSummarize');
        const btnText = document.getElementById('btnText');
        const btnLoading = document.getElementById('btnLoading');

        btnSummarize.addEventListener('click', function () {
            const selectedTemaId = temaSelect.value;
            if (!selectedTemaId) {
                alert('Silakan pilih tema terlebih dahulu!');
                return;
            }

            // Ambil input tanggal
            const checkbox = document.getElementById('usePeriode');
            let tanggal_awal = "";
            let tanggal_akhir = "";

            if (checkbox.checked) {
                tanggal_awal = document.getElementById('tanggal_awal').value;
                tanggal_akhir = document.getElementById('tanggal_akhir').value;
            } else {
                tanggal_awal = document.getElementById('tanggal_awal_single').value;
            }

            // Bangun URL dengan query params
            let url = `/simfoni/summarize-tema/${selectedTemaId}?tanggal_awal=${encodeURIComponent(tanggal_awal)}`;
            if (tanggal_akhir) {
                url += `&tanggal_akhir=${encodeURIComponent(tanggal_akhir)}`;
            }

            // Tampilkan loading
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            btnSummarize.disabled = true;
            summaryTextarea.value = 'Sedang merangkum...';

            // Fetch GET ke server
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    summaryTextarea.value = data.summary;
                })
                .catch(error => {
                    console.error('Error fetching summary:', error);
                    summaryTextarea.value = 'Terjadi kesalahan saat mengambil ringkasan.';
                })
                .finally(() => {
                    btnText.classList.remove('d-none');
                    btnLoading.classList.add('d-none');
                    btnSummarize.disabled = false;
                });
        });

        // Toggle tanggal
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
    });
</script>
@endsection
