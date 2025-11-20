<?php

namespace Modules\Institution\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstitutionController extends Controller
{
    /**
     * Display the institution dashboard.
     */
    public function index()
    {
        // For now, return simple static data for the dashboard
        // You can later replace this with actual database queries
        return Inertia::render('Institution::Dashboard', [
            'auth' => [
                'user' => [
                    'first_name' => 'Shareen',
                    'last_name' => 'Prasad',
                ]
            ],
            'institutionName' => 'College A Victoria',
            'totalAttestations' => 0,
            'reservedGradAttestations' => 0,
            'availableAttestations' => 0,
            'gradIssued' => 0,
            'gradDeclined' => 0,
            'undergradIssued' => 0,
            'undergradDeclined' => 0,
            'remainingUndergradAttestations' => 0,
        ]);
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
}
