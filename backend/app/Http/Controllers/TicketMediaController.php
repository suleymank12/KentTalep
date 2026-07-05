<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Tickets\DeleteTicketMedia;
use App\Actions\Tickets\UploadTicketMedia;
use App\Enums\TicketMediaType;
use App\Http\Requests\Tickets\StoreTicketMediaRequest;
use App\Http\Resources\TicketMediaResource;
use App\Models\Ticket;
use App\Models\TicketMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketMediaController extends Controller
{
    public function store(StoreTicketMediaRequest $request, Ticket $ticket, UploadTicketMedia $action): JsonResponse
    {
        $file = $request->file('file');

        if (! $file instanceof UploadedFile) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $media = $action->handle(
            $ticket,
            $this->authUser($request),
            $file,
            TicketMediaType::from($request->string('type')->value()),
        );

        return TicketMediaResource::make($media)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(TicketMedia $media): StreamedResponse
    {
        return Storage::disk($media->disk)->response(
            $media->path,
            null,
            ['Content-Type' => $media->mime_type],
        );
    }

    public function thumb(TicketMedia $media): StreamedResponse
    {
        return Storage::disk($media->disk)->response(
            $media->thumb_path,
            null,
            ['Content-Type' => 'image/jpeg'],
        );
    }

    public function destroy(TicketMedia $media, DeleteTicketMedia $action): Response
    {
        $action->handle($media);

        return response()->noContent();
    }
}
