<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'manufacturer',
        'model',
        'engine_capacity',
        'seats',
        'seat_height',
        'cargo_capacity',
        'tonnage',
        'is_approved',
        'vehicle_type_id',
        'user_id',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
