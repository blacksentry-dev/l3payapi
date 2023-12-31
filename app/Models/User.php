<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'password',
        'username',
        'address',
        'transaction_pin',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function passwordResetTokens()
    {
        return $this->hasMany(PasswordResetToken::class, 'email', 'email');
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
