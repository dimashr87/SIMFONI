@extends('layouts.otomatisasi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Ringkasan Fenomena Otomatis</h5>
                <button type="button" class="btn-pengaturan" data-bs-toggle="modal" data-bs-target="#modalPengaturan"
                        title="Pengaturan keyword scraping" aria-label="Pengaturan keyword scraping">
                    {{-- ikon pengaturan --}}
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Indikator</label>
                        <select id="indikator" class="form-select">
                            @foreach ($indikatorList as $kode => $nama)
                                <option value="{{ $kode }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Model Ringkasan</label>
                        <select id="model" class="form-select">
                            @foreach ($modelList as $kode => $nama)
                                <option value="{{ $kode }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Awal</label>
                        <input type="date" id="tanggal_awal" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" id="tanggal_akhir" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <button id="btnTampilkan" class="btn btn-primary w-100">Tampilkan</button>
                    </div>
                </div>

                <label class="form-label">Ringkasan Akhir</label>
                <textarea id="ringkasanAkhir" class="form-control mb-4" rows="6" readonly
                          placeholder="Pilih indikator, model ringkasan & rentang tanggal, lalu klik Tampilkan"></textarea>

                <div id="loadingIndicator" class="text-center mb-3" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-sm mt-2">Memproses ringkasan akhir, mohon tunggu...</p>
                </div>

                <a class="toggle-rincian d-inline-flex align-items-center text-decoration-none" data-bs-toggle="collapse"
                   href="#tabelBerita" role="button" aria-expanded="false" aria-controls="tabelBerita">
                    <svg class="chevron me-2" width="14" height="14" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M5.5 2.5 11 8l-5.5 5.5" fill="none" stroke="currentColor"
                              stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Lihat rincian berita (<span id="jumlahBerita">0</span>)</span>
                </a>

                <div class="collapse mt-3" id="tabelBerita">
                    <table class="table table-striped" id="tabelBeritaDatatable">
                        <thead>
                            <tr>
                                <th class="kolom-judul">Judul</th>
                                <th class="kolom-tanggal">Tanggal</th>
                                <th class="kolom-sumber">Sumber</th>
                                <th class="kolom-ringkasan">Ringkasan <span id="namaModelTabel"></span></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pop-up pengaturan keyword scraping --}}
