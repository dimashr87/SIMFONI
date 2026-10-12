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
                                    <th>Sumber</th>
                                    <th>Cakupan</th>
                                    <th>Subjek</th>
                                    <th>Resume</th>
                                    <th>Link Bukti</th>
                                    <th>Tanggal Created</th>
                                    <th>Status</th>
                                    <th width="180px">Action</th>
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
                                        <td>{{ $f->sumber }}</td>
                                        <td>{{ $f->cakupan }}</td>
                                        <td>{{ $f->subjek }}</td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#resumeModal{{ $f->id }}">
                                                Show Resume
                                            </button>
                                        </td>
                                        <td><a href="{{ $f->linkbukti }}" target="_blank">Link</a></td>
                                        <td>{{ $f->tanggalcreated }}</td>
                                        <td>
                                            @if ($f->approval == 1)
                                                Approved
                                            @else
                                                Pending
                                            @endif
                                        </td>
                                        <td>
                                            <!-- Approve Form -->
                                            <form action="{{ route('fenomena.approve', ['id' => $f->id, 'action' => 'approve']) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui data ini?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" {{ $f->approval == 1 ? 'disabled' : '' }}>Approve</button>
                                            </form>

                                            <!-- Reject Button (opens modal) -->
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $f->id }}" {{ $f->approval == 2 ? 'disabled' : '' }}>
                                                Reject
                                            </button>

                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal{{ $f->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $f->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('fenomena.approve', ['id' => $f->id, 'action' => 'reject']) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="rejectModalLabel{{ $f->id }}">Alasan Penolakan</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label for="reason">Pilih Alasan Penolakan</label>
                                                                    <select class="form-select" name="reason" required>
                                                                        <option value="" disabled selected>Pilih Alasan</option>
                                                                        <option value="Periode fenomena kurang tepat">Periode fenomena kurang tepat</option>
                                                                        <option value="Cakupan fenomena kurang relevan">Cakupan fenomena kurang relevan</option>
                                                                        <option value="Klasifikasi tema fenomena belum tepat">Klasifikasi tema fenomena belum tepat</option>
                                                                        <option value="Resume fenomena kurang lengkap">Resume fenomena kurang lengkap</option>
                                                                        <option value="Sumber fenomena tidak terpercaya">Sumber fenomena tidak terpercaya</option>
                                                                        <option value="Lainnya">Alasan Lainnya</option>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group mt-3" id="otherReasonContainer" style="display:none;">
                                                                    <label for="other_reason">Alasan Lainnya</label>
                                                                    <textarea name="other_reason" class="form-control" placeholder="Jelaskan alasan penolakan..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger">Tolak</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
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
        var table = $('#fenomenaTable').DataTable({
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

        // Custom search for modals
        $('#customSearch').on('keyup', function () {
            table.search(this.value).draw();
        });

        $('select[name="reason"]').change(function () {
            var selectedReason = $(this).val();
            if (selectedReason === 'Lainnya') {
                $('#otherReasonContainer').show();
            } else {
                $('#otherReasonContainer').hide();
            }
        });

        // Event listener for DataTables search
        table.on('search.dt', function () {
            var searchTerm = table.search();
            $('.resume-text').each(function () {
                var modal = $(this).closest('.modal');
                if ($(this).text().toLowerCase().indexOf(searchTerm.toLowerCase()) !== -1) {
                    modal.show();
                } else {
                    modal.hide();
                }
            });
        });
    });
</script>
@endsection
