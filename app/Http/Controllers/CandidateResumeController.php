<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidateResumeController extends Controller
{
    public function download(?User $candidate = null): StreamedResponse|Response
    {
        $candidate = $candidate ?? Auth::user();

        $path = $candidate->candidateMedia?->cv_path;

        abort_if(! $path || ! Storage::disk('public')->exists($path), 404, 'No resume available.');

        $filename = $this->filename($candidate, $path);

        return Storage::disk('public')->download($path, $filename);
    }

    public function show(?User $candidate = null): Response
    {
        $candidate = $candidate ?? Auth::user();

        $path = $candidate->candidateMedia?->cv_path;

        abort_if(! $path || ! Storage::disk('public')->exists($path), 404, 'No resume available.');

        $headers = [
            'Content-Type' => Storage::disk('public')->mimeType($path),
            'Content-Disposition' => 'inline; filename="'.$this->filename($candidate, $path).'"',
        ];

        return response(Storage::disk('public')->get($path), 200, $headers);
    }

    protected function filename(User $candidate, string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return Str::slug($candidate->name).'-resume.'.$extension;
    }
}
