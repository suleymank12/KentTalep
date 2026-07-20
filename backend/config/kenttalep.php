<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Medya deposu (disk)
    |--------------------------------------------------------------------------
    |
    | Talep medyalarının saklandığı Flysystem diski. Varsayılan private
    | "local" disktir; production'da MinIO/S3'e geçiş yalnızca bu değeri
    | değiştirerek (MEDIA_DISK) yapılır. Public disk KULLANILMAZ — dosyalar
    | yalnız yetkili stream uç noktasından servis edilir.
    |
    */

    'media_disk' => env('MEDIA_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Genel (public) ayar whitelist'i
    |--------------------------------------------------------------------------
    |
    | GET /api/settings yalnız bu anahtarları döner (auth gerektirmez).
    | Whitelist dışı hiçbir Setting kaydı sızmaz.
    |
    */

    'public_settings' => [
        'municipality_name',
        'primary_color',
        'map_center_lat',
        'map_center_lng',
        'map_zoom',
        'map_tile_url',
    ],

];
