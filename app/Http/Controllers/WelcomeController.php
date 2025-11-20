<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    /**
     * Display the public welcome page.
     */
    public function index(): Response
    {
        // TODO: Adjust welcome page as needed
        return Inertia::render('Welcome');
    }
}
