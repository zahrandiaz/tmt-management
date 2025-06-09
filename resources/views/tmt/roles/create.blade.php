@extends('layouts.tmt_app')

@section('title', 'Tambah Peran Baru - TMT Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Tambah Peran Baru</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tmt.admin.roles.store') }}" method="POST">
                        @csrf

                        {{-- Nama Peran --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Peran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Hak Akses (Permissions) --}}
                        <div class="mb-3">
                            <label class="form-label">Hak Akses (Permissions) <span class="text-danger">*</span></label>
                            <div class="row">
                                @foreach ($permissions as $permission)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="permission_{{ $permission->id }}">
                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('permissions')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('tmt.admin.roles.index') }}" class="btn btn-outline-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Peran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection