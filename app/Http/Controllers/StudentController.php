<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(): Response
    {
        $business = Auth::user()->businesses()->firstOrFail();

        return Inertia::render('Students/Index', [
            'students' => $business->students()
                ->with(['programme', 'latestCertificate:certificates.id,certificates.student_id,certificates.certificate_number,certificates.created_at'])
                ->latest()
                ->get(),
            'programmes' => $business->programmes()->where('is_archived', false)->get(['id', 'name', 'typical_duration']),
            'builtins' => [
                ['key' => 'classic-navy', 'label' => 'Classic Navy & Brass'],
            ],
            'customTemplates' => $business->certificateTemplates()->where('status', 'active')->get(['id', 'name']),
        ]);
    }

    public function complete(Student $student): RedirectResponse
    {
        $this->authorizeOwnership($student);

        $student->update([
            'completed_at' => now(),
            'end_at' => $student->end_at ?? now(),
        ]);

        return back()->with('success', 'Marked as completed.');
    }

    public function uncomplete(Student $student): RedirectResponse
    {
        $this->authorizeOwnership($student);

        $student->update(['completed_at' => null]);

        return back()->with('success', 'Reopened.');
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $business = Auth::user()->businesses()->firstOrFail();
        $business->students()->create($request->validated());

        return back()->with('success', 'Student added.');
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorizeOwnership($student);

        $student->update($request->validated());

        return back()->with('success', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorizeOwnership($student);

        // Safe even if the student has certificates — certificates.student_id is nullOnDelete,
        // and recipient_name/programme are already captured on the certificate itself.
        $student->delete();

        return back()->with('success', 'Student removed.');
    }

    private function authorizeOwnership(Student $student): void
    {
        abort_unless(
            $student->business_id === Auth::user()->businesses()->firstOrFail()->id,
            403
        );
    }
}
