{{-- Menggunakan layout utama aplikasi --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold mb-0">
            Pusat Unduhan Laporan
        </h2>
    </x-slot>

    <x-module-layout>
        <x-slot name="sidebar">
            @include('karung::layouts.partials.sidebar')
        </x-slot>

        {{-- ================= KONTEN UTAMA HALAMAN ================= --}}
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Pusat Unduhan Laporan</h5>
                        </div>
                        <div class="card-body">
                            @include('karung::components.flash-message')
                            <p class="text-muted">Di sini Anda dapat menemukan riwayat semua laporan yang telah Anda ekspor. File akan dihapus secara otomatis setelah 30 hari.</p>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col">Nama File</th>
                                            <th scope="col">Tanggal Dibuat</th>
                                            <th scope="col" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($exportedReports as $report)
                                            <tr>
                                                <td>
                                                    <i class="bi bi-file-earmark-spreadsheet-fill text-success me-2"></i>
                                                    {{ $report->filename }}
                                                </td>
                                                <td>{{ $report->created_at->format('d F Y, H:i') }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('karung.reports.download', ['filename' => $report->filename]) }}" class="btn btn-success btn-sm">
                                                        <i class="bi bi-download"></i> Download
                                                    </a>
                                                    @role('Super Admin TMT')
                                                    <form action="{{ route('karung.reports.download_center.destroy', $report->id) }}" method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="bi bi-trash3-fill"></i> Hapus
                                                        </button>
                                                    </form>
                                                    @endrole
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">Anda belum pernah mengekspor laporan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $exportedReports->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-module-layout>

    <x-slot name="scripts">
        <script>
            // Script konfirmasi hapus dengan SweetAlert2
            document.addEventListener('DOMContentLoaded', function () {
                const deleteForms = document.querySelectorAll('.delete-form');
                deleteForms.forEach(form => {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        Swal.fire({
                            title: 'Anda yakin?',
                            text: "File laporan dan catatannya akan dihapus secara permanen!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>
    </x-slot>
</x-app-layout>