<?php

namespace App\Http\Controllers;

use App\Models\VehicleType;

class VehicleTypeController extends Controller
{
    public function index()
    {
        try {
            $vehicleTypes = VehicleType::all();

            return response()->json([
                'success' => true,
                'data' => $vehicleTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving vehicle types',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
