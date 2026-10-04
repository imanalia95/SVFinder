<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * AuthController
 *
 *   GET  /signup          - show sign-up form
 *   POST /signup          - validate + create student + login + redirect
 *   GET  /login           - show login form
 *   POST /login           - validate + attempt login + redirect
 *   POST /logout          - destroy session + redirect to login
 */
class AuthController extends Controller
{
    // ══════════════════════════════════════════════════════
    // SIGN UP
    // ══════════════════════════════════════════════════════

    public function showSignup()
    {
        // If already logged in, send straight to dashboard
        if (session()->has('student_id')) {
            return redirect()->route('home');
        }
        return view('auth.signup');
    }

    public function signup(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
            'matric_number' => [
                'required',
                'digits_between:5,6',       // 5 or 6 digits only
                'unique:students,matric_number',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:students,email',
            ],
            'password' => [
                'required',
                'confirmed',                // needs password_confirmation field
                Password::min(8)
                    ->letters()
                    ->numbers(),
            ],
        ], [
            // Custom error messages
            'matric_number.digits_between' => 'Matric number must be 5 or 6 digits.',
            'matric_number.unique'         => 'This matric number is already registered.',
            'email.unique'                 => 'This email is already registered.',
            'password.confirmed'           => 'Password confirmation does not match.',
        ]);

        // Create student — password is auto-hashed via model $casts
        $student = Student::create([
            'name'          => $validated['name'],
            'matric_number' => $validated['matric_number'],
            'email'         => $validated['email'],
            'password'      => $validated['password'],
        ]);

        // Log in immediately after sign-up
        session([
            'student_id'   => $student->id,
            'student_name' => $student->name,
            'student_matric' => $student->matric_number,
        ]);

        return redirect()->route('home')
            ->with('success', 'Welcome, ' . $student->name . '! Your account has been created.');
    }


    // ══════════════════════════════════════════════════════
    // LOG IN
    // ══════════════════════════════════════════════════════

    public function showLogin()
    {
        if (session()->has('student_id')) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'matric_number' => ['required', 'digits_between:5,6'],
            'password'      => ['required'],
        ], [
            'matric_number.digits_between' => 'Matric number must be 5 or 6 digits.',
        ]);

        // Find student by matric number
        $student = Student::where('matric_number', $validated['matric_number'])->first();

        // Check student extist
        if (!$student) {
        return back()
            ->withInput($request->only('matric_number'))
            ->withErrors(['matric_number' => 'No account found with this matric number.']);
        }

        // Check password separately
        if (!Hash::check($validated['password'], $student->password)) {
            return back()
                ->withInput($request->only('matric_number'))
                ->withErrors(['password' => 'Incorrect password.']);
        }

        // Store in session
        session([
            'student_id'     => $student->id,
            'student_name'   => $student->name,
            'student_matric' => $student->matric_number,
        ]);

        // Respect the "intended" URL if they were redirected from a protected page
        return redirect()->intended(route('home'))
            ->with('success', 'Welcome back, ' . $student->name . '!');
    }


    // ══════════════════════════════════════════════════════
    // LOG OUT
    // ══════════════════════════════════════════════════════

    public function logout(Request $request)
    {
        $request->session()->forget(['student_id', 'student_name', 'student_matric']);
        $request->session()->regenerate();

        return redirect()->route('landing')
            ->with('success', 'You have been logged out.');
    }
}