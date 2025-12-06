<?php

namespace Modules\Institution\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use App\Models\Institution;

class InstitutionController extends Controller
{
    /**
     * Display the institution dashboard.
     */
    public function index()
    {
        $data = $this->getCommonData();
        $user = Auth::user();

        return Inertia::render('Institution::Dashboard', array_merge($data, [
            'canEdit' => $user->hasRole(Role::INSTITUTION_ADMIN),
        ]));
    }

    /**
     * Get common data for dashboard and other pages.
     *
     * @return array
     */
    private function getCommonData(): array
    {
        $user = Auth::user();

        // Check if user is authenticated
        if (!$user) {
            abort(401, 'User not authenticated. Please log in first.');
        }

        $institution = $user->institution();

        if (!$institution) {
            // If no institution found, return default/empty data
            return [
                'auth' => ['user' => $user],
                'institution' => null,
                'institutionName' => $user->organization ?? 'Unknown Institution',
            ];
        }

        return [
            'auth' => ['user' => $user],
            'institution' => $institution,
            'institutionName' => $institution->name,
        ];
    }

    /**
     * Display the institution profile page.
     */
    public function profile()
    {
        $user = Auth::user();
        $institution = $user->institution();

        if (!$institution) {
            return Inertia::render('Institution::Profile', [
                'auth' => ['user' => $user],
                'institution' => null,
                'canEdit' => false,
                'message' => 'Institution profile not found. Please contact administrator.',
            ]);
        }

        // Check if user has INSTITUTION_ADMIN role for editing
        $canEdit = $user->hasRole(Role::INSTITUTION_ADMIN);

        return Inertia::render('Institution::Profile', [
            'auth' => ['user' => $user],
            'institution' => $institution,
            'canEdit' => $canEdit,
        ]);
    }

    /**
     * Update the institution profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $institution = $user->institution();

        // Check authorization
        if (!$user->hasRole(Role::INSTITUTION_ADMIN)) {
            return Redirect::back()->withErrors([
                'error' => 'You do not have permission to edit institution profile.'
            ]);
        }

        if (!$institution) {
            return Redirect::back()->withErrors([
                'error' => 'Institution profile not found.'
            ]);
        }

        // Validate request
        $validated = $request->validate([
            'display_name' => 'nullable|string|max:100',
            'primary_contact_email' => 'nullable|email|max:255',
            'primary_contact_phone' => 'nullable|string|max:20',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:10',
        ]);

        // Update institution with validated data
        $institution->update($validated);

        return Redirect::route('institution.profile')->with([
            'success' => 'Institution profile updated successfully.'
        ]);
    }

    // TODO: Implement applications feature
    // public function applications() {}
    // public function viewApplication($id) {}
    // public function reviewApplication(Request $request, $id) {}

    // TODO: Implement attestations feature
    // public function attestations() {}
    // public function viewAttestation($id) {}
    // public function revokeAttestation(Request $request, $id) {}

    // TODO: Implement reports feature
    // public function reports(Request $request) {}
    // public function exportReport(Request $request, $type) {}
}
