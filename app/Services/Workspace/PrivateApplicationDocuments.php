<?php

namespace App\Services\Workspace;

use App\Models\JobApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PrivateApplicationDocuments
{
    private const MIMES = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];

    public function store(JobApplication $application, UploadedFile $file): string
    {
        if (! $file->isValid() || ! in_array($file->getMimeType(), self::MIMES, true) || $file->getSize() > 10 * 1024 * 1024) {
            throw ValidationException::withMessages(['document' => 'Document must be a valid PDF, DOCX, or text file no larger than 10 MB.']);
        }
        $extension = ['application/pdf' => 'pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx', 'text/plain' => 'txt'][$file->getMimeType()];
        $name = Str::uuid().'.'.$extension;
        $path = "job-applications/{$application->id}/{$name}";
        Storage::disk('local')->putFileAs("job-applications/{$application->id}", $file, $name);

        return $path;
    }

    public function deleteAll(JobApplication $application): void
    {
        Storage::disk('local')->deleteDirectory("job-applications/{$application->id}");
    }
}
