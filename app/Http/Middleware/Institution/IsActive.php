<?php

namespace App\Http\Middleware\Institution;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Role;

class IsActive
{
    /**
     * Handle an incoming request to check if user has institution access.
     * 
     * Allows access for users with institution-related roles:
     * - Institution_USER (basic institution access)
     * - Institution_ADMIN (institution admin)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('intended', $request->url());
        }

        $user = Auth::user();

        // Check if user account is active
        if (!$user->is_active) {
            // Log out the user and redirect to login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->withErrors(['error' => 'Your account is inactive. Please contact your administrator to activate your account.']);
        }

        // Check if user has BCeID access (institution users must be BCeID)
        if (empty($user->bceid_user_guid)) {
            // Log out the user and redirect to login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->withErrors(['error' => 'Institution access is only available to BCeID users.']);
        }

        // Institution roles - any of these grant access to institution module
        $institutionRoles = [
            Role::INSTITUTION_USER,
            Role::INSTITUTION_ADMIN,
        ];

        if (!$user->hasAnyRole($institutionRoles)) {
            // Log out the user and redirect to login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->withErrors(['error' => 'Access denied. You do not have institution access privileges.']);
        }

        return $next($request);
    }
}
