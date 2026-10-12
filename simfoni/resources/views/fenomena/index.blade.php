@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Fenomena'])

<style>
    /* Style tombol Choose File */
    input[type="file"]::-webkit-file-upload-button {
        background-color: #fa6340;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 5px;
        cursor: pointer;
    }

    /* Untuk Firefox */
    input[type="file"]::file-selector-button {
        background-color: #fa6340;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 5px;
        cursor: pointer;
    }

    /* Hilangkan efek hover */
    input[type="file"]::-webkit-file-upload-button:hover,
    input[type="file"]::file-selector-button:hover {
        background-color: #fa6340; /* tetap oranye */
        color: white; /* tetap putih */
    }
</style>


<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <!-- Import Excel Form, Tambah Fenomena Button, dan Download Template -->
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0">Daftar Fenomena</h6>
                    </div>
                    
                </div>

                <!-- Success Message -->
                <div class="card-body">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success mt-3">
                            <p class="mb-0">{{ $message }}</p>
                        </div>
                    @endif
                    <div class="d-flex">
                        <!-- Form Import Excel -->
                        <form action="{{ route('import.fenomena') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center w-100">
                            @csrf
                            <input type="file" class="form-control me-3" name="file" required>
                            <button class="btn btn-success btn-sm w-30" type="submit">
                                <i class="ni ni-cloud-upload-96"></i> Import Excel
                            </button>
                        </form>

                        <!-- Download Template Button -->
                        <a href="https://jkt.web.bps.go.id/simfoni/templateimport.xlsx" class="btn btn-info btn-sm ms-3 w-25">
                            <i class="ni ni-cloud-download-95"></i> Download Template
                        </a>
                        
                        <!-- Tambah Fenomena Button -->
                        <a href="{{ route('fenomena.create') }}" class="btn btn-primary btn-sm ms-3 w-25">Tambah Fenomena</a>
                    </div>
                </div>

                <!-- Table Data -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="fenomenaTable" class="table table-striped table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Periode</th>
                                    <th>Sumber</th>
                                    <th>Cakupan</th>
                                    <th>Subjek</th>
                                    <th>Arah</th>
                                    <th>Jenis Periode</th>
                                    <th>Link Bukti</th>
                                    <th>Tanggal Created</th>
                                    <th width="180px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($fenomena as $f)
                                <tr>
                                    <!-- Periode (awal - akhir atau hanya awal) -->
                                    <td>
                                        @if ($f->tanggal_akhir)
                                            {{$f->tanggal_awal}} - {{$f->tanggal_akhir}}
                                        @else
                                            {{$f->tanggal_awal}}
                                        @endif
                                    </td>

                                    <td>{{ $f->sumber }}</td>
                                    <td>{{ $f->cakupan }}</td>
                                    <td>{{ $f->subjek }}</td>
                                    <td>{{ $f->arah }}</td>

                                    <!-- Jenis Periode (checkbox multiple) -->
                                    <td>
                                    @php
                                        $labels = [
                                            'qtq' => 'Quarter-to-Quarter (Q-to-Q)',
                                            'mtm' => 'Month-to-Month (M-to-M)',
                                            'yoy' => 'Year-on-Year (Y-on-Y)',
                                            'ctc' => 'Cumulative-to-Cumulative (C-to-C)',
                                        ];

                                        $periods = explode(',', $f->periodicity);
                                    @endphp

                                    @if(!empty($periods))
                                        <ul class="mb-0">
                                            @foreach ($periods as $p)
                                                <li>{{ $labels[trim($p)] ?? $p }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <em>-</em>
                                    @endif
                                </td>

                                    <td><a href="{{ $f->linkbukti }}" target="_blank">Link</a></td>
                                    <td>{{ \Carbon\Carbon::parse($f->tanggalcreated)->format('d-m-Y') }}</td>
                                    <td>
                                        <form action="{{ route('fenomena.destroy', $f->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#resumeModal{{ $f->id }}">
                                                Resume
                                            </button>
                                            <a href="{{ route('fenomena.edit', $f->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @include('layouts.footers.auth.footer')
        </div>
    </div>
</div>

<!-- Modals -->
@foreach ($fenomena as $f)
    <div class="modal fade" id="resumeModal{{ $f->id }}" tabindex="-1" aria-labelledby="resumeModalLabel{{ $f->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resumeModalLabel{{ $f->id }}">Resume</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{ $f->resume }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Tambahkan DataTables Script -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>

<script>
    $(document).ready(function () {
        $('#fenomenaTable').DataTable({
            "pagingType": "simple_numbers",
            "lengthMenu": [5, 10, 25, 50],
            "pageLength": 10,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "paginate": {
                    "previous": "Sebelumnya",
                    "next": "Berikutnya"
                }
            }
        });
    });
</script>

@endsection
