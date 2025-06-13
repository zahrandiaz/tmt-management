@extends('layouts.tmt_app')

@section('title', 'Log Aktivitas Sistem - TMT Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Log Aktivitas Sistem</h5>
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left-circle-fill" viewBox="0 0 16 16">
                            <path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0m3.5 7.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5z"/>
                        </svg>
                        Kembali ke Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col" style="width: 15%;">Waktu</th>
                                    <th scope="col">Deskripsi Log</th>
                                    <th scope="col">Subjek</th>
                                    <th scope="col" style="width: 15%;">Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activities as $activity)
                                    <tr>
                                        <td>{{ $activity->created_at->format('d-m-Y H:i:s') }}</td>
                                        <td>{{ $activity->description }}</td>
                                        <td>
                                            @if ($activity->subject)
                                                <span class="badge bg-secondary">{{ class_basename($activity->subject_type) }}</span>
                                                ID: {{ $activity->subject_id }}
                                                @if(isset($activity->subject->name))
                                                    ({{ $activity->subject->name }})
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($activity->causer)
                                                {{ $activity->causer->name }}
                                            @else
                                                Sistem
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada aktivitas yang tercatat.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection