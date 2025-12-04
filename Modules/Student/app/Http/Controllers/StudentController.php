<?php

namespace Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Modules\Student\Http\Requests\ProfileUpdateRequest;
use App\Models\Student;
use App\Models\Demographic;
use App\Models\StudentDemographic;
use App\Models\StudentDemographicAnswer;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->getCommonData();
        return Inertia::render('Student::Dashboard', array_merge($data, [
            'page' => 'dashboard', 
        ]));
    }

    /**
     * Display the profile page.
     */
    public function profile()
    {
        $data = $this->getCommonData();
        return Inertia::render('Student::Dashboard', array_merge($data, [
            'page' => 'profile', 
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('student::create');
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
        return view('student::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('student::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProfileUpdateRequest $request) 
    {
        $validated = $request->validated();

        $user = Auth::user();
        $student = Student::firstOrNew(['user_guid' => $user->guid]);
        // TODO: modify the logic as needed
        if (!$student->exists) {
            $student->guid = Str::orderedUuid()->getHex();
        }

        // Use SSO info here
        $student->user_guid = $user->guid; 
        $student->first_name = $user->first_name;
        $student->last_name = $user->last_name;
        $student->email = $user->email;

        // Update editable fields
        $student->sin = $validated['sin'];
        $student->dob = $validated['dob'];
        $student->gender = $validated['gender'];
        $student->address = $validated['address'];
        $student->city = $validated['city'];
        $student->zip_code = $validated['zip_code'];
        $student->bc_resident = $request->boolean('bc_resident');
        $student->info_consent = $request->boolean('info_consent');

        $student->save();

        // Save dynamic demographics fields
        $demographicInput = $validated['demographics'] ?? [];
        $this->saveDemographics($student, $demographicInput);

        return Redirect::route('student.profile.index')->with('success', 'Profile updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request) 
    {
        $student = Auth::user()->student;
        
        if ($student) {
            $student->delete();
        }

        // Logout
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('login')->with('success', 'Your account has been deactivated.');
    }
    
    /**
     * Get common data.
     */
    private function getCommonData()
    {
        $user = Auth::user();
        $student = $user->student()->with(['demographics.answers'])->first();
        $demographics = Demographic::with('options')->where('active', true)->orderBy('order')->get();

        $profileData = $student ? $student->toArray() : [
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->email,
        ];

        $existingDemographics = $student ? $student->getFormattedDemographics() : [];

        return [
            'profileData' => $profileData,
            'demographics' => $demographics,
            'existingDemographics' => $existingDemographics,
        ];
    }

    /**
     * Save demographics for a student.
     */
    private function saveDemographics(Student $student, array $demographics)
    {
        DB::transaction(function () use ($student, $demographics) {
            foreach ($demographics as $demographicData) {
                if (!isset($demographicData['demographic_id']) || !isset($demographicData['answers'])) {
                    continue;
                }
                
                $demographicId = $demographicData['demographic_id'];
                $answers = $demographicData['answers'];
                
                // Get the demographic question for the snapshot
                $demographic = Demographic::find($demographicId);
                if (!$demographic) {
                    continue;
                }
                
                // Create question snapshot
                $questionSnapshot = [
                    'id' => $demographic->id,
                    'question' => $demographic->question,
                    'type' => $demographic->type,
                    'required' => $demographic->required,
                    'description' => $demographic->description,
                    'options' => $demographic->options,
                    'captured_at' => now()->toISOString()
                ];
                
                // Find or create student demographic record
                $studentDemographic = StudentDemographic::firstOrCreate([
                    'student_guid' => $student->guid,
                    'demographic_id' => $demographicId,
                ], [
                    'question_snapshot' => json_encode($questionSnapshot),
                    'type' => $demographic->type,
                    'answered_at' => now(),
                ]);
                
                // Update the record if it already existed (in case question changed)
                if ($studentDemographic->wasRecentlyCreated === false) {
                    $studentDemographic->update([
                        'question_snapshot' => json_encode($questionSnapshot),
                        'type' => $demographic->type,
                        'answered_at' => now(),
                    ]);
                }
                
                // Delete existing answers for this demographic
                StudentDemographicAnswer::where('student_demographic_id', $studentDemographic->id)->delete();
                
                // Create new answers
                foreach ($answers as $answerValue) {
                    if (!empty($answerValue)) {
                        // For select/radio/checkbox types, find the corresponding option label
                        $labelSnapshot = $answerValue; // Default to the value itself
                        
                        if (in_array($demographic->type, ['select', 'radio', 'checkbox', 'multi-select'])) {
                            // Try to find the option that matches this value
                            $matchingOption = $demographic->options->firstWhere('value', $answerValue) 
                                           ?? $demographic->options->firstWhere('label', $answerValue);
                            
                            if ($matchingOption) {
                                $labelSnapshot = $matchingOption->label;
                            }
                        }
                        
                        StudentDemographicAnswer::create([
                            'student_demographic_id' => $studentDemographic->id,
                            'value' => $answerValue,
                            'label_snapshot' => $labelSnapshot,
                        ]);
                    }
                }
            }
        });
    }
}
