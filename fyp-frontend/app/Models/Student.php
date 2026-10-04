<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

/**
 * Student model
 *
 * Extends Authenticatable so Laravel's session/auth helpers work out of the box.
 * We use a custom guard ('student') so it does not conflict with any admin/user auth.
 *
 * Login identifier: matric_number  (not email — set in config/auth.php)
 */
class Student extends Authenticatable
{
    use Notifiable;

    protected $table = 'students';

    /**
     * Fields that can be mass-assigned (used in Student::create([...]))
     */
    protected $fillable = [
        'name',
        'matric_number',
        'email',
        'password',
    ];

    /**
     * Fields hidden from JSON serialisation (never leaked to views or API)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Automatic casting
     */
    protected $casts = [
        'password' => 'hashed',   // Laravel 10+: auto-bcrypt on assignment
    ];

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Returns the formatted matric number padded to at least 5 digits.
     * e.g. "12345" → "12345", "123456" → "123456"
     */
    public function formattedMatric(): string
    {
        return str_pad($this->matric_number, 5, '0', STR_PAD_LEFT);
    }
}
