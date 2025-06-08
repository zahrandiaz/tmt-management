@extends('layouts.tmt_app')

@section('title', 'Dashboard TMT Management')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Dashboard TMT Management') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p>{{ __('Selamat datang di TMT Management!') }}</p>
                    <p>Anda berhasil login sebagai: <strong>{{ Auth::user()->name }}</strong></p>
                    <p>Email Anda: <strong>{{ Auth::user()->email }}</strong></p>

                    <hr>
                    <h5>Modul yang Tersedia:</h5>

                    @if(Auth::check() && (Auth::user()->can('karung.access_module') || Auth::user()->hasRole('Super Admin TMT')))
                        <div class="list-group mt-3">
                            <a href="{{ route('karung.dashboard') }}" class="list-group-item list-group-item-action fw-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-box-seam-fill me-2" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M15.528 2.973a.75.75 0 0 1 .472.696v8.662a.75.75 0 0 1-.472.696l-7.25 2.9a.75.75 0 0 1-.557 0l-7.25-2.9A.75.75 0 0 1 0 12.331V3.669a.75.75 0 0 1 .471-.696L7.443.184l.01-.003.268-.108a.75.75 0 0 1 .48-.006l.269.108.01.003zM10.404 2 4.25 4.461 1.846 3.5l6-2.402zM5 5.05C5 4.477 5.25 4.005 5.5 3.75h1v1h-1zM6.5 3.75h1v1h-1zM8 3.75h1v1h-1zM9.5 3.75h1v1h-1zm1.5.89L15 3.5l-2.404.964zm-.39-1.19L8 5.05l-2.11-.844zM1.5 4.633v5.092l1.57.627v-5.109zm13 0v5.108l-1.57.627v-5.092zM7.5 14.776V9.524l1 2.5V14.776zm1-9.278V9.5l.927-.371zM14 4.633V9.524l.927.371V5.005zm-13-.001V9.896l.927-.371V4.633zM4.25 8.864l3.25 1.3V14.77l-3.25-1.3zm7.5 0l-3.25 1.3V14.77l3.25-1.3z"/>
                                </svg>
                                Manajemen Toko Karung
                            </a>
                            {{-- <a href="#" class="list-group-item list-group-item-action disabled mt-2" tabindex="-1" aria-disabled="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cup-hot-fill me-2" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M.5 6a.5.5 0 0 0-.488.608l1.652 7.434A2.5 2.5 0 0 0 4.104 16h5.792a2.5 2.5 0 0 0 2.44-1.958l1.65-7.434A.5.5 0 0 0 13.5 6zM13 12.125H3.002L1.53 5.51A1.5 1.5 0 0 1 3.002 4h5.058a1.5 1.5 0 0 1 .99.316l2.306 1.982c.326.28.38.768.125 1.082M4.25 0A2.25 2.25 0 0 0 2 2.25V4h10V2.25A2.25 2.25 0 0 0 9.75 0z"/>
                                </svg>
                                Manajemen Toko Kopi (Segera Hadir)
                            </a> --}}
                        </div>
                    @else
                        <p class="mt-3">Anda tidak memiliki akses ke modul manapun saat ini atau belum ada modul yang dikonfigurasi.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection