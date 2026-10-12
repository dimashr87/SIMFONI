@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'User Management'])

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <!-- Card Header: Title -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Users</h6>
                        @if ($message = Session::get('success'))
                        <div class="alert alert-success mt-3">
                            <p class="mb-0">{{ $message }}</p>
                        </div>
                    @endif
                    </div>

                    <div class="card-body">
                        <!-- Users Table -->
                        <div class="table-responsive">
                            <table id="userTable" class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th class="text-center">Create Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $user->username }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">
                                                    @switch($user->level)
                                                        @case(1) Admin @break
                                                        @case(2) Supervisor @break
                                                        @case(3) Operator @break
                                                        @case(4) Viewer @break
                                                        @default Unknown Role
                                                    @endswitch
                                                </p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                @if ($user->created_at)
                                                    <span class="font-weight-bold">{{ $user->created_at->format('d/m/Y') }}</span>
                                                @else
                                                    <span class="font-weight-bold">N/A</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-info btn-sm">Edit</a>
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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

@push('css')
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
@endpush

@push('js')
    <!-- jQuery and DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script>
        $(document).ready(function () {
            $('#userTable').DataTable({
                "pagingType": "simple_numbers",
                "lengthMenu": [5, 10, 25, 50],
                "pageLength": 10,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": 3 } // Disable ordering on the 'Actions' column
                ],
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
@endpush
