// File: public/js/app-helpers.js

document.addEventListener('DOMContentLoaded', function () {
    /**
     * Menangani konfirmasi SweetAlert untuk form secara dinamis
     * dengan menggunakan data attributes pada elemen form.
     */
    const setupFormConfirmation = () => {
        document.querySelectorAll('form.needs-confirmation').forEach(form => {
            form.addEventListener('submit', function (event) {
                event.preventDefault(); // Mencegah form dikirim langsung

                const config = {
                    title: form.dataset.confirmTitle || 'Anda yakin?',
                    text: form.dataset.confirmText || "Aksi ini tidak dapat diurungkan.",
                    icon: form.dataset.confirmIcon || 'warning',
                    showCancelButton: true,
                    confirmButtonColor: form.dataset.confirmButtonColor || '#3085d6',
                    cancelButtonColor: form.dataset.cancelButtonColor || '#d33',
                    confirmButtonText: form.dataset.confirmButtonText || 'Ya, lanjutkan!',
                    cancelButtonText: form.dataset.cancelButtonText || 'Batal'
                };

                Swal.fire(config).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Jika dikonfirmasi, kirim form
                    }
                });
            });
        });
    };

    // Panggil fungsi untuk mengaktifkan event listener
    setupFormConfirmation();
});

/**
 * Membuat dan mengembalikan instance TomSelect dengan konfigurasi umum.
 * @param {HTMLElement} element - Elemen <input> atau <select> yang akan diinisialisasi.
 * @param {object} config - Konfigurasi spesifik untuk instance ini.
 * @returns {TomSelect}
 */
function createTomSelect(element, config) {
    const defaultConfig = {
        placeholder: '-- Pilih atau Cari --',
        maxItems: 1,
        // Tambahkan opsi default lain jika ada
    };

    // Gabungkan config default dengan config spesifik
    const finalConfig = { ...defaultConfig, ...config };

    return new TomSelect(element, finalConfig);
}