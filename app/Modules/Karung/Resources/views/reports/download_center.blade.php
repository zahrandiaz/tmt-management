@extends('karung::layouts.karung_app')

@section('title', 'Pusat Unduhan Laporan')

@section('module-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Pusat Unduhan Laporan</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Di sini Anda dapat menemukan riwayat semua laporan yang telah Anda ekspor. File akan tersedia untuk diunduh selama beberapa waktu.</p>
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
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel-fill me-2" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M5.884 6.68 8 9.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 10l2.233 2.68a.5.5 0 0 1-.768.64L8 10.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 10 5.116 7.32a.5.5 0 1 1 .768-.64"/></svg>
                                            {{ $report->filename }}
                                        </td>
                                        <td>{{ $report->created_at->format('d F Y, H:i') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('karung.reports.download', ['filename' => $report->filename]) }}" class="btn btn-success btn-sm">
                                                Download
                                            </a>
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
@endsection