<?php

namespace Modules\Institution\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstitutionIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Get institution relationship
        $institution = $user->institution;

        // Check if user has an institution
        if (!$institution) {
            return redirect()->route('welcome')->with('error', 'You are not associated with any institution.');
        }

        // Check if institution is active
        if (!$institution->active_status) {
            return redirect()->route('welcome')->with('error', 'Your institution account is not active. Please contact support.');
        }

        return $next($request);
    }
}