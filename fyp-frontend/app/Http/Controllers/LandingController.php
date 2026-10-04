<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class LandingController extends Controller
{
    public function index()
    {
        // If student is already logged in, send to the dashboard
        if (session()->has('student_id')) {
            return redirect()->route('home');
        }

        return view('landing');
    }
}
