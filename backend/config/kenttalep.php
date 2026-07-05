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

];
