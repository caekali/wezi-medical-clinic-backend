<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{

    public function index()
    {
        $appointments = Appointment::all();
        return response()->json($appointments);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'phone_number' => [
                'required',
                'string',
                'max:20',
            ],
            'patient_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'service_id'    => 'required|exists:services,id',
            'doctor_id'     => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
        ]);


        // new appointent
        $appointment = Appointment::create($validatedData);

        return response()->json($appointment, 201);
    }


    public function show(Appointment $appointment)
    {
        return response()->json($appointment);
    }



    public function update(Request $request, Appointment $appointment)
    {
        // Validate the incoming request data 
        $validatedData = $request->validate([
            'phone_number' => 'sometimes|string|max:255',
            'patient_name' => 'sometimes|string|max:255',
            'service_id' => 'sometimes|exists:services,id',
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
        ]);

        // Update appointment
        $appointment->update($validatedData);

        // Return the updated appointment
        return response()->json($appointment);
    }


    public function destroy(Appointment $appointment)
    {
        // Delete the appointment
        $appointment->delete();

        // Return a success message with a 204 No Content status code
        return response()->json(null, 204);
    }
}
