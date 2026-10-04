<?php
 
namespace App\Http\Controllers;
 
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
 
class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Verify matric number + email, then redirect to reset form.
     */
    public function verifyIdentity(Request $request)
    {
        $request->validate([
            'matric_number' => ['required', 'digits_between:1,7'],
            'email'         => ['required', 'email'],
        ]);

        $user = Student::where('matric_number', $request->matric_number)
                    ->where('email', $request->email)
                    ->first();

        if (! $user) {
            return back()
                ->withInput()
                ->with('error', 'No account found with that matric number and email.');
        }

        // Generate a short-lived token stored in cache
        $token = Str::random(64);
        Cache::put('password_reset_' . $token, $user->id, now()->addMinutes(15));

        return redirect()->route('password.reset.form', ['token' => $token]);
    }

    /**
     * Show the reset password form (requires a valid token).
     */
    public function showResetForm(string $token)
    {
        // Bail early if the token doesn't exist in cache
        if(! Cache::has('password_reset_' . $token)) {
            return redirect()->route('password.forgot')
                ->with('error', 'This reset link has expired or is invalid. Please try again.');
        }

        return view('auth.reset-password', compact('token'));
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $userId = Cache::get('password_reset_' . $request->token);

        if (! $userId) {
            return redirect()->route('password.forgot')
                ->with('error', 'This reset link has expired or is invalid. Please try again.');
        }

        $user = Student::findOrFail($userId);
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Invalidate the token immediately after use
        Cache::forget('password_reset_' . $request->token);

        return redirect()->route('login')
            ->with('success', 'Password reset successfully. You can now log in.');
    }
}
