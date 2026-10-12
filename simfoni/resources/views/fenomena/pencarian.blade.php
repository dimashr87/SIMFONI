@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Fenomena'])

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success mt-3">
                            <p class="mb-0">{{ $message }}</p>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="fenomenaTable" class="table table-striped table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Periode</th>
                                    <th>Cakupan</th>
                                    <th>Subjek</th>
                                    <th>Resume</th>
                                    <th>Aksi</th>
                                    <th>Link Bukti</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fenomena as $f)
                                    <tr>
                                        <td>
                                            @if ($f->tanggal_akhir)
                                                {{$f->tanggal_awal}} - {{$f->tanggal_akhir}}
                                            @else
                                                {{$f->tanggal_awal}}
                                            @endif
                                        </td>
                                        <td>{{ $f->cakupan }}</td>
                                        <td>{{ $f->subjek }}</td>
                                        <td class="deskripsi-col">{{ $f->resume }}</td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#resumeModal{{ $f->id }}">
                                                Show Resume
                                            </button>
                                        </td>
                                        <td><a href="{{ $f->linkbukti }}" target="_blank">Link</a></td>
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
