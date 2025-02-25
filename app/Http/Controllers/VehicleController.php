<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with('vehicleType', 'user')
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc');

        // Filter by vehicle type
        if ($request->has('vehicle_type_id')) {
            $query->where('vehicle_type_id', $request->vehicle_type_id);
        }

        // Search by registration number
        if ($request->has('registration_number')) {
            $query->where('registration_number', 'like', '%' . $request->registration_number . '%');
        }

        // Paginate results (default 10 per page)
        $perPage = $request->per_page ?? 10;
        $vehicles = $query->paginate($perPage);

        return response()->json($vehicles);
    }

    public function store(Request $request)
    {
        $validated = $this->validateVehicle($request);

        $validated['user_id'] = Auth::id();
        $validated['is_approved'] = false;

        $vehicle = Vehicle::create($validated);

        return response()->json($vehicle, 201);
    }

    public function show(Vehicle $vehicle)
    {
        if (!$vehicle->is_approved) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }

        return response()->json($vehicle->load('vehicleType', 'user'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        // Check if user owns the vehicle
        if (Auth::id() !== $vehicle->user_id && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $this->validateVehicle($request, $vehicle->id);

        $vehicle->update($validated);

        return response()->json($vehicle);
    }

    public function destroy(Vehicle $vehicle)
    {
        // Check if user owns the vehicle
        if (Auth::id() !== $vehicle->user_id && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted successfully']);
    }

    public function approve(Vehicle $vehicle)
    {
        // Only admins can approve vehicles
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $vehicle->update(['is_approved' => true]);

        return response()->json($vehicle);
    }

    private function validateVehicle(Request $request, $vehicleId = null)
    {
        $rules = [
            'registration_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vehicles')->ignore($vehicleId),
            ],
            'manufacturer' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'engine_capacity' => 'required|numeric|min:0',
            'seats' => 'required|integer|min:1',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
        ];

        // Add type-specific validation rules
        if ($request->vehicle_type_id) {
            $vehicleType = VehicleType::find($request->vehicle_type_id);

            if ($vehicleType) {
                switch ($vehicleType->name) {
                    case 'Motorcycle':
                        $rules['seat_height'] = 'required|numeric|min:0';
                        break;
                    case 'Car':
                        $rules['cargo_capacity'] = 'required|numeric|min:0';
                        break;
                    case 'Pickup Truck':
                        $rules['tonnage'] = 'required|numeric|min:0';
                        break;
                }
            }
        }

        return $request->validate($rules);
    }
}
