<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
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
    public function index(Request $request)
    {
        $query = Vehicle::query();

        // Only show approved vehicles for public view
        $query->where('is_approved', true);

        // Filter by vehicle type
        if ($request->has('vehicle_type_id') && $request->vehicle_type_id) {
            $query->where('vehicle_type_id', $request->vehicle_type_id);
        }

        // Search by registration number - with case-insensitive search
        if ($request->has('search') && $request->search) {
            // Use LOWER() function for case-insensitive search
            $searchTerm = $request->search;
            $query->whereRaw('LOWER(registration_number) LIKE ?', ['%' . strtolower($searchTerm) . '%']);

            // Other fields:
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(registration_number) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(manufacturer) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(model) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
            });
        }

        // Sort results
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['created_at', 'manufacturer', 'model', 'registration_number'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Paginate results
        $perPage = $request->input('per_page', 10);
        $vehicles = $query->paginate($perPage);

        return response()->json($vehicles);
    }

    /**
     * Store a newly created vehicle in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // First validate all the required fields
            $validator = Validator::make($request->all(), [
                'registration_number' => 'required|string|max:255|unique:vehicles',
                'manufacturer' => 'required|string|max:255',
                'model' => 'required|string|max:255',
                'engine_capacity' => 'required|numeric|min:0',
                'seats' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'expected_fields' => [
                        'registration_number' => 'A unique vehicle registration/license number',
                        'manufacturer' => 'Vehicle manufacturer name',
                        'model' => 'Vehicle model name',
                        'engine_capacity' => 'Engine capacity in liters',
                        'seats' => 'Number of seats',
                        'vehicle_type' => 'Vehicle type name (Motorcycle, Car, Pickup Truck) or ID',
                    ]
                ], 422);
            }

            // Process vehicle type (accept either ID or name)
            $vehicleTypeId = null;
            $vehicleType = null;

            if ($request->has('vehicle_type_id')) {
                // If ID is provided directly
                $vehicleTypeId = $request->vehicle_type_id;
                $vehicleType = VehicleType::find($vehicleTypeId);
            } elseif ($request->has('vehicle_type')) {
                if (is_numeric($request->vehicle_type)) {
                    // If numeric type is provided
                    $vehicleTypeId = $request->vehicle_type;
                    $vehicleType = VehicleType::find($vehicleTypeId);
                } else {
                    // If name is provided
                    $vehicleType = VehicleType::where('name', $request->vehicle_type)->first();
                    $vehicleTypeId = $vehicleType ? $vehicleType->id : null;
                }
            }

            // Validate that we got a valid type
            if (!$vehicleType) {
                return response()->json([
                    'message' => 'Invalid vehicle type',
                    'valid_types' => VehicleType::pluck('name')->toArray(),
                    'usage' => 'Provide either "vehicle_type_id" or "vehicle_type" field'
                ], 422);
            }

            // Validate type-specific fields
            $typeSpecificValidator = null;

            switch ($vehicleType->name) {
                case 'Motorcycle':
                    $typeSpecificValidator = Validator::make($request->all(), [
                        'seat_height' => 'required|numeric|min:0',
                    ]);
                    break;
                case 'Car':
                    $typeSpecificValidator = Validator::make($request->all(), [
                        'cargo_capacity' => 'required|numeric|min:0',
                    ]);
                    break;
                case 'Pickup Truck':
                    $typeSpecificValidator = Validator::make($request->all(), [
                        'tonnage' => 'required|numeric|min:0',
                    ]);
                    break;
            }

            if ($typeSpecificValidator && $typeSpecificValidator->fails()) {
                return response()->json([
                    'message' => 'Vehicle type-specific validation failed',
                    'errors' => $typeSpecificValidator->errors(),
                    'vehicle_type' => $vehicleType->name,
                    'required_fields' => $vehicleType->name === 'Motorcycle' ? ['seat_height'] : ($vehicleType->name === 'Car' ? ['cargo_capacity'] : ['tonnage'])
                ], 422);
            }

            // Create the vehicle
            $vehicle = new Vehicle();
            $vehicle->registration_number = $request->registration_number;
            $vehicle->manufacturer = $request->manufacturer;
            $vehicle->model = $request->model;
            $vehicle->engine_capacity = $request->engine_capacity;
            $vehicle->seats = $request->seats;
            $vehicle->vehicle_type_id = $vehicleTypeId;
            $vehicle->user_id = Auth::id();
            $vehicle->is_approved = false;

            // Add type-specific fields
            switch ($vehicleType->name) {
                case 'Motorcycle':
                    $vehicle->seat_height = $request->seat_height;
                    break;
                case 'Car':
                    $vehicle->cargo_capacity = $request->cargo_capacity;
                    break;
                case 'Pickup Truck':
                    $vehicle->tonnage = $request->tonnage;
                    break;
            }

            $vehicle->save();

            return response()->json([
                'message' => 'Vehicle created successfully',
                'vehicle' => $vehicle
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating vehicle',
                'error' => $e->getMessage()
            ], 500);
        }
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

        // Check if user is authenticated
        if (Auth::check()) {
            // Only authenticated users can see unapproved vehicles
            if (!$vehicle->is_approved && $vehicle->user_id !== Auth::id() && !Auth::user()->is_admin) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } else {
            // For public access, only show approved vehicles
            if (!$vehicle->is_approved) {
                return response()->json(['message' => 'Vehicle not found'], 404);
            }
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
        try {
            $vehicle = Vehicle::findOrFail($id);

            // Check if the user is authorized to update this vehicle
            if ($vehicle->user_id !== Auth::id() && !Auth::user()->is_admin) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // First validate all the required fields
            $validator = Validator::make($request->all(), [
                'registration_number' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('vehicles')->ignore($id),
                ],
                'manufacturer' => 'required|string|max:255',
                'model' => 'required|string|max:255',
                'engine_capacity' => 'required|numeric|min:0',
                'seats' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'expected_fields' => [
                        'registration_number' => 'A unique vehicle registration/license number',
                        'manufacturer' => 'Vehicle manufacturer name',
                        'model' => 'Vehicle model name',
                        'engine_capacity' => 'Engine capacity in liters',
                        'seats' => 'Number of seats',
                        'vehicle_type' => 'Vehicle type name (Motorcycle, Car, Pickup Truck) or ID',
                    ]
                ], 422);
            }

            // Process vehicle type (accept either ID or name)
            $vehicleTypeId = null;
            $vehicleType = null;

            if ($request->has('vehicle_type_id')) {
                // If ID is provided directly
                $vehicleTypeId = $request->vehicle_type_id;
                $vehicleType = VehicleType::find($vehicleTypeId);
            } elseif ($request->has('vehicle_type')) {
                if (is_numeric($request->vehicle_type)) {
                    // If numeric type is provided
                    $vehicleTypeId = $request->vehicle_type;
                    $vehicleType = VehicleType::find($vehicleTypeId);
                } else {
                    // If name is provided
                    $vehicleType = VehicleType::where('name', $request->vehicle_type)->first();
                    $vehicleTypeId = $vehicleType ? $vehicleType->id : null;
                }
            } else {
                // If no type provided, keep existing
                $vehicleTypeId = $vehicle->vehicle_type_id;
                $vehicleType = VehicleType::find($vehicleTypeId);
            }

            // Validate that we got a valid type
            if (!$vehicleType) {
                return response()->json([
                    'message' => 'Invalid vehicle type',
                    'valid_types' => VehicleType::pluck('name')->toArray(),
                    'usage' => 'Provide either "vehicle_type_id" or "vehicle_type" field'
                ], 422);
            }

            // Check if vehicle type changed
            $typeChanged = $vehicle->vehicle_type_id !== $vehicleTypeId;

            // Validate type-specific fields if type changed
            if ($typeChanged) {
                $typeSpecificValidator = null;

                switch ($vehicleType->name) {
                    case 'Motorcycle':
                        $typeSpecificValidator = Validator::make($request->all(), [
                            'seat_height' => 'required|numeric|min:0',
                        ]);
                        break;
                    case 'Car':
                        $typeSpecificValidator = Validator::make($request->all(), [
                            'cargo_capacity' => 'required|numeric|min:0',
                        ]);
                        break;
                    case 'Pickup Truck':
                        $typeSpecificValidator = Validator::make($request->all(), [
                            'tonnage' => 'required|numeric|min:0',
                        ]);
                        break;
                }

                if ($typeSpecificValidator && $typeSpecificValidator->fails()) {
                    return response()->json([
                        'message' => 'Vehicle type-specific validation failed',
                        'errors' => $typeSpecificValidator->errors(),
                        'vehicle_type' => $vehicleType->name,
                        'required_fields' => $vehicleType->name === 'Motorcycle' ? ['seat_height'] : ($vehicleType->name === 'Car' ? ['cargo_capacity'] : ['tonnage'])
                    ], 422);
                }
            }

            // Update the vehicle
            $vehicle->registration_number = $request->registration_number;
            $vehicle->manufacturer = $request->manufacturer;
            $vehicle->model = $request->model;
            $vehicle->engine_capacity = $request->engine_capacity;
            $vehicle->seats = $request->seats;
            $vehicle->vehicle_type_id = $vehicleTypeId;
            $vehicle->is_approved = false; // Reset approval status on update

            // Update type-specific fields
            if ($typeChanged) {
                // Reset all type-specific fields
                $vehicle->seat_height = null;
                $vehicle->cargo_capacity = null;
                $vehicle->tonnage = null;

                // Set only the relevant field for the new type
                switch ($vehicleType->name) {
                    case 'Motorcycle':
                        $vehicle->seat_height = $request->seat_height;
                        break;
                    case 'Car':
                        $vehicle->cargo_capacity = $request->cargo_capacity;
                        break;
                    case 'Pickup Truck':
                        $vehicle->tonnage = $request->tonnage;
                        break;
                }
            } else {
                // If type didn't change, just update the relevant field if provided
                switch ($vehicleType->name) {
                    case 'Motorcycle':
                        if ($request->has('seat_height')) {
                            $vehicle->seat_height = $request->seat_height;
                        }
                        break;
                    case 'Car':
                        if ($request->has('cargo_capacity')) {
                            $vehicle->cargo_capacity = $request->cargo_capacity;
                        }
                        break;
                    case 'Pickup Truck':
                        if ($request->has('tonnage')) {
                            $vehicle->tonnage = $request->tonnage;
                        }
                        break;
                }
            }

            $vehicle->save();

            if (!$vehicle->is_approved) {
                return response()->json([
                    'message' => 'Vehicle updated successfully. The vehicle will need to be approved by an administrator before it appears in the public listing.',
                    'vehicle' => $vehicle,
                    'approval_status' => 'pending'
                ]);
            } else {
                return response()->json([
                    'message' => 'Vehicle updated successfully. Your changes have reset the approval status and the vehicle will need to be re-approved.',
                    'vehicle' => $vehicle,
                    'approval_status' => 'pending'
                ]);
            }

            return response()->json([
                'message' => 'Vehicle updated successfully',
                'vehicle' => $vehicle
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating vehicle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of unapproved vehicles.
     *
     * @return \Illuminate\Http\Response
     */
    public function unapproved(Request $request)
    {
        $query = Vehicle::where('is_approved', false);

        // For regular users, only show their own unapproved vehicles
        if (!Auth::user()->is_admin) {
            $query->where('user_id', Auth::id());
        }

        // Apply the same sorting and filtering options as the main index method
        if ($request->has('vehicle_type_id') && $request->vehicle_type_id) {
            $query->where('vehicle_type_id', $request->vehicle_type_id);
        }

        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(registration_number) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(manufacturer) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(model) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
            });
        }

        // Sort results
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSortFields = ['created_at', 'manufacturer', 'model', 'registration_number'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Paginate results
        $perPage = $request->input('per_page', 10);
        $vehicles = $query->paginate($perPage);

        return response()->json($vehicles);
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
}
