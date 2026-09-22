<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves an applicant's uploads.
 *
 * Transcripts, recommendation letters and CVs are personal data, so they live
 * on the private disk and are streamed through here behind the same policy
 * that guards the application itself — the student who owns it, the
 * coordinator who runs the scholarship, and reviewers.
 */
class ApplicationDocumentController extends Controller
{
    public function show(ApplicationDocument $document): StreamedResponse
    {
        $this->authorize('view', $document->application);

        abort_unless($document->file_path && Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name ?? basename($document->file_path),
        );
    }
}
