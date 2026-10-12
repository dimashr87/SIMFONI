@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Detail Fenomena'])

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Detail Fenomena</h6>
                </div>
                <div class="card-body">
                    <p><strong>Periode:</strong> {{ $fenomenum->periode }}</p>
                    <p><strong>Sumber:</strong> {{ $fenomenum->sumber }}</p>
                    <p><strong>Cakupan:</strong> {{ $fenomenum->cakupan }}</p>
                    <p><strong>Subjek:</strong> {{ $fenomenum->subjek }}</p>
                    <p><strong>Resume:</strong> {{ $fenomenum->resume }}</p>
                    <p><strong>Link Bukti:</strong> <a href="{{ $fenomenum->linkbukti }}" target="_blank">{{ $fenomenum->linkbukti }}</a></p>
                    <p><strong>Tanggal Created:</strong> {{ $fenomenum->tanggalcreated }}</p>
                </div>
            </div>
            @include('layouts.footers.auth.footer')
        </div>
    </div>
</div>
@endsection
