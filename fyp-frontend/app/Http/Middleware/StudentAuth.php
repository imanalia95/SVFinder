<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * StudentAuth middleware
 *
 * Applied to all routes that require a logged-in student.
 * Checks for 'student_id' in the session.
 * If missing → redirect to /login with a message.
 *
 */
class StudentAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('student_id')) {
            // If request expects JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Log in as a UNIMAS student to continue.'
                ], 403);
            }

            // If coming from a protected action (like email button)
            if ($request->routeIs('email.lecturer')) {
                return redirect()->back()
                    ->with('error', 'Log in as a UNIMAS student to contact lecturers.');
            }

                return redirect()->route('login')
                    ->with('error', 'Please log in to access the supervisor recommender.');
        }

        return $next($request);
    }
}
