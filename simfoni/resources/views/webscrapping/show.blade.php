@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Detail Web Scrapping'])

<div class="container-fluid py-4">
    <div class="card">
        <div class="card-body">
            <h4>{{ $row->judul }}</h4>
            <p><strong>Deskripsi:</strong> {{ $row->deskripsi }}</p>
            <p><strong>Tanggal Mulai:</strong> {{ $row->tanggal_mulai }}</p>
            <p><strong>Tanggal Berakhir:</strong> {{ $row->tanggal_berakhir }}</p>
            <p><strong>Link:</strong> <a href="{{ $row->link }}" target="_blank">{{ $row->link }}</a></p>
            <a href="{{ route('webscrapping.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</div>
@endsection
