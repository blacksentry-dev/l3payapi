<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    use HasFactory;
    protected $fillable = [
        'email', 'token',
    ];
    public static function findByOtp($otp)
    {
        return self::where('otp', $otp)->first();
    }
}
