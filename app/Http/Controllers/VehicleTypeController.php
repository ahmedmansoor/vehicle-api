<?php

namespace App\Http\Controllers;

use App\Models\VehicleType;

class VehicleTypeController extends Controller
{
    public function index()
    {
        $vehicleTypes = VehicleType::all();
        return response()->json($vehicleTypes);
    }
}
