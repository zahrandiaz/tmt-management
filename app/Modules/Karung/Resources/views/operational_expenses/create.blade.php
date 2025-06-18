@extends('karung::layouts.karung_app')
@section('title', 'Tambah Biaya Operasional')
@section('module-content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header"><h5 class="mb-0">Form Tambah Biaya Operasional</h5></div>
        <div class="card-body">
            <form action="{{ route('karung.operational-expenses.store') }}" method="POST">
                @csrf
                @include('karung::operational_expenses._form')
            </form>
        </div>
    </div>
</div>
@endsection