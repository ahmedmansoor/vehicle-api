<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Display a listing of the vehicles.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->is_admin) {
            // Admins can see all vehicles
            $vehicles = Vehicle::all();
        } else {
            // Regular users can only see their own vehicles
            $vehicles = Vehicle::where('user_id', Auth::id())->get();
        }

        return response()->json(['vehicles' => $vehicles]);
    }

    /**
     * Store a newly created vehicle in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'license_plate' => 'required|string|max:20|unique:vehicles',
        ]);

        $vehicle = new Vehicle();
        $vehicle->make = $request->make;
        $vehicle->model = $request->model;
        $vehicle->year = $request->year;
        $vehicle->license_plate = $request->license_plate;
        $vehicle->user_id = Auth::id();
        $vehicle->is_approved = false;
        $vehicle->save();

        return response()->json([
            'message' => 'Vehicle created successfully',
            'vehicle' => $vehicle
        ], 201);
    }

    /**
     * Display the specified vehicle.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Check if the user is authorized to view this vehicle
        if ($vehicle->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['vehicle' => $vehicle]);
    }

    /**
     * Update the specified vehicle in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Check if the user is authorized to update this vehicle
        if ($vehicle->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate,' . $id,
        ]);

        $vehicle->make = $request->make;
        $vehicle->model = $request->model;
        $vehicle->year = $request->year;
        $vehicle->license_plate = $request->license_plate;
        $vehicle->is_approved = false; // Reset approval status on update
        $vehicle->save();

        return response()->json([
            'message' => 'Vehicle updated successfully',
            'vehicle' => $vehicle
        ]);
    }

    /**
     * Remove the specified vehicle from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Check if the user is authorized to delete this vehicle
        if ($vehicle->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted successfully']);
    }

    /**
     * Approve a vehicle (admin only).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    {
        // Check if the user is an admin
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $vehicle = Vehicle::findOrFail($id);
        $vehicle->is_approved = true;
        $vehicle->save();

        return response()->json([
            'message' => 'Vehicle approved successfully',
            'vehicle' => $vehicle
        ]);
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
