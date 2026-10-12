@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Edit ARC'])

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6>Edit Advance Release Calendar</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('arc.update', $arc->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label>Nama Kegiatan</label>
                            <input type="text" name="nama_kegiatan" class="form-control"
                                   value="{{ $arc->nama_kegiatan }}" required>
                        </div>
                        <div class="mb-3">
                            <label>Tanggal Kegiatan</label>
                            <input type="date" name="tanggal_kegiatan" class="form-control"
                                   value="{{ $arc->tanggal_kegiatan }}" required>
                        </div>
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="{{ route('arc.index') }}" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
