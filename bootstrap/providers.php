<?php

// bootstrap/providers.php

return [
    App\Providers\AppServiceProvider::class,
    // App\Providers\AuthServiceProvider::class, // Biasanya ini juga ada, atau ter-discover otomatis
    // App\Providers\EventServiceProvider::class, // Sama seperti di atas

    App\Modules\Karung\Providers\KarungServiceProvider::class, // <-- INI YANG BENAR
    App\Providers\AuthServiceProvider::class,
];