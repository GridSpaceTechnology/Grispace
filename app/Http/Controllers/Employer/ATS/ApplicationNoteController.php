<?php

namespace App\Http\Controllers\Employer\ATS;

use App\Http\Controllers\Controller;
use App\Models\ApplicationNote;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationNoteController extends Controller
{
    public function index(JobApplication $application)
    {
        $this->authorizeAccess($application);

        $notes = $application->notes()->with('employer')->latest()->get();

        return response()->json($notes);
    }

    public function store(Request $request, JobApplication $application)
    {
        $this->authorizeAccess($application);

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $note = $application->notes()->create([
            'employer_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        $note->load('employer');

        if ($request->wantsJson()) {
            return response()->json($note, 201);
        }

        return redirect()->back()->with('success', 'Note added successfully.');
    }

    public function update(Request $request, ApplicationNote $note)
    {
        $this->authorizeAccess($note->application);

        if ($note->employer_id !== Auth::id()) {
            abort(403, 'You can only edit your own notes.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $note->update($validated);

        if ($request->wantsJson()) {
            return response()->json($note);
        }

        return redirect()->back()->with('success', 'Note updated successfully.');
    }

    public function destroy(ApplicationNote $note)
    {
        $this->authorizeAccess($note->application);

        if ($note->employer_id !== Auth::id()) {
            abort(403, 'You can only delete your own notes.');
        }

        $note->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Note deleted.'], 200);
        }

        return redirect()->back()->with('success', 'Note deleted successfully.');
    }

    protected function authorizeAccess(JobApplication $application): void
    {
        if ($application->job->employer_id !== Auth::id()) {
            abort(403, 'You can only manage your own applications.');
        }
    }
}
