<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    
 public function getByDepartment($departmentId)
{
    $doctors = Doctor::where('department_id', $departmentId)
        ->with('department') // eager load department
        ->get()
        ->map(function ($doctor) {
            return [
                'id' => $doctor->id,
                'first_name' => $doctor->user->first_name,
                'last_name' => $doctor->user->last_name,
                'department' => $doctor->department ? $doctor->department->name : null,
                'specialization' => $doctor->specialization,
            ];
        });

    return response()->json($doctors);
}
    
    public function availabilities($doctorId)
    {
        $availabilities = DoctorAvailability::where('doctor_id', $doctorId)->get()->map(function ($availability) {
            return [
            'id' => $availability->id,
            'day_of_week' => $availability->day_of_week,
            'start_time' => date('H:i', strtotime($availability->start_time)),
            'end_time' => date('H:i', strtotime($availability->end_time)),
            'slot_duration' => $availability->slot_duration,
            'is_available' => $availability->is_available,
            ];
        });
        return response()->json($availabilities);


    }

    
    public function storeAvailability(Request $request, $doctorId)
    {
        $validated = $request->validate([
            'day_of_week' => ['required', Rule::in(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'])],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:5',
        ]);

        $availability = DoctorAvailability::create([
            'doctor_id' => $doctorId,
            ...$validated
        ]);

        return response()->json($availability, 201);
    }

    public function updateAvailability(Request $request, $availabilityId)
    {
        $availability = DoctorAvailability::findOrFail($availabilityId);

        $validated = $request->validate([
            'day_of_week' => ['sometimes', Rule::in(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'])],
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'slot_duration' => 'sometimes|integer|min:5',
            'is_available' => 'sometimes',
        ]);

        $availability->update($validated);

        return response()->json($availability);
    }

    public function destroyAvailability($availabilityId)
    {
        $availability = DoctorAvailability::findOrFail($availabilityId);
        $availability->delete();

        return response()->json(['message' => 'Availability deleted successfully.']);
    }
}
