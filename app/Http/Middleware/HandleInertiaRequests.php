<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use Illuminate\Support\Facades\Session;
use App\Models\User;

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
        $logoutUrl = null;
        $logoutBcscUrl = null;
        $user = $request->user();

        //if the user is logged in set $logoutUrl and $logoutBcscUrl
        if ($user) {
            $logoutUrl = Session::get('kc_logout_uri_' . $request->user()->id);
            $logoutBcscUrl = Session::get('bcsc_logout_uri_' . $request->user()->id);
            $user = User::where('id', $request->user()->id)->with('roles')->first();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                // Include user information with roles
                'user' => $user,
                'roles' => $user ? $user->roles : [],
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'logoutUrl' => $logoutUrl,
            'logoutBcscUrl' => $logoutBcscUrl,
        ];
    }
}
