<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    /**
     * Display the login view.
     */
    public function login(Request $request): Response|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return Inertia::render('Auth/Login', [
            'loginAttempt' => false,
            'hasAccess' => false,
            'status' => session('status'),
        ]);
    }

    /**
     * IDIB/BCSC/BCeID login.
     */
    public function portalLogin(Request $request)
    {
        $provider = new Keycloak([
            'authServerUrl' => config('auth.keycloak.auth_server_url'),
            'realm' => config('auth.keycloak.realm'),
            'clientId' => config('auth.keycloak.client_id'),
            'clientSecret' => config('auth.keycloak.client_secret'),
            'redirectUri' => config('auth.keycloak.redirect_uri'),
            'scopes' => 'openid profile email',
        ]);

        return $this->loginUser($request, $provider);
    }

    /**
     * IDIR/Government staff login.
     */
    public function idirLogin(Request $request)
    {
        $provider = new Keycloak([
            'authServerUrl' => config('auth.keycloak.auth_server_url'),
            'realm' => config('auth.keycloak.realm'),
            'clientId' => config('auth.keycloak.client_id'),
            'clientSecret' => config('auth.keycloak.client_secret'),
            'redirectUri' => config('auth.keycloak.redirect_uri'),
            'scopes' => 'openid profile email',
        ]);

        return $this->loginUser($request, $provider, 'idir');
    }

    /**
     * BC Services Card login.
     */
    public function bcscLogin(Request $request)
    {
        $provider = new Keycloak([
            'authServerUrl' => config('auth.keycloak.auth_server_url'),
            'realm' => config('auth.keycloak.realm'),
            'clientId' => config('auth.keycloak.client_id'),
            'clientSecret' => config('auth.keycloak.client_secret'),
            'redirectUri' => config('auth.keycloak.redirect_uri'),
            'scopes' => 'openid profile email',
        ]);

        return $this->loginUser($request, $provider, 'bcsc');
    }

    /**
     * BCeID login.
     */
    public function bceidLogin(Request $request)
    {
        $provider = new Keycloak([
            'authServerUrl' => config('auth.keycloak.auth_server_url'),
            'realm' => config('auth.keycloak.realm'),
            'clientId' => config('auth.keycloak.client_id'),
            'clientSecret' => config('auth.keycloak.client_secret'),
            'redirectUri' => config('auth.keycloak.redirect_uri'),
            'scopes' => 'openid profile email',
        ]);

        return $this->loginUser($request, $provider, 'bceid');
    }

    /**
     * Handle OAuth login flow.
     */
    private function loginUser(Request $request, $provider, string $idpType = null)
    {
        if (!$request->has('code')) {
            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl([
                'scope' => 'openid profile email',
            ]);

            $request->session()->put('oauth2state', $provider->getState());

            Log::info('Redirecting to Keycloak for authentication', [
                'idp_type' => $idpType,
                'auth_url' => $authUrl,
            ]);

            // Add IDP hints based on login type
            if ($idpType === 'bcsc') {
                return redirect($authUrl . '&kc_idp_hint=' . env('KEYCLOAK_CLIENT_ID'));
            } elseif ($idpType === 'bceid') {
                return redirect($authUrl . '&kc_idp_hint=bceidbusiness');
            } elseif ($idpType === 'idir') {
                return redirect($authUrl . '&kc_idp_hint=azureidir');
            }

            // Default redirect without hint
            return redirect($authUrl);
        } elseif (!$request->has('state') || ($request->state !== $request->session()->get('oauth2state'))) {
            // Invalid state - security check failed
            Log::warning('OAuth state mismatch', [
                'request_state' => $request->state,
                'session_state' => $request->session()->get('oauth2state'),
            ]);

            $request->session()->forget('oauth2state');

            return Inertia::render('Auth/Login', [
                'loginAttempt' => true,
                'hasAccess' => false,
                'status' => 'Authentication failed. Please try again.',
            ]);
        } else {
            // We have a valid code, exchange it for a token

            // Get IDP type from session using the state as key
            $state = $request->session()->get('oauth2state');

            try {
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $request->code,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to get access token', [
                    'error' => $e->getMessage(),
                ]);

                return Inertia::render('Auth/Login', [
                    'loginAttempt' => true,
                    'hasAccess' => false,
                    'status' => 'Failed to get access token',
                ]);
            }

            // Get user profile from Keycloak
            try {
                $providerUser = $provider->getResourceOwner($token);
                $providerUser = $providerUser->toArray();

                Log::info('User authenticated with Keycloak', [
                    'user_email' => $providerUser['email'] ?? 'unknown',
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to get resource owner', [
                    'error' => $e->getMessage(),
                ]);

                return Inertia::render('Auth/Login', [
                    'loginAttempt' => true,
                    'hasAccess' => false,
                    'status' => 'Failed to get user information: ' . $e->getMessage(),
                ]);
            }

            // Find or create user based on IDP type
            [$user, $idpType] = $this->findOrCreateUser($providerUser, $token, $request);

            if (!$user) {
                return Inertia::render('Auth/Login', [
                    'loginAttempt' => true,
                    'hasAccess' => false,
                    'status' => 'Access denied. Please contact administrator.',
                ]);
            }

            // Log the user in
            Auth::login($user);

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'idp_type' => $idpType,
            ]);

            // Redirect based on user role
            return $this->redirectToDashboard();
        }
    }

    /**
     * Find or create user based on identity provider.
     * @return array|null [User, idpType] or null
     */
    private function findOrCreateUser(array $providerUser, $token = null, Request $request = null): ?array
    {
        Log::info('findOrCreateUser', [
            'providerUser' => $providerUser,
            'token' => $token,
        ]);

        $user = User::where('keycloak_id', $providerUser['sub'])->first();

        /* 
        if $providerUser['bceid_user_guid'] is set, $idpType = bceid;
        if $providerUser['idir_user_guid'] is set, $idpType = idir;
        else it is bcsc 
        */
        $idpType = 'bcsc';
        if (isset($providerUser['bceid_user_guid'])) {
            $idpType = 'bceid';
        } elseif (isset($providerUser['idir_user_guid'])) {
            $idpType = 'idir';
        }

        // If user doesn't exist, create them
        if (!$user && $this->shouldCreateUser($providerUser)) {
            $user = $this->createNewUser($providerUser, $idpType, $token);
        }

        // Update user information and tokens for existing users
        if ($user) {
            if (isset($providerUser['name'])) {
                $user->name = $providerUser['name'];
            }

            // Update tokens if provided
            if ($token) {
                $user->kc_token = $token->getToken();
                if ($token->getRefreshToken()) {
                    $user->kc_refresh_token = $token->getRefreshToken();
                }
            }

            $user->save();
        }

        // TODO: Implement BCSC SSO logout URIs session storage

        return array($user, $idpType);
    }

    /**
     * Create new user.
     */
    private function createNewUser(array $providerUser, string $idpType, $token = null): User
    {
        $user = new User();
        $user->guid = Str::orderedUuid()->getHex();
        $user->name = Str::upper($providerUser['name'] ?? '');
        $user->first_name = Str::upper($providerUser['given_name'] ?? '');
        $user->last_name = Str::upper($providerUser['family_name'] ?? '');
        $user->email = Str::lower($providerUser['email'] ?? '');
        $user->display_name = Str::upper($providerUser['display_name'] ?? '');
        $user->family_name = Str::upper($providerUser['family_name'] ?? '');
        $user->given_name = Str::upper($providerUser['given_name'] ?? '');

        $user->identity_provider = $idpType;
        $user->keycloak_id = $providerUser['sub'];

        // Store tokens if provided
        if ($token) {
            $user->kc_token = $token->getToken();
            if ($token->getRefreshToken()) {
                $user->kc_refresh_token = $token->getRefreshToken();
            }
        }

        // Set IDP-specific fields
        switch ($idpType) {
            case 'bcsc':
                // default bcsc user to active state
                $user->is_active = true;
                break;

            case 'idir':
                $user->idir_user_guid = $providerUser['idir_user_guid'] ?? null;
                $user->idir_username = $providerUser['idir_username'] ?? null;
                break;

            case 'bceid':
                $user->bceid_user_guid = $providerUser['bceid_user_guid'] ?? null;
                $user->bceid_username = $providerUser['bceid_username'] ?? null;
                $user->bceid_business_guid = $providerUser['bceid_business_guid'] ?? null;
                $user->organization = Str::upper($providerUser['bceid_business_name'] ?? '');
                break;
        }

        $user->save();

        // Assign default role based on IDP type
        $this->assignDefaultRole($user, $idpType);

        Log::info('New user created', [
            'user_id' => $user->id,
            'email' => $user->email,
            'idp_type' => $idpType,
            'keycloak_id' => $user->keycloak_id,
        ]);

        return $user;
    }

    /**
     * Check if we should create a new user.
     */
    private function shouldCreateUser(array $providerUser): bool
    {
        // Add validation logic here
        return isset($providerUser['email']) && !empty($providerUser['email']);
    }

    /**
     * Assign default role based on identity provider.
     */
    private function assignDefaultRole(User $user, string $idpType): void
    {
        // Check the current route to determine if this is admin login
        $isAdminLogin = request()->is('admin/*') || session('admin_login_requested');

        $roleMap = [
            'bcsc' => Role::STUDENT,
            'idir' => $isAdminLogin ? Role::ADMIN_GUEST : Role::MINISTRY_USER,  // Admin vs Ministry based on login route
            'bceid' => Role::INSTITUTION_USER,
        ];

        if (isset($roleMap[$idpType])) {
            $role = Role::where('name', $roleMap[$idpType])->first();
            if ($role) {
                $user->roles()->attach($role);

                // Set is_active to false only for new admin IDIR users (Admin Guest)
                if ($idpType === 'idir' && $isAdminLogin) {
                    $user->is_active = false;
                    $user->save();
                }
            }
        }
    }

    /**
     * Log the user out (handles both GET and POST).
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            Log::info('User logging out', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        Auth::logout($user);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flush(); // Clear all session data

        return redirect('/login')->with('message', 'You have been logged out successfully.');
    }

    // TODO: Implement SSO logout when .env variables are confirmed
    

    /**
     * Redirect to appropriate dashboard based on user roles and IDP type.
     */
    private function redirectToDashboard(): RedirectResponse
    {
        $user = Auth::user();
        
        // TODO: Implement Admin redirects logic

        // IDP-based routing for general login
        // IDIR users (ministry) - check by role or IDP type
        if (
            $user->hasAnyRole([Role::MINISTRY_USER, Role::MINISTRY_ADMIN]) ||
            (!empty($user->idir_user_guid) && !$user->hasRole(Role::ADMIN_GUEST))
        ) {
            return redirect()->route('ministry.dashboard');
        }

        // BCeID users (institutions)
        if ($user->hasAnyRole([Role::INSTITUTION_ADMIN, Role::INSTITUTION_USER]) || !empty($user->bceid_user_guid)) {
            return redirect()->route('institution.dashboard');
        }

        // BCSC users (students)
        if ($user->hasRole(Role::STUDENT) || !empty($user->bcsc_user_guid)) {
            return redirect()->route('student.dashboard');
        }

        // Default dashboard for other cases
        return redirect()->route('login')
            ->withErrors(['error' => 'Could not access dashboard. Please contact an administrator. Error #0082940']);
    }
}
