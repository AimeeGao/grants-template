<?php

namespace App\Http\Middleware\Ministry;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Role;

class IsActive
{
    /**
     * Handle an incoming request to check if user has ministry access.
     * 
     * Allows access for users with any ministry-related role:
     * - Ministry_User (basic ministry access)
     * - Ministry_Admin (ministry admin)
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

        // Check if user has IDIR access (ministry users must be IDIR)
        // However, admin users accessing ministry via admin panel are allowed
        if (empty($user->idir_user_guid) && !session('admin_accessing_ministry')) {
            // Log out the user and redirect to login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->withErrors(['error' => 'Ministry access is only available to government users (IDIR).']);
        }

        // Ministry roles - any of these grant access to ministry module
        $ministryRoles = [
            Role::MINISTRY_USER,
            Role::MINISTRY_ADMIN
        ];

        // Admin roles - these can access ministry when coming through admin panel
        $adminRoles = [
            Role::SUPER_ADMIN,
            Role::APPLICATION_MANAGER,
            Role::ADMIN_MANAGER,
            Role::SECURITY_OFFICER,
            Role::PRIVACY_OFFICER
        ];

        // Allow access if user has ministry roles or admin roles with session flag
        if (!$user->hasAnyRole($ministryRoles) && 
            !($user->hasAnyRole($adminRoles))) {
            // Log out the user and redirect to login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->withErrors(['error' => 'Access denied. You do not have ministry access privileges.']);
        }

        return $next($request);
    }
}
