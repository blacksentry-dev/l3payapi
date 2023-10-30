<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'payment_methods';

    // Define relationships here, e.g., to the User model.
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
