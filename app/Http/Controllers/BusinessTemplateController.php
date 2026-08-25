<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessTemplateController extends Controller
{
    public function setDefaultBuiltin(Request $request): RedirectResponse
    {
        $validated = $request->validate(['builtin_template_key' => 'required|string']);
        $business = Auth::user()->businesses()->firstOrFail();

        // resolvedTemplateSelection() always prefers an active custom
        // template over the builtin default — setting one here while a
        // custom template is active would silently do nothing, so block
        // it instead of letting the UI lie.
        if ($business->hasActiveCustomTemplate()) {
            return back()->with('error', 'Deactivate your custom template first to use a built-in one.');
        }

        $business->update(['default_builtin_template_key' => $validated['builtin_template_key']]);

        return back()->with('success', 'Default template updated.');
    }
}
