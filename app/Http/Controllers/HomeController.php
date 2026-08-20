<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // First launch from the installed PWA carries this marker.
        // Persist it in session so subsequent in-app navigation to "/"
        // (e.g. clicking the logo) still behaves like a PWA context,
        // even without the query string on every request.
        if ($request->query('source') === 'pwa') {
            $request->session()->put('is_pwa', true);
        }

        $isPwa = $request->session()->get('is_pwa', false);

        if ($isPwa) {
            return $request->user()
                ? redirect()->route('dashboard')
                : redirect()->route('login');
        }

        // Regular web visit — always show the landing page,
        // whether logged in or not. Nav already adapts for auth state.
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    // ...rest unchanged
}
