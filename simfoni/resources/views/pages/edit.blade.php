@extends('layouts.app')

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Edit User'])
    <div class="row mt-4 mx-4">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Edit User</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Username -->
                        <div class="form-group mb-3">
                            <label for="username" class="form-control-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                            @error('username')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="form-group mb-3">
                            <label for="email" class="form-control-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Role (Level) -->
                        <div class="form-group mb-3">
                            <label for="level" class="form-control-label">Role</label>
                            <select id="level" name="level" class="form-control">
                                <option value="1" {{ $user->level == 1 ? 'selected' : '' }}>Admin</option>
                                <option value="2" {{ $user->level == 2 ? 'selected' : '' }}>Supervisor</option>
                                <option value="3" {{ $user->level == 3 ? 'selected' : '' }}>Operator</option>
                                <option value="4" {{ $user->level == 4 ? 'selected' : '' }}>Viewer</option>
                            </select>
                            @error('level')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Subjek yang Disupervisi -->
                        <div class="form-group mb-3">
                            <label for="subjek" class="form-control-label">Subjek yang Disupervisi</label>
                            <div class="row">
                                @foreach($subjeks as $subjek)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                name="subjeks[]" 
                                                value="{{ $subjek->subjek }}"
                                                {{ in_array($subjek->subjek, $subjek_user) ? 'checked' : '' }}>
                                            <label class="form-check-label">
                                                {{ $subjek->subjek }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>


                        <!-- Update Button -->
                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-success">Update User</button>
                            <a href="{{ route('user.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
