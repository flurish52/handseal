<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProgrammeRequest;
use App\Http\Requests\UpdateProgrammeRequest;
use App\Models\Programme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProgrammeController extends Controller
{
    public function index(): Response
    {
        $business = Auth::user()->businesses()->firstOrFail();

        return Inertia::render('Programmes/Index', [
            'programmes' => $business->programmes()->latest()->get(),
        ]);
    }

    public function store(StoreProgrammeRequest $request): RedirectResponse
    {
        $business = Auth::user()->businesses()->firstOrFail();

        $business->programmes()->create($request->validated());

        return back()->with('success', 'Programme added.');
    }

    public function update(UpdateProgrammeRequest $request, Programme $programme): RedirectResponse
    {
        $this->authorizeOwnership($programme);

        abort_if(
            $programme->certificates()->exists(),
            422,
            'This programme has certificates issued against it and can no longer be edited. Archive it and create a new programme instead.'
        );

        $programme->update($request->validated());

        return back()->with('success', 'Programme updated.');
    }

    public function archive(Programme $programme): RedirectResponse
    {
        $this->authorizeOwnership($programme);

        $programme->update(['is_archived' => true]);

        return back()->with('success', 'Programme archived.');
    }

    public function restore(Programme $programme): RedirectResponse
    {
        $this->authorizeOwnership($programme);

        $programme->update(['is_archived' => false]);

        return back()->with('success', 'Programme restored.');
    }

    public function destroy(Programme $programme): RedirectResponse
    {
        $this->authorizeOwnership($programme);

        abort_if(
            $programme->certificates()->exists(),
            422,
            'This programme has certificates issued against it and can\'t be deleted. Archive it instead.'
        );

        $programme->delete();

        return back()->with('success', 'Programme removed.');
    }

    /**
     * Route-model-bound $programme could belong to any business — always confirm
     * it belongs to the authenticated owner's business before mutating it.
     */
    private function authorizeOwnership(Programme $programme): void
    {
        abort_unless(
            $programme->business_id === Auth::user()->businesses()->firstOrFail()->id,
            403
        );
    }
}
