@extends('layouts.tmt_app')

@section('title', 'Edit Peran - TMT Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Edit Peran: {{ $role->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tmt.admin.roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Nama Peran --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Peran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $role->name) }}" required>
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
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="permission_{{ $permission->id }}"
                                            {{-- Cek apakah permission ini ada di array permission milik peran ini --}}
                                            {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                        >
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
                            <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection