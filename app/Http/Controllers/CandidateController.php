<?php

namespace App\Http\Controllers;

use App\Services\CandidateService;
use Illuminate\Http\RedirectResponse;

class CandidateController extends Controller
{
    public function dashboard(): RedirectResponse
    {
        return redirect('/candidate/dashboard');
    }
}