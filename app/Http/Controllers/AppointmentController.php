<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{

    public function index()
    {
        $appointments = Appointment::all();
        return response()->json($appointments);
    }


    public function store(Request $request, SmsService $smsService)
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


        // Generate unique booking ID
        $bookingId = 'BK' . time() . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $validatedData['booking_id'] = $bookingId;

        $appointment = Appointment::create($validatedData);

        $service = Service::find($appointment->service_id);
        $price = $service ? number_format($service->price, 2) : 'N/A';

        $message = "Wezi Medical Centre: Hello {$appointment->patient_name}, your appointment is confirmed.\n"
            . "Booking ID: {$bookingId}\n"
            . "Service: {$service->name} (Price: MWK {$price})\n"
            . "Date: {$appointment->appointment_date}\n"
            . "Time: {$appointment->appointment_time}\n"
            . "Thank you for choosing Wezi Medical Centre.";

        try {
            $smsService->sendSms($appointment->phone_number, $message);
        } catch (\Exception $e) {
            \Log::error("SMS sending failed: " . $e->getMessage());
        }
        return response()->json($appointment, 201);
    }


    public function show(Appointment $appointment)
    {
        return response()->json($appointment);
    }



    public function update(Request $request, Appointment $appointment, SmsService $smsService)
    {
        // Validate the incoming request data 
        $validatedData = $request->validate([
            'phone_number'      => 'sometimes|string|max:255',
            'patient_name'      => 'sometimes|string|max:255',
            'service_id'        => 'sometimes|exists:services,id',
            'appointment_date'  => 'sometimes|date|after_or_equal:today',
            'appointment_time'  => 'sometimes|date_format:H:i',
            'status'            => 'sometimes|string',
        ]);

        // Track old status before update
        $oldStatus = $appointment->status;

        // Update appointment
        $appointment->update($validatedData);

        // If status changed to "rescheduled", send SMS
        if (
            isset($validatedData['status']) &&
            $validatedData['status'] === 'rescheduled' &&
            $oldStatus !== 'rescheduled'
        ) {
            $service = Service::find($appointment->service_id);
            $price   = $service?->price ?? '0';

            $message = "Wezi Medical Centre: Dear {$appointment->patient_name}, "
                . "your appointment has been RESCHEDULED.\n"
                . "Booking ID: {$appointment->booking_id}\n"
                . "Service: {$service->name} (MWK {$price})\n"
                . "New Date: {$appointment->appointment_date}\n"
                . "New Time: {$appointment->appointment_time}\n"
                . "Thank you for trusting Wezi Medical Centre.";
            try {
                $smsService->sendSms($appointment->phone_number, $message);
            } catch (\Exception $e) {
                Log::error("SMS sending failed: " . $e->getMessage());
            }
            return response()->json($appointment, 201);
        }

        // Return updated appointment
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