<div class="modal fade" id="modalPengaturan" tabindex="-1" aria-labelledby="judulModalPengaturan" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="judulModalPengaturan">Pengaturan Keyword Scraping</h5>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Tutup">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-sm mb-2">
                    Keyword dasar <strong>selalu dipakai</strong>. Tambahkan keyword supaya scraping juga mengambil
                    berita yang menyebut keyword tersebut di DKI Jakarta. Perubahan berlaku untuk scraping berikutnya.
                </p>

                <label class="form-label mb-1">Keyword</label>
                <div id="daftarKeyword" class="daftar-keyword mb-3"></div>

                <form id="formKeyword" class="d-flex gap-2 mb-1" autocomplete="off">
                    <input type="text" id="inputKeyword" class="form-control" maxlength="100"
                           placeholder='Contoh: Inflasi atau Pertumbuhan Ekonomi'>
                    <button type="submit" id="btnTambahKeyword" class="btn btn-primary mb-0 text-nowrap">Tambah</button>
                </form>
                <p id="pesanKeyword" class="text-danger text-xs mb-3" style="min-height:1rem;"></p>

                <label class="form-label mb-1">Query Media Cloud yang dipakai</label>
                <pre id="previewQuery" class="preview-query mb-0">Memuat...</pre>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    let dataTable = null;

    // escape HTML
    function escapeHtml(teks) {
        return String(teks ?? '').replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // hanya izinkan link http/https
    function urlAman(url) {
        try {
            const u = new URL(url);
            return (u.protocol === 'http:' || u.protocol === 'https:') ? u.href : null;
        } catch (e) {
            return null;
        }
    }

    // hitung ulang lebar kolom tabel
    $('#tabelBerita').on('shown.bs.collapse', function () {
        if (dataTable) dataTable.columns.adjust();
    });

    // ringkasan lengkap
    $('#tabelBeritaDatatable').on('click', '.btn-selengkapnya', function () {
        const teks = $(this).siblings('.ringkasan-teks');
        teks.toggleClass('terbuka');
        $(this).text(teks.hasClass('terbuka') ? 'Tutup' : 'Selengkapnya');
    });

    // RINGKASAN AKHIR
    $('#btnTampilkan').on('click', function () {
        const indikator = $('#indikator').val();
        const model = $('#model').val();
        const tanggalAwal = $('#tanggal_awal').val();
        const tanggalAkhir = $('#tanggal_akhir').val();

        if (!tanggalAwal || !tanggalAkhir) {
            alert('Pilih tanggal awal dan akhir terlebih dahulu.');
            return;
        }

        if (tanggalAkhir < tanggalAwal) {
            alert('Tanggal akhir tidak boleh lebih awal dari tanggal awal.');
            return;
        }

        $('#loadingIndicator').show();
        $('#ringkasanAkhir').val('');
        $('#btnTampilkan').prop('disabled', true);

        $.ajax({ // ambil berita, proses ringkasan akhir, tampilkan di textarea & tabel
            url: "{{ route('otomatisasi.ringkasan') }}",
            method: 'GET',
            data: {
                indikator: indikator,
                model: model,
                tanggal_awal: tanggalAwal,
                tanggal_akhir: tanggalAkhir,
            },
            success: function (res) {
                // ringkasan selalu berupa teks
                $('#ringkasanAkhir').val(res.ringkasan_akhir ?? res.pesan ?? '');
                $('#jumlahBerita').text(res.berita.length);
                // judul kolom mengikuti model yang dipakai, misal
                $('#namaModelTabel').text('(' + $('#model option:selected').text() + ')');

                if (dataTable) {
                    dataTable.clear().rows.add(res.berita).draw();
                    return;
                }

                // tabel berita selengkapnya
                dataTable = $('#tabelBeritaDatatable').DataTable({
                    data: res.berita,
                    autoWidth: false,
                    pageLength: 10,
                    order: [[1, 'desc']],
                    language: { url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/id.json' },
                    columns: [
                        {
                            data: 'judul',
                            render: function (judul, type, row) {
                                if (type !== 'display') return judul;
                                const href = urlAman(row.url);
                                return href
                                    ? `<a class="judul-berita" href="${escapeHtml(href)}" target="_blank" rel="noopener noreferrer">${escapeHtml(judul)}</a>`
                                    : escapeHtml(judul);
                            },
                        },
                        {
                            data: 'tanggal',
                            // show d-m-Y, sort Y-m-d
                            render: function (tanggal, type, row) {
                                return type === 'sort' ? row.tanggal_iso : escapeHtml(tanggal);
                            },
                        },
                        {
                            data: 'sumber',
                            render: function (sumber, type) {
                                return type === 'display' ? escapeHtml(sumber ?? '-') : (sumber ?? '');
                            },
                        },
                        {
                            data: 'ringkasan',
                            orderable: false,
                            render: function (ringkasan, type) {
                                if (type !== 'display') return ringkasan;
                                return `<p class="ringkasan-teks">${escapeHtml(ringkasan)}</p>`
                                     + `<button type="button" class="btn-selengkapnya">Selengkapnya</button>`;
                            },
                        },
                    ],
                });
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan saat mengambil data.';
                $('#ringkasanAkhir').val('Gagal memuat: ' + msg);
            },
            complete: function () {
                $('#loadingIndicator').hide();
                $('#btnTampilkan').prop('disabled', false);
            },
        });
    });

    // ---------------- pengaturan keyword scraping ----------------
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const urlKeyword = "{{ route('otomatisasi.keyword.index') }}";

    // tampilkan keyword
    function tampilkanKeyword(data) {
        const wadah = $('#daftarKeyword').empty();

        data.dasar.forEach(function (k) {
            wadah.append($('<span class="chip-keyword chip-dasar" title="Keyword dasar, selalu dipakai">').text(k));
        });
        data.keywords.forEach(function (k) {
            const chip = $('<span class="chip-keyword">').text(k.keyword);
            chip.append($('<button type="button" class="btn-hapus-keyword" aria-label="Hapus keyword">&times;</button>')
                .attr('data-id', k.id).attr('title', 'Hapus "' + k.keyword + '"'));
            wadah.append(chip);
        });
        wadah.append($('<span class="text-xs text-secondary ms-1">').text('AND "' + data.wilayah + '"'));

        $('#previewQuery').text(data.query);
    }

    // error add keyword
    function tampilkanError(xhr) {
        const errors = xhr.responseJSON?.errors;
        $('#pesanKeyword').text(errors?.keyword?.[0] ?? xhr.responseJSON?.message ?? 'Terjadi kesalahan, coba lagi.');
    }

    // modal pengaturan keyword scraping
    $('#modalPengaturan').on('show.bs.modal', function () {
        $('#pesanKeyword').text('');
        $.get(urlKeyword).done(tampilkanKeyword).fail(tampilkanError);
    }).on('shown.bs.modal', function () {
        $('#inputKeyword').trigger('focus');
    });

    // tambah keyword
    $('#formKeyword').on('submit', function (e) {
        e.preventDefault();
        const keyword = $('#inputKeyword').val().trim();
        if (!keyword) return;

        $('#btnTambahKeyword').prop('disabled', true);
        $('#pesanKeyword').text('');

        $.post(urlKeyword, { keyword: keyword })
            .done(function (data) {
                tampilkanKeyword(data);
                $('#inputKeyword').val('').trigger('focus');
            })
            .fail(tampilkanError)
            .always(function () { $('#btnTambahKeyword').prop('disabled', false); });
    });

    // hapus keyword
    $('#daftarKeyword').on('click', '.btn-hapus-keyword', function () {
        $('#pesanKeyword').text('');
        $.ajax({ url: urlKeyword + '/' + $(this).data('id'), method: 'DELETE' })
            .done(tampilkanKeyword)
            .fail(tampilkanError);
    });
});
</script>
@endsection
