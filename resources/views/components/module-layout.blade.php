@props(['sidebar'])

<div class="d-flex flex-grow-1 w-100">
    {{-- ================= Sidebar ================= --}}
    <div class="offcanvas-lg offcanvas-start bg-dark text-white flex-shrink-0" tabindex="-1" id="moduleSidebar" aria-labelledby="moduleSidebarLabel" style="width: 280px;">
        
        {{-- [BARU] Header untuk Offcanvas --}}
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title" id="moduleSidebarLabel">Menu Utama</h5>
            
            {{-- [BARU] Tombol Close Standar Bootstrap untuk tema gelap --}}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#moduleSidebar" aria-label="Close"></button>
        </div>

        {{-- [BARU] Body untuk Offcanvas agar konten menu bisa di-scroll --}}
        <div class="offcanvas-body">
            {{-- Slot sidebar (berisi semua link menu) sekarang kita letakkan di dalam body --}}
            {{ $sidebar }}
        </div>

    </div>

    {{-- ================= Main Content ================= --}}
    <div class="d-flex flex-column flex-grow-1" style="min-width: 0;">
        <div class="flex-grow-1" style="overflow-y: auto;">
            <div class="p-3">
                {{ $slot }}
            </div>
        </div>

        {{-- Footer Terpusat --}}
        <footer class="text-center py-2 bg-light border-top">
             <div class="small text-muted">
                 TMT Management | Versi
                 <strong>{{ \App\Helpers\VersionHelper::get('version') ?? 'dev' }}</strong>
                 <span class="text-black-50">({{ \App\Helpers\VersionHelper::get('commit') ?? 'N/A' }})</span>
                 &copy; {{ date('Y') }}
             </div>
        </footer>
    </div>
</div>