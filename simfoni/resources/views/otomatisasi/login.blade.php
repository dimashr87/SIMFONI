@extends('layouts.otomatisasi')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-4 col-lg-5 col-md-7">
        <div class="card mt-5">
            <div class="card-header pb-0 text-start">
                <h4 class="font-weight-bolder">Login Otomatisasi</h4>
                <p class="mb-0">Masukkan email dan password akun SIMFONI Anda</p>
            </div>
            <div class="card-body">
                <form role="form" method="POST" action="{{ route('otomatisasi.login.perform') }}">
                    @csrf
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control form-control-lg"
                               placeholder="Email" value="{{ old('email') ?? '' }}"
                               required autofocus aria-label="Email">
                        @error('email')
                        <p class="text-danger text-xs pt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control form-control-lg"
                               placeholder="Password" required aria-label="Password">
                        @error('password')
                        <p class="text-danger text-xs pt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-lg btn-primary w-100 mt-4 mb-0">
                            Sign in
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center pt-0">
                <a href="{{ route('login') }}" class="text-primary text-gradient font-weight-bold text-sm">
                    &larr; Kembali ke login SIMFONI
                </a>
            </div>
        </div>
    </div>
</div>
@endsection