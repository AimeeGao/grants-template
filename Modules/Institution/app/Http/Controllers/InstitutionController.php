<?php

namespace Modules\Institution\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Modules\Institution\Models\Institution;
use Modules\Institution\Models\Application;
use Modules\Institution\Models\Attestation;

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
            'attestationData' => $data['attestationData'],
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
                'attestationData' => [
                    'totalAttestations' => 0,
                    'reservedGradAttestations' => 0,
                    'availableAttestations' => 0,
                    'gradIssued' => 0,
                    'gradDeclined' => 0,
                    'undergradIssued' => 0,
                    'undergradDeclined' => 0,
                    'remainingUndergradAttestations' => 0,
                ],
            ];
        }

        // Calculate attestation data with real database queries
        $gradIssued = Attestation::forInstitution($institution->bceid_business_guid)
            ->type('graduate')
            ->issued()
            ->count();

        $gradDeclined = Attestation::forInstitution($institution->bceid_business_guid)
            ->type('graduate')
            ->declined()
            ->count();

        $undergradIssued = Attestation::forInstitution($institution->bceid_business_guid)
            ->type('undergraduate')
            ->issued()
            ->count();

        $undergradDeclined = Attestation::forInstitution($institution->bceid_business_guid)
            ->type('undergraduate')
            ->declined()
            ->count();

        // Calculate remaining undergrad attestations
        $undergradQuota = $institution->attestation_quota_undergrad ?? 0;
        $remainingUndergradAttestations = max(0, $undergradQuota - $undergradIssued);

        $attestationData = [
            'totalAttestations' => $institution->attestation_quota_total ?? 0,
            'reservedGradAttestations' => $institution->attestation_quota_grad ?? 0,
            'availableAttestations' => $institution->attestation_quota_undergrad ?? 0,
            'gradIssued' => $gradIssued,
            'gradDeclined' => $gradDeclined,
            'undergradIssued' => $undergradIssued,
            'undergradDeclined' => $undergradDeclined,
            'remainingUndergradAttestations' => $remainingUndergradAttestations,
        ];

        return [
            'auth' => ['user' => $user],
            'institution' => $institution,
            'institutionName' => $institution->name,
            'attestationData' => $attestationData,
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('institution::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('institution::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('institution::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}

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
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:10',
            'website' => 'nullable|url|max:255',
        ]);

        // Update institution with validated data
        $institution->update($validated);

        return Redirect::route('institution.profile')->with([
            'success' => 'Institution profile updated successfully.'
        ]);
    }

    /**
     * Display the applications list page.
     */
    public function applications(Request $request)
    {
        $user = Auth::user();
        $institution = $user->institution();

        if (!$institution) {
            return Inertia::render('Institution::Dashboard', [
                'auth' => ['user' => $user],
                'institution' => null,
                'error' => 'Institution not found. Please contact administrator.',
            ]);
        }

        // Get filter parameters
        $status = $request->query('status');
        $programLevel = $request->query('program_level');
        $search = $request->query('search');

        // Build query
        $query = Application::forInstitution($institution->bceid_business_guid)
            ->with(['user', 'reviewer'])
            ->orderBy('submitted_at', 'desc');

        // Apply filters
        if ($status) {
            $query->status($status);
        }

        if ($programLevel) {
            $query->programLevel($programLevel);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('application_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('first_name', 'LIKE', "%{$search}%")
                                ->orWhere('last_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Paginate results
        $applications = $query->paginate(15);

        // Calculate statistics
        $stats = [
            'total' => Application::forInstitution($institution->bceid_business_guid)->count(),
            'pending' => Application::forInstitution($institution->bceid_business_guid)->pending()->count(),
            'approved' => Application::forInstitution($institution->bceid_business_guid)->status('approved')->count(),
            'rejected' => Application::forInstitution($institution->bceid_business_guid)->status('rejected')->count(),
        ];

        return Inertia::render('Institution::Dashboard', [
            'auth' => ['user' => $user],
            'institution' => $institution,
            'institutionName' => $institution->name,
            'canEdit' => $user->hasRole(Role::INSTITUTION_ADMIN),
            'applications' => $applications,
            'applicationStats' => $stats,
            'filters' => [
                'status' => $status,
                'program_level' => $programLevel,
                'search' => $search,
            ],
        ]);
    }

    /**
     * View a specific application details.
     */
    public function viewApplication($id)
    {
        $user = Auth::user();
        $institution = $user->institution();

        if (!$institution) {
            return Redirect::back()->withErrors([
                'error' => 'Institution not found.'
            ]);
        }

        $application = Application::forInstitution($institution->bceid_business_guid)
            ->with(['user', 'reviewer'])
            ->findOrFail($id);

        return response()->json([
            'application' => $application,
        ]);
    }

    /**
     * Review and update an application status.
     */
    public function reviewApplication(Request $request, $id)
    {
        $user = Auth::user();
        $institution = $user->institution();

        // Check authorization
        if (!$user->hasRole(Role::INSTITUTION_ADMIN)) {
            return Redirect::back()->withErrors([
                'error' => 'You do not have permission to review applications.'
            ]);
        }

        if (!$institution) {
            return Redirect::back()->withErrors([
                'error' => 'Institution not found.'
            ]);
        }

        $application = Application::forInstitution($institution->bceid_business_guid)
            ->findOrFail($id);

        // Check if application can be reviewed
        if (!$application->canBeReviewed()) {
            return Redirect::back()->withErrors([
                'error' => 'This application cannot be reviewed in its current status.'
            ]);
        }

        // Validate request
        $validated = $request->validate([
            'status' => 'required|in:under_review,additional_info_required,approved,rejected',
            'review_notes' => 'nullable|string',
            'rejection_reason' => 'required_if:status,rejected|nullable|string',
            'approved_amount' => 'required_if:status,approved|nullable|numeric|min:0',
        ]);

        // Update application
        $updateData = [
            'status' => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by_user_id' => $user->id,
            'reviewed_by_user_guid' => $user->guid,
            'reviewed_at' => now(),
        ];

        if ($validated['status'] === 'rejected') {
            $updateData['rejection_reason'] = $validated['rejection_reason'];
            $updateData['rejected_at'] = now();
        }

        if ($validated['status'] === 'approved') {
            $updateData['approved_amount'] = $validated['approved_amount'];
            $updateData['approved_at'] = now();
        }

        $application->update($updateData);

        return Redirect::back()->with([
            'success' => 'Application reviewed successfully.'
        ]);
    }

    /**
     * Display the attestations list page.
     */
    public function attestations(Request $request)
    {
        $user = Auth::user();
        $institution = $user->institution();

        if (!$institution) {
            return Inertia::render('Institution::Dashboard', [
                'auth' => ['user' => $user],
                'institution' => null,
                'error' => 'Institution not found. Please contact administrator.',
            ]);
        }

        // Get filter parameters
        $status = $request->query('status');
        $attestationType = $request->query('attestation_type');
        $search = $request->query('search');

        // Build query
        $query = Attestation::forInstitution($institution->bceid_business_guid)
            ->with(['user', 'issuedBy', 'application'])
            ->orderBy('issue_date', 'desc');

        // Apply filters
        if ($status) {
            $query->status($status);
        }

        if ($attestationType) {
            $query->type($attestationType);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('attestation_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('first_name', 'LIKE', "%{$search}%")
                                ->orWhere('last_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Paginate results
        $attestations = $query->paginate(15);

        // Calculate statistics
        $stats = [
            'total' => Attestation::forInstitution($institution->bceid_business_guid)->count(),
            'gradIssued' => Attestation::forInstitution($institution->bceid_business_guid)
                ->type('graduate')->issued()->count(),
            'gradDeclined' => Attestation::forInstitution($institution->bceid_business_guid)
                ->type('graduate')->declined()->count(),
            'undergradIssued' => Attestation::forInstitution($institution->bceid_business_guid)
                ->type('undergraduate')->issued()->count(),
            'undergradDeclined' => Attestation::forInstitution($institution->bceid_business_guid)
                ->type('undergraduate')->declined()->count(),
        ];

        return Inertia::render('Institution::Dashboard', [
            'auth' => ['user' => $user],
            'institution' => $institution,
            'institutionName' => $institution->name,
            'canEdit' => $user->hasRole(Role::INSTITUTION_ADMIN),
            'attestations' => $attestations,
            'attestationStats' => $stats,
            'filters' => [
                'status' => $status,
                'attestation_type' => $attestationType,
                'search' => $search,
            ],
        ]);
    }

    /**
     * View a specific attestation details.
     */
    public function viewAttestation($id)
    {
        $user = Auth::user();
        $institution = $user->institution();

        if (!$institution) {
            return Redirect::back()->withErrors([
                'error' => 'Institution not found.'
            ]);
        }

        $attestation = Attestation::forInstitution($institution->bceid_business_guid)
            ->with(['user', 'issuedBy', 'application', 'revokedBy'])
            ->findOrFail($id);

        return response()->json([
            'attestation' => $attestation,
        ]);
    }

    /**
     * Revoke an attestation.
     */
    public function revokeAttestation(Request $request, $id)
    {
        $user = Auth::user();
        $institution = $user->institution();

        // Check authorization
        if (!$user->hasRole(Role::INSTITUTION_ADMIN)) {
            return Redirect::back()->withErrors([
                'error' => 'You do not have permission to revoke attestations.'
            ]);
        }

        if (!$institution) {
            return Redirect::back()->withErrors([
                'error' => 'Institution not found.'
            ]);
        }

        $attestation = Attestation::forInstitution($institution->bceid_business_guid)
            ->findOrFail($id);

        // Check if attestation can be revoked
        if (!$attestation->isIssued()) {
            return Redirect::back()->withErrors([
                'error' => 'Only issued attestations can be revoked.'
            ]);
        }

        // Validate request
        $validated = $request->validate([
            'revocation_reason' => 'required|string',
        ]);

        // Update attestation
        $attestation->update([
            'status' => 'revoked',
            'revocation_reason' => $validated['revocation_reason'],
            'revoked_by_user_id' => $user->id,
            'revoked_by_user_guid' => $user->guid,
            'revoked_at' => now(),
        ]);

        return Redirect::back()->with([
            'success' => 'Attestation revoked successfully.'
        ]);
    }

    /**
     * Display the reports page with analytics.
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        $institution = $user->institution();

        if (!$institution) {
            return Inertia::render('Institution::Dashboard', [
                'auth' => ['user' => $user],
                'institution' => null,
                'error' => 'Institution not found. Please contact administrator.',
            ]);
        }

        // Get filter parameters
        $reportType = $request->query('report_type', 'summary');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Generate report data based on type
        $reportData = $this->generateReportData($institution, $reportType, $startDate, $endDate);

        return Inertia::render('Institution::Dashboard', [
            'auth' => ['user' => $user],
            'institution' => $institution,
            'institutionName' => $institution->name,
            'canEdit' => $user->hasRole(Role::INSTITUTION_ADMIN),
            'reportData' => $reportData,
            'filters' => [
                'report_type' => $reportType,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Generate report data based on report type.
     */
    private function generateReportData($institution, $reportType, $startDate = null, $endDate = null)
    {
        $bceidGuid = $institution->bceid_business_guid;

        switch ($reportType) {
            case 'applications':
                return $this->getApplicationsReportData($bceidGuid, $startDate, $endDate);
            case 'attestations':
                return $this->getAttestationsReportData($bceidGuid, $startDate, $endDate);
            case 'monthly':
                return $this->getMonthlyActivityReportData($bceidGuid, $startDate, $endDate);
            case 'quota':
                return $this->getQuotaUsageReportData($institution);
            case 'summary':
            default:
                return $this->getSummaryReportData($institution, $bceidGuid, $startDate, $endDate);
        }
    }

    /**
     * Get applications report data.
     */
    private function getApplicationsReportData($bceidGuid, $startDate, $endDate)
    {
        $query = Application::forInstitution($bceidGuid);

        if ($startDate) {
            $query->where('submitted_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('submitted_at', '<=', $endDate);
        }

        return [
            'type' => 'applications',
            'total' => $query->count(),
            'byStatus' => [
                'draft' => (clone $query)->status('draft')->count(),
                'submitted' => (clone $query)->status('submitted')->count(),
                'under_review' => (clone $query)->status('under_review')->count(),
                'approved' => (clone $query)->status('approved')->count(),
                'rejected' => (clone $query)->status('rejected')->count(),
            ],
            'byProgramLevel' => [
                'graduate' => (clone $query)->programLevel('graduate')->count(),
                'undergraduate' => (clone $query)->programLevel('undergraduate')->count(),
            ],
            'recentApplications' => (clone $query)->with('user')->orderBy('submitted_at', 'desc')->limit(10)->get(),
        ];
    }

    /**
     * Get attestations report data.
     */
    private function getAttestationsReportData($bceidGuid, $startDate, $endDate)
    {
        $query = Attestation::forInstitution($bceidGuid);

        if ($startDate) {
            $query->where('issue_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('issue_date', '<=', $endDate);
        }

        return [
            'type' => 'attestations',
            'total' => $query->count(),
            'byStatus' => [
                'issued' => (clone $query)->issued()->count(),
                'declined' => (clone $query)->declined()->count(),
                'expired' => (clone $query)->where('status', 'expired')->count(),
                'revoked' => (clone $query)->where('status', 'revoked')->count(),
            ],
            'byType' => [
                'graduate' => (clone $query)->type('graduate')->count(),
                'undergraduate' => (clone $query)->type('undergraduate')->count(),
            ],
            'recentAttestations' => (clone $query)->with('user')->orderBy('issue_date', 'desc')->limit(10)->get(),
        ];
    }

    /**
     * Get monthly activity report data.
     */
    private function getMonthlyActivityReportData($bceidGuid, $startDate, $endDate)
    {
        // Get last 12 months if no date range provided
        if (!$startDate) {
            $startDate = now()->subMonths(12)->startOfMonth();
        }
        if (!$endDate) {
            $endDate = now()->endOfMonth();
        }

        $applications = Application::forInstitution($bceidGuid)
            ->whereBetween('submitted_at', [$startDate, $endDate])
            ->selectRaw('DATE_TRUNC(\'month\', submitted_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $attestations = Attestation::forInstitution($bceidGuid)
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->selectRaw('DATE_TRUNC(\'month\', issue_date) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'type' => 'monthly',
            'applications' => $applications,
            'attestations' => $attestations,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * Get quota usage report data.
     */
    private function getQuotaUsageReportData($institution)
    {
        $bceidGuid = $institution->bceid_business_guid;

        $gradIssued = Attestation::forInstitution($bceidGuid)->type('graduate')->issued()->count();
        $undergradIssued = Attestation::forInstitution($bceidGuid)->type('undergraduate')->issued()->count();

        return [
            'type' => 'quota',
            'quotas' => [
                'total' => $institution->attestation_quota_total ?? 0,
                'graduate' => $institution->attestation_quota_grad ?? 0,
                'undergraduate' => $institution->attestation_quota_undergrad ?? 0,
            ],
            'usage' => [
                'graduate' => [
                    'issued' => $gradIssued,
                    'remaining' => max(0, ($institution->attestation_quota_grad ?? 0) - $gradIssued),
                    'percentageUsed' => ($institution->attestation_quota_grad ?? 0) > 0
                        ? round(($gradIssued / $institution->attestation_quota_grad) * 100, 2)
                        : 0,
                ],
                'undergraduate' => [
                    'issued' => $undergradIssued,
                    'remaining' => max(0, ($institution->attestation_quota_undergrad ?? 0) - $undergradIssued),
                    'percentageUsed' => ($institution->attestation_quota_undergrad ?? 0) > 0
                        ? round(($undergradIssued / $institution->attestation_quota_undergrad) * 100, 2)
                        : 0,
                ],
            ],
        ];
    }

    /**
     * Get summary report data (all reports combined).
     */
    private function getSummaryReportData($institution, $bceidGuid, $startDate, $endDate)
    {
        return [
            'type' => 'summary',
            'applications' => $this->getApplicationsReportData($bceidGuid, $startDate, $endDate),
            'attestations' => $this->getAttestationsReportData($bceidGuid, $startDate, $endDate),
            'quota' => $this->getQuotaUsageReportData($institution),
        ];
    }

    /**
     * Export report data as CSV.
     */
    public function exportReport(Request $request, $type)
    {
        $user = Auth::user();
        $institution = $user->institution();

        if (!$institution) {
            return Redirect::back()->withErrors([
                'error' => 'Institution not found.'
            ]);
        }

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $reportData = $this->generateReportData($institution, $type, $startDate, $endDate);

        // Generate CSV
        $csv = $this->generateCSV($reportData, $type);

        $filename = sprintf(
            '%s_report_%s_%s.csv',
            $type,
            $institution->name,
            now()->format('Y-m-d')
        );

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Generate CSV from report data.
     */
    private function generateCSV($reportData, $type)
    {
        $output = fopen('php://temp', 'r+');

        switch ($type) {
            case 'applications':
                fputcsv($output, ['Applications Report']);
                fputcsv($output, ['']);
                fputcsv($output, ['Summary']);
                fputcsv($output, ['Total Applications', $reportData['total']]);
                fputcsv($output, ['']);
                fputcsv($output, ['By Status']);
                foreach ($reportData['byStatus'] as $status => $count) {
                    fputcsv($output, [ucfirst(str_replace('_', ' ', $status)), $count]);
                }
                break;

            case 'attestations':
                fputcsv($output, ['Attestations Report']);
                fputcsv($output, ['']);
                fputcsv($output, ['Summary']);
                fputcsv($output, ['Total Attestations', $reportData['total']]);
                fputcsv($output, ['']);
                fputcsv($output, ['By Status']);
                foreach ($reportData['byStatus'] as $status => $count) {
                    fputcsv($output, [ucfirst($status), $count]);
                }
                break;

            case 'quota':
                fputcsv($output, ['Quota Usage Report']);
                fputcsv($output, ['']);
                fputcsv($output, ['Quotas']);
                fputcsv($output, ['Total Quota', $reportData['quotas']['total']]);
                fputcsv($output, ['Graduate Quota', $reportData['quotas']['graduate']]);
                fputcsv($output, ['Undergraduate Quota', $reportData['quotas']['undergraduate']]);
                fputcsv($output, ['']);
                fputcsv($output, ['Graduate Usage']);
                fputcsv($output, ['Issued', $reportData['usage']['graduate']['issued']]);
                fputcsv($output, ['Remaining', $reportData['usage']['graduate']['remaining']]);
                fputcsv($output, ['Percentage Used', $reportData['usage']['graduate']['percentageUsed'] . '%']);
                fputcsv($output, ['']);
                fputcsv($output, ['Undergraduate Usage']);
                fputcsv($output, ['Issued', $reportData['usage']['undergraduate']['issued']]);
                fputcsv($output, ['Remaining', $reportData['usage']['undergraduate']['remaining']]);
                fputcsv($output, ['Percentage Used', $reportData['usage']['undergraduate']['percentageUsed'] . '%']);
                break;

            default:
                fputcsv($output, ['Summary Report']);
                fputcsv($output, ['Report generated on: ' . now()->format('Y-m-d H:i:s')]);
                break;
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
