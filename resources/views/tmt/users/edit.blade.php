@extends('layouts.tmt_app')

@section('title', 'Edit Pengguna - TMT Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Edit Pengguna: {{ $user->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tmt.admin.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Nama --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password (Opsional) --}}
                        <div class="mb-3">
                            <label class="form-label">Ubah Password (Opsional)</label>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Password Baru">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Konfirmasi Password Baru">
                                </div>
                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                            </div>
                        </div>

                         {{-- Peran (Roles) --}}
                        <div class="mb-3">
                            <label for="roles" class="form-label">Peran (Roles) <span class="text-danger">*</span></label>
                            <select name="roles[]" id="roles" class="form-select @error('roles') is-invalid @enderror" required>
                                <option value="">-- Pilih Peran --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Untuk saat ini, pilih satu peran per pengguna.</small>
                            @error('roles')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('tmt.admin.users.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection