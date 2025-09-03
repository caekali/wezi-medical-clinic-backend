<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorAvailability;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoctorAvailabilityController extends Controller
{
    public function index(Doctor $doctor)
    {
        return response()->json($doctor->availabilities);
    }

    public function show(DoctorAvailability $availability)
    {
        return response()->json($availability);
    }

    public function store(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'day_of_week' => ['required', Rule::in(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'])],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:1',
        ]);

        if ($this->hasConflict($doctor->id, $validated['day_of_week'], $validated['start_time'], $validated['end_time'])) {
            return response()->json([
                'message' => 'This availability conflicts with an existing slot.'
            ], 422);
        }

        $availability = $doctor->availabilities()->create($validated);

        return response()->json($availability, 201);
    }

    public function update(Request $request, DoctorAvailability $availability)
    {
        $validated = $request->validate([
            'day_of_week' => ['sometimes', Rule::in(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'])],
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'slot_duration' => 'sometimes|integer|min:1',
            'is_available' => 'sometimes'
        ]);

        $day = $validated['day_of_week'] ?? $availability->day_of_week;
        $start = $validated['start_time'] ?? $availability->start_time;
        $end = $validated['end_time'] ?? $availability->end_time;

        if ($this->hasConflict($availability->doctor_id, $day, $start, $end, $availability->id)) {
            return response()->json([
                'message' => 'This availability conflicts with an existing slot.'
            ], 422);
        }

        $availability->update($validated);

        return response()->json($availability);
    }

    public function destroy(DoctorAvailability $availability)
    {
        $availability->delete();
        return response()->json(['message' => 'Availability deleted successfully']);
    }

    private function hasConflict($doctorId, $dayOfWeek, $startTime, $endTime, $excludeId = null)
    {
        $query = DoctorAvailability::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
