<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    use HasFactory;
    protected $fillable = [
        'email', 'otp', 'expiration', 'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public static function findByOtp($otp)
    {
        return self::where('otp', $otp)->first();
    }
}
