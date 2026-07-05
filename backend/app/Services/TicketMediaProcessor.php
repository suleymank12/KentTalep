<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;

/**
 * Yüklenen görseli güvenli ve gizlilik-uyumlu biçimde işler: gerçek MIME,
 * boyut ve piksel (decompression bomb) kontrolleri; EXIF orientation uygulama;
 * yeniden encode ile EXIF/GPS temizleme (KVKK); küçültme ve thumbnail; private
 * diske kayıt. İşleme senkrondur (queue yok).
 */
final class TicketMediaProcessor
{
    private const MAX_BYTES = 10 * 1024 * 1024;

    private const MAX_PIXELS = 40_000_000;

    private const MAX_LONG_EDGE = 2560;

    private const THUMB_LONG_EDGE = 480;

    private const JPEG_QUALITY = 82;

    /** @var list<string> */
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * @return array{disk: string, path: string, thumb_path: string, original_name: string, mime_type: string, size: int, width: int, height: int}
     */
    public function process(UploadedFile $file, int $ticketId): array
    {
        $realPath = $file->getRealPath();

        if ($realPath === false) {
            $this->reject();
        }

        // (1) Gerçek MIME'i finfo ile belirle — istemci uzantısına/başlığına
        //     güvenilmez (uzantı aldatmacası buradan yakalanır).
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo === false ? '' : (string) finfo_file($finfo, $realPath);

        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            $this->reject('Yalnızca JPEG, PNG veya WEBP görsel yükleyebilirsiniz.');
        }

        // (2) Dosya boyutu ≤ 10 MB.
        if ((int) $file->getSize() > self::MAX_BYTES) {
            $this->reject('Görsel en fazla 10 MB olabilir.');
        }

        // (3) Toplam piksel ≤ 40M (decompression bomb koruması).
        $dimensions = getimagesize($realPath);
        if ($dimensions === false || ($dimensions[0] * $dimensions[1]) > self::MAX_PIXELS) {
            $this->reject('Görsel çözünürlüğü çok yüksek.');
        }

        $image = ImageManager::gd()->read($realPath);

        // (4) EXIF orientation'ı uygula (görüntü dik kalsın), uzun kenarı 2560'a küçült.
        $image->orient()->scaleDown(self::MAX_LONG_EDGE, self::MAX_LONG_EDGE);

        // (5) JPEG q82 ile yeniden encode — EXIF/GPS bu adımda tamamen düşer.
        //     KVKK: vatandaş telefonu konumu görsele gömer; temizlenmeden saklanamaz.
        $mainBytes = (string) $image->toJpeg(quality: self::JPEG_QUALITY);
        $width = $image->width();
        $height = $image->height();

        // (6) 480px uzun kenarlı thumbnail (aynı işlenmiş görselden).
        $thumbBytes = (string) $image
            ->scaleDown(self::THUMB_LONG_EDGE, self::THUMB_LONG_EDGE)
            ->toJpeg(quality: self::JPEG_QUALITY);

        // (7) Private diske kaydet (public URL yok; yalnız yetkili stream).
        $disk = (string) config('kenttalep.media_disk');
        $uuid = (string) Str::uuid();
        $path = "tickets/{$ticketId}/{$uuid}.jpg";
        $thumbPath = "tickets/{$ticketId}/{$uuid}_thumb.jpg";

        Storage::disk($disk)->put($path, $mainBytes);
        Storage::disk($disk)->put($thumbPath, $thumbBytes);

        return [
            'disk' => $disk,
            'path' => $path,
            'thumb_path' => $thumbPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => 'image/jpeg',
            'size' => strlen($mainBytes),
            'width' => $width,
            'height' => $height,
        ];
    }

    private function reject(string $message = 'Geçersiz görsel.'): never
    {
        throw ValidationException::withMessages(['file' => $message]);
    }
}
