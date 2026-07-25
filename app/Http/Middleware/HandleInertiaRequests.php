<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'canLogin' => fn () => Route::has('login'),
            'canRegister' => fn () => Route::has('register'),
            'laravelVersion' => fn () => Application::VERSION,
            'phpVersion' => fn () => PHP_VERSION,
            // ADD THIS PART BELOW:
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'danger' => fn () => $request->session()->get('danger'),
                'error' => fn() => $request->session()->get('error'),
            ],
             'recaptcha_site_key' => env('GOOGLE_RECAPTCHA_KEY'),
        ];
    }
}
