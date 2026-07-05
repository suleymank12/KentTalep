<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\TicketMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

afterEach(function (): void {
    Storage::disk('media_test')->deleteDirectory('tickets');
});

it('lets the owner upload a before photo', function (): void {
    $owner = userWithRole(Role::Citizen);
    $ticket = makeTicket(['status' => 'pending', 'user_id' => $owner->id]);

    post("/api/tickets/{$ticket->id}/media", [
        'file' => UploadedFile::fake()->image('foto.jpg', 1200, 900),
        'type' => 'before',
    ], bearer(tokenFor($owner)))
        ->assertCreated()
        ->assertJsonPath('data.type', 'before');
});

it('rejects a text file disguised as a jpg', function (): void {
    $owner = userWithRole(Role::Citizen);
    $ticket = makeTicket(['status' => 'pending', 'user_id' => $owner->id]);

    post("/api/tickets/{$ticket->id}/media", [
        'file' => UploadedFile::fake()->create('evil.jpg', 8),
        'type' => 'before',
    ], bearer(tokenFor($owner)))->assertStatus(422);
});

it('enforces the ten media per ticket limit', function (): void {
    $owner = userWithRole(Role::Citizen);
    $ticket = makeTicket(['status' => 'pending', 'user_id' => $owner->id]);

    for ($i = 0; $i < 10; $i++) {
        $ticket->media()->create([
            'uploaded_by' => $owner->id,
            'type' => 'before',
            'disk' => 'media_test',
            'path' => "seed/{$i}.jpg",
            'thumb_path' => "seed/{$i}_t.jpg",
            'original_name' => "{$i}.jpg",
            'mime_type' => 'image/jpeg',
            'size' => 1,
            'width' => 10,
            'height' => 10,
        ]);
    }

    post("/api/tickets/{$ticket->id}/media", [
        'file' => UploadedFile::fake()->image('foto.jpg', 600, 400),
        'type' => 'before',
    ], bearer(tokenFor($owner)))->assertStatus(422);
});

it('forbids a foreign citizen from uploading', function (): void {
    $owner = userWithRole(Role::Citizen);
    $other = userWithRole(Role::Citizen);
    $ticket = makeTicket(['status' => 'pending', 'user_id' => $owner->id]);

    post("/api/tickets/{$ticket->id}/media", [
        'file' => UploadedFile::fake()->image('foto.jpg', 600, 400),
        'type' => 'before',
    ], bearer(tokenFor($other)))->assertForbidden();
});

it('forbids a non-assigned staff from uploading an after photo', function (): void {
    $assigned = userWithRole(Role::Staff);
    $outsider = userWithRole(Role::Staff);
    $ticket = makeTicket(['status' => 'in_progress', 'assigned_to' => $assigned->id]);

    post("/api/tickets/{$ticket->id}/media", [
        'file' => UploadedFile::fake()->image('foto.jpg', 600, 400),
        'type' => 'after',
    ], bearer(tokenFor($outsider)))->assertForbidden();
});

it('streams media to authorized users, forbids others and deletes files', function (): void {
    $owner = userWithRole(Role::Citizen);
    $ticket = makeTicket(['status' => 'pending', 'user_id' => $owner->id]);

    $mediaId = post("/api/tickets/{$ticket->id}/media", [
        'file' => UploadedFile::fake()->image('foto.jpg', 800, 600),
        'type' => 'before',
    ], bearer(tokenFor($owner)))->assertCreated()->json('data.id');

    flushAuth();
    get("/api/ticket-media/{$mediaId}", bearer(tokenFor($owner)))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/jpeg');

    flushAuth();
    get("/api/ticket-media/{$mediaId}", bearer(tokenFor(userWithRole(Role::Citizen))))->assertForbidden();

    $media = TicketMedia::findOrFail((int) $mediaId);
    $path = $media->path;
    $thumb = $media->thumb_path;

    flushAuth();
    deleteJson("/api/ticket-media/{$mediaId}", [], bearer(tokenFor($owner)))->assertNoContent();

    expect(TicketMedia::find($mediaId))->toBeNull()
        ->and(Storage::disk('media_test')->exists($path))->toBeFalse()
        ->and(Storage::disk('media_test')->exists($thumb))->toBeFalse();
});

it('forbids the uploader from deleting an after photo once resolved', function (): void {
    $staff = userWithRole(Role::Staff);
    $ticket = makeTicket(['status' => 'in_progress', 'assigned_to' => $staff->id]);

    $mediaId = post("/api/tickets/{$ticket->id}/media", [
        'file' => UploadedFile::fake()->image('is.jpg', 800, 600),
        'type' => 'after',
    ], bearer(tokenFor($staff)))->assertCreated()->json('data.id');

    // Talep çözüldü: kanıt fotoğrafı artık yükleyence silinemez.
    $ticket->update(['status' => 'resolved', 'resolved_at' => now()]);

    flushAuth();
    deleteJson("/api/ticket-media/{$mediaId}", [], bearer(tokenFor($staff)))->assertForbidden();

    $media = TicketMedia::findOrFail((int) $mediaId);

    expect(Storage::disk('media_test')->exists($media->path))->toBeTrue()
        ->and(Storage::disk('media_test')->exists($media->thumb_path))->toBeTrue();
});

it('lets an admin delete media on a resolved ticket', function (): void {
    $staff = userWithRole(Role::Staff);
    $admin = userWithRole(Role::Admin);
    $ticket = makeTicket(['status' => 'in_progress', 'assigned_to' => $staff->id]);

    $mediaId = post("/api/tickets/{$ticket->id}/media", [
        'file' => UploadedFile::fake()->image('is.jpg', 800, 600),
        'type' => 'after',
    ], bearer(tokenFor($staff)))->assertCreated()->json('data.id');

    $media = TicketMedia::findOrFail((int) $mediaId);
    $path = $media->path;
    $thumb = $media->thumb_path;

    $ticket->update(['status' => 'resolved', 'resolved_at' => now()]);

    flushAuth();
    deleteJson("/api/ticket-media/{$mediaId}", [], bearer(tokenFor($admin)))->assertNoContent();

    expect(TicketMedia::find((int) $mediaId))->toBeNull()
        ->and(Storage::disk('media_test')->exists($path))->toBeFalse()
        ->and(Storage::disk('media_test')->exists($thumb))->toBeFalse();
});
