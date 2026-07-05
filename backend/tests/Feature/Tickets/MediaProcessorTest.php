<?php

declare(strict_types=1);

use App\Services\TicketMediaProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

afterEach(function (): void {
    Storage::disk('media_test')->deleteDirectory('tickets');
});

it('strips exif, downsizes and produces a thumbnail', function (): void {
    $file = UploadedFile::fake()->image('foto.jpg', 3000, 2000);

    $result = app(TicketMediaProcessor::class)->process($file, 777);

    // Uzun kenar 2560'a küçültülür.
    expect(max($result['width'], $result['height']))->toBe(2560)
        ->and($result['mime_type'])->toBe('image/jpeg');

    $bytes = (string) Storage::disk('media_test')->get($result['path']);

    // Yeniden encode edilen JPEG'de EXIF segmenti bulunmaz (KVKK).
    expect(str_contains($bytes, "Exif\x00\x00"))->toBeFalse()
        ->and(Storage::disk('media_test')->exists($result['thumb_path']))->toBeTrue();
});

it('rejects a non-image file disguised with a jpg extension', function (): void {
    $file = UploadedFile::fake()->create('evil.jpg', 8);

    expect(fn () => app(TicketMediaProcessor::class)->process($file, 778))
        ->toThrow(ValidationException::class);
});
