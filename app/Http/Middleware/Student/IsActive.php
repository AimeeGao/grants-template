<?php

namespace App\Http\Middleware\Student;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Role;

class IsActive
{
    /**
     * Handle an incoming request to check if user has student access.
     * 
     * Allows access for users with student-related roles:
     * - Student (basic student access)
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

        // Check if user has BCSC access (students must be BCSC)
        if ($user->identity_provider !== 'bcsc') {
            // Log out the user and redirect to login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->withErrors(['error' => 'Student access is only available to BC Services Card users.']);
        }

        // Student roles - any of these grant access to student module
        $studentRoles = [
            Role::STUDENT,
        ];

        if (!$user->hasAnyRole($studentRoles)) {
            // Log out the user and redirect to login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->withErrors(['error' => 'Access denied. You do not have student access privileges.']);
        }

        return $next($request);
    }
}
