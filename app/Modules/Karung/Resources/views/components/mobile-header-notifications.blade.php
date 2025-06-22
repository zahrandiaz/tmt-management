@auth
<div class="dropdown">
    <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-bell-fill" viewBox="0 0 16 16">
            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2m.995-14.901a1 1 0 1 0-1.99 0A5 5 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901"/>
        </svg>
        <template x-if="notificationCount > 0">
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" x-text="notificationCount"></span>
        </template>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow" style="width: 320px;">
        <li class="px-3 py-2 fs-6 fw-bold border-bottom">Notifikasi</li>
        <template x-if="notifications.length > 0">
            <template x-for="(notification, index) in notifications.slice(0, 5)" :key="notification.id">
                <li>
                    <div class="p-2 border-bottom">
                        <p class="mb-1 small text-muted" x-text="notification.data.message"></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <a :href="notification.data.download_url" class="fw-bold small" x-text="'Download: ' + notification.data.file_name.substring(0, 20) + '...'"></a>
                            <button @click="markAsRead(notification, index)" class="btn btn-sm btn-link text-success p-0" title="Tandai sudah dibaca">
                                Tandai Dibaca
                            </button>
                        </div>
                        <small class="d-block text-muted mt-1" x-text="new Date(notification.created_at).toLocaleString('id-ID')"></small>
                    </div>
                </li>
            </template>
        </template>
        <template x-if="notifications.length == 0">
             <li><span class="dropdown-item-text text-muted text-center small">Tidak ada notifikasi baru.</span></li>
        </template>
         <li><hr class="dropdown-divider"></li>
         <li><a class="dropdown-item text-center small" href="{{ route('karung.reports.download_center') }}">Lihat Semua Riwayat</a></li>
    </ul>
</div>
@endauth