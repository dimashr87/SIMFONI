@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Web Scrapping'])

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Daftar File Web Scrapping</h6>
                    <form action="{{ route('webscrapping.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="input-group">
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                            <button type="submit" class="btn btn-primary">Tambah File</button>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success mt-3">
                            <p class="mb-0">{{ $message }}</p>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="webscrappingTable" class="table table-striped table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama File</th>
                                    <th>Tanggal Upload</th>
                                    <th>Status</th>
                                    <th width="180px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($files as $index => $file)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $file->nama_file }}</td>
                                        <td>{{ $file->created_at->format('d-m-Y H:i') }}</td>
                                        <td>
                                            {{ $file->status == 1 ? 'Sudah Didownload' : 'Belum Didownload' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('webscrapping.download', $file->id) }}" class="btn btn-sm btn-success">Download</a>
                                            <form action="{{ route('webscrapping.destroy', $file->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus file ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
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

<!-- Tambahkan DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
<script>
    $(document).ready(function () {
        $('#webscrappingTable').DataTable({
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
