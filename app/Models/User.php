<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Crypt;

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

    // Encrypt sensitive fields before saving to the database
    // public function setFirstNameAttribute($value)
    // {
    //     $this->attributes['first_name'] = Crypt::encrypt($value);
    // }

    // public function setLastNameAttribute($value)
    // {
    //     $this->attributes['last_name'] = Crypt::encrypt($value);
    // }

    // public function setEmailAttribute($value)
    // {
    //     $this->attributes['email'] = Crypt::encrypt($value);
    // }

    // Decrypt sensitive fields when retrieving from the database
    // public function getFirstNameAttribute($value)
    // {
    //     return Crypt::decrypt($value);
    // }

    // public function getLastNameAttribute($value)
    // {
    //     return Crypt::decrypt($value);
    // }

    // public function getEmailAttribute($value)
    // {
    //     return Crypt::decrypt($value);
    // }

}
