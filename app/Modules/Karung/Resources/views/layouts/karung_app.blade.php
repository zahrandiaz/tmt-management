@extends('layouts.tmt_app')

@section('content')
<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <symbol id="home" viewBox="0 0 16 16"><path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4z"/></symbol>
    <symbol id="speedometer2" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4M3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707M2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10m9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5m.754-4.246a.39.39 0 0 0-.527-.02L7.547 7.31A.91.91 0 1 0 8.85 8.547l3.471-3.472a.39.39 0 0 0-.019-.527zM4.754 4.246a.39.39 0 0 1 .527.02l3.471 3.472a.91.91 0 0 1-1.302 1.302L4.246 5.527a.39.39 0 0 1 .02-.527z"/><path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.98 7.98 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.533 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z"/></symbol>
    <symbol id="table" viewBox="0 0 16 16"><path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm15 2h-4v3h4zm-5 0H6v3h4zm-5 0H1v3h4zm10 4H1v3h14zm-5 0H6v3h4zm-5 0H1v3h4z"/></symbol>
    <symbol id="grid" viewBox="0 0 16 16"><path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5zM2.5 10a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zM9 2.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5zM10.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5zM9 10.5A1.5 1.5 0 0 1 10.5 9h3A1.5 1.5 0 0 1 15 10.5v3A1.5 1.5 0 0 1 13.5 15h-3A1.5 1.5 0 0 1 9 13.5zM10.5 10a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5z"/></symbol>
    <symbol id="cash-coin" viewBox="0 0 16 16"><path d="M11 5a2 2 0 1 0-4 0 2 2 0 0 0 4 0z"/><path d="M4 8a.5.5 0 0 1 .5-.5h.755A3.5 3.5 0 0 1 13 8c0 .637-.167 1.233-.459 1.75H13.5a.5.5 0 0 1 0 1H11a.5.5 0 0 1-.5-.5v-1.5a.5.5 0 0 1 1 0v.538a2.5 2.5 0 1 0-3.813-3.004l-.34.682a.5.5 0 0 1-.447.278H4.5A.5.5 0 0 1 4 8z"/><path d="M16 8a8 8 0 1 1-16 0 8 8 0 0 1 16 0zM1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8z"/></symbol>
</svg>

<div class="d-flex flex-grow-1 w-100">
    <div class="offcanvas-lg offcanvas-start bg-dark text-white flex-shrink-0" tabindex="-1" id="moduleSidebar" aria-labelledby="moduleSidebarLabel" style="width: 280px;">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="moduleSidebarLabel">Menu Toko Karung</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#moduleSidebar" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body p-0">
        @include('karung::layouts.partials.sidebar')
      </div>
    </div>

    <div class="d-flex flex-column flex-grow-1" style="min-width: 0;">
        <div class="flex-grow-1" style="overflow-y: auto;">
            <div class="p-3">
                <nav aria-label="breadcrumb" class="mb-3 d-none d-lg-block">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard TMT</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('karung.dashboard') }}">Modul Toko Karung</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </nav>
                @yield('module-content')
            </div>
        </div>

        <footer class="text-center py-2 bg-gray-200 border-t border-gray-300">
                <div class="text-xs text-gray-500">
                    TMT Management | Versi
                    <strong>{{ \App\Helpers\VersionHelper::get('version') ?? 'dev' }}</strong>
                    <span class="text-gray-400">({{ \App\Helpers\VersionHelper::get('commit') ?? 'N/A' }})</span>
                    &copy; {{ date('Y') }}
                </div>
        </footer>
    </div>
</div>
@endsection