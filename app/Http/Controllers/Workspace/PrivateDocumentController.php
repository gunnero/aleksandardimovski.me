<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class PrivateDocumentController extends Controller
{
    public function show(Request $request, JobApplication $application, string $document)
    {
        abort_unless($application->user_id === $request->user()->id, 404);
        abort_unless(preg_match('/\A[0-9a-f-]{36}\.(pdf|docx|txt)\z/i', $document) === 1, 404);
        $path = "job-applications/{$application->id}/{$document}";
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $document, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
