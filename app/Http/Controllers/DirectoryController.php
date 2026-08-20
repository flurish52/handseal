<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DirectoryController extends Controller
{
    /**
     * Public — no auth. Browse/search only, aggregate-safe fields only.
     * No individual certificates or student names ever surface here.
     */
    public function index(Request $request): Response
    {
        $businesses = Business::where('is_publicly_visible', true)
            ->when($request->string('q')->trim()->isNotEmpty(), function ($query) use ($request) {
                $query->where('business_name', 'like', '%' . $request->string('q')->trim() . '%');
            })
            ->withCount('certificates')
            ->orderBy('business_name')
            ->get(['id', 'business_name'])
            ->map(fn (Business $business) => [
                'business_name' => $business->business_name,
                'certificates_count' => $business->certificates_count,
            ]);

        return Inertia::render('Directory/Index', [
            'businesses' => $businesses,
            'q' => $request->string('q')->toString(),
        ]);
    }
}
