@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Export Fenomena'])

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <!-- Header -->
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Export Data Fenomena</h6>
                </div>

                <!-- Body -->
                <div class="card-body">
                    <form action="{{ route('export.fenomena') }}" method="GET" class="row g-3">
                        @csrf

                        <!-- Periode -->
                        <div class="col-md-6">
                            <label for="tanggal_awal" class="form-label">Tanggal Awal Periode</label>
                            <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_akhir" class="form-label">Tanggal Akhir Periode</label>
                            <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" required>
                        </div>

                        <!-- Cakupan -->
                        <div class="col-md-6">
                            <label for="cakupan" class="form-label">Cakupan</label>
                            <select name="cakupan" id="cakupan" class="form-select">
                                <option value="">-- Semua Cakupan --</option>
                                @foreach($cakupan as $c)
                                    <option value="{{ $c->cakupan }}">{{ $c->cakupan }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Kabupaten/Kota (muncul jika cakupan = Kabupaten/Kota) -->
                        <div class="col-md-6" id="kabupatenContainer" style="display: none;">
                            <label for="cakupankabkot" class="form-label">Kabupaten/Kota</label>
                            <select name="cakupankabkot" id="cakupankabkot" class="form-select">
                                <option value="">-- Pilih Kabupaten/Kota --</option>
                                @foreach ($kabkots as $kabkot)
                                    <option value="{{ $kabkot->wilayah }}">{{ $kabkot->wilayah }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subjek -->
                        <div class="col-md-6">
                            <label for="subjek" class="form-label">Subjek</label>
                            <select name="subjek" id="subjek" class="form-select">
                                <option value="">-- Semua Subjek --</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->subjek }}">{{ $s->subjek }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tombol Export -->
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                <i class="ni ni-cloud-download-95"></i> Export Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @include('layouts.footers.auth.footer')
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cakupanSelect = document.getElementById('cakupan');
        const kabupatenContainer = document.getElementById('kabupatenContainer');

        cakupanSelect.addEventListener('change', function () {
            if (this.value === 'Kabupaten/Kota') {
                kabupatenContainer.style.display = 'block';
            } else {
                kabupatenContainer.style.display = 'none';
                document.getElementById('cakupankabkot').value = '';
            }
        });
    });
</script>
@endsection
