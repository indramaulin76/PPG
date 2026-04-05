<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $appName = $settings['app_name'] ?? 'SI - JEMAAH';
        $appLogo = isset($settings['app_logo']) ? asset('storage/' . $settings['app_logo']) : null;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $request->user()->only([
                    'id', 'name', 'email', 'role', 'is_active', 'desa_id', 'kelompok_id', 'scope_label',
                ]) : null,
            ],
            'global_settings' => [
                'app_name' => $appName,
                'app_logo' => $appLogo,
            ],
        ];
    }
}
