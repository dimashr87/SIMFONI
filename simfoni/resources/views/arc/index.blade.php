@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Input ARC'])
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
                    <div class="card-header">
                        <h6 class="mb-0">Daftar Advance Release Calendar (ARC)</h6>
                        @if ($message = Session::get('success'))
                            <div class="alert alert-success mt-3">
                                <p class="mb-0">{{ $message }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="d-flex">
                            <form action="{{ route('arc.import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center me-2 w-100">
                                @csrf
                                <input type="file" name="file" class="form-control me-2" required>
                                <button type="submit" class="btn btn-primary btn-sm me-2">Import</button>
                                <a href="https://jkt.web.bps.go.id/simfoni/template_arc.xlsx" class="btn btn-success btn-sm w-20">Download Template</a>
                            </form>
                        </div>
                        <form action="{{ route('arc.store') }}" method="POST" class="mb-3">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="nama_kegiatan" class="form-control" placeholder="Nama Kegiatan" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="tanggal_kegiatan" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success w-100">Tambah</button>
                                </div>
                            </div>
                        </form>

                        <form id="bulkDeleteForm" action="{{ route('arc.bulkDelete') }}" method="POST">
                        @csrf
                        @method('DELETE')

                        <div class="mb-2">
                            <button type="submit" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
                                Hapus Terpilih
                            </button>
                        </div>
                        </form>

                        <div class="table-responsive">
                            <table id="arcTable" class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>No</th>
                                        <th>Nama Kegiatan</th>
                                        <th>Tanggal Kegiatan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($arcs as $index => $arc)
                                        <tr>
                                            <td>
                                                <input type="checkbox" value="{{ $arc->id }}" class="selectItem">
                                            </td>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="deskripsi-col">{{ $arc->nama_kegiatan }}</td>
                                            <td>{{ $arc->tanggal_format }}</td>
                                            <td>
                                                @if($arc->status == 'akan datang')
                                                    <span class="badge bg-info">AKAN DATANG</span>
                                                @elseif($arc->status == 'berjalan')
                                                    <span class="badge bg-warning">BERJALAN</span>
                                                @else
                                                    <span class="badge bg-success">SELESAI</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('arc.edit', $arc->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                                <form action="{{ route('arc.destroy', $arc->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>

<script>
    $(document).ready(function () {
        $('#arcTable').DataTable({
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

        // Checkbox select all
        $("#selectAll").click(function(){
            $(".selectItem").prop('checked', this.checked);
            toggleDeleteButton();
        });

        // Enable/disable bulk delete button
        $(".selectItem").change(function(){
            toggleDeleteButton();
        });

        function toggleDeleteButton() {
            if ($(".selectItem:checked").length > 0) {
                $("#bulkDeleteBtn").prop("disabled", false);
            } else {
                $("#bulkDeleteBtn").prop("disabled", true);
            }
        }

        $("#bulkDeleteForm").submit(function(e) {
            e.preventDefault(); // cegah submit dulu

            // hapus input hidden lama
            $("#bulkDeleteForm input[name='ids[]']").remove();

            // ambil semua checkbox yang dipilih
            $(".selectItem:checked").each(function() {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'ids[]',
                    value: $(this).val()
                }).appendTo('#bulkDeleteForm');
            });

            this.submit(); // submit ulang dengan data hidden
        });
    });
</script>
