<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierData extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',  // Add this to allow mass assignment
        'phone',
        'called_at',
        'data',
        'user_agent',
        'refer',
        'type',
        'ip'
    ];

    // Define the relationship with the User model (if needed)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
