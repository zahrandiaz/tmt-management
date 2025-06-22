@auth
<div class="position-fixed bottom-0 end-0 p-3 d-none d-lg-block" style="z-index: 1100">
    <div x-show="open" 
         x-transition
         class="card shadow-lg" 
         style="width: 350px; display: none;">
        
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Notifikasi</h6>
            <button type="button" @click="open = false" class="btn-close" aria-label="Close"></button>
        </div>
        <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
            <ul class="list-group list-group-flush">
                <template x-if="notifications.length > 0">
                    <template x-for="(notification, index) in notifications" :key="notification.id">
                        <li class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <p class="mb-1 small" x-text="notification.data.message"></p>
                                <button @click="markAsRead(notification, index)" class="btn btn-sm btn-outline-success border-0" title="Tandai sudah dibaca">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/></svg>
                                </button>
                            </div>
                             <a :href="notification.data.download_url" class="fw-bold small" x-text="'Download: ' + notification.data.file_name.substring(0, 30) + '...'"></a>
                             <small class="d-block text-muted mt-1" x-text="new Date(notification.created_at).toLocaleString('id-ID')"></small>
                        </li>
                    </template>
                </template>
                <template x-if="notifications.length == 0">
                    <li class="list-group-item text-center text-muted">Tidak ada notifikasi baru.</li>
                </template>
            </ul>
        </div>
        <div class="card-footer text-center">
            <a href="{{ route('karung.reports.download_center') }}" class="small">Lihat Semua Riwayat</a>
        </div>
    </div>

    <button @click="open = !open" type="button" class="btn btn-primary btn-lg rounded-circle shadow-lg mt-3 float-end">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-bell-fill" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901"/></svg>
        <template x-if="notificationCount > 0">
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" x-text="notificationCount"></span>
        </template>
    </button>
</div>
@endauth