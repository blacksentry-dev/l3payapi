<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = ['otp', 'expiration', 'user_id', 'verified'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function findByOtp($otp)
    {
        return self::where('otp', $otp)->first();
    }

    public function markAsVerified()
    {
        $this->update(['verified' => true]);
    }
}
