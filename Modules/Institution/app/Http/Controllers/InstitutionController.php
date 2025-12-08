<?php

namespace Modules\Institution\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use App\Models\Institution;
use Modules\Institution\Http\Requests\ProfileUpdateRequest;

class InstitutionController extends Controller
{
    /**
     * Display the institution dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $institution = $user->institution;
        $institutionName = $institution?->name ?? $user->organization ?? 'Unknown Institution';

        return Inertia::render('Institution::Dashboard', [
            'page' => 'dashboard',
            'institutionName' => $institutionName,
        ]);
    }

    /**
     * Display the institution profile page.
     */
    public function profile()
    {
        $user = Auth::user();
        $institution = $user->institution;

        return Inertia::render('Institution::Dashboard', [
            'page' => 'profile',
            'institution' => $institution?->toArray(),
            'institutionName' => $institution?->name ?? $user->organization ?? 'Unknown Institution',
            'canEdit' => $institution && $user->hasRole(Role::INSTITUTION_ADMIN),
        ]);
    }

    /**
     * Update the institution profile.
     */
    public function updateProfile(ProfileUpdateRequest $request)
    {
        $user = Auth::user();
        $institution = $user->institution;

        if (!$institution) {
            return Redirect::back()->withErrors([
                'error' => 'Institution profile not found.'
            ]);
        }

        // Get validated data (authorization is handled in ProfileUpdateRequest)
        $validated = $request->validated();

        // Update institution with validated data
        $institution->update($validated);

        return Redirect::route('institution.profile.index')->with([
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