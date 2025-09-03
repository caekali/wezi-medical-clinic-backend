<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $today = now()->toDateString();



        if ($user->role === 'doctor') {
            // === Stats ===
            $todayAppointmentsCount = Appointment::where('doctor_id', $user->id)
                ->whereDate('created_at', $today)
                ->count();

            $totalPatients = Appointment::where('doctor_id', $user->id)
                // ->distinct('user_id')
                ->count('doctor_id');

            $pending = Appointment::where('doctor_id', $user->id)
                ->where('status', 'pending')
                ->count();

            $completed = Appointment::where('doctor_id', $user->id)
                ->where('status', 'completed')
                ->count();

            // $completed = Appointment::where('doctor_id', $user->id)
            //     ->where('status', 'c')
            //     ->count();
            // $completed = Appointment::where('doctor_id', $user->id)
            //     ->where('status', 'completed')
            //     ->count();

            // === Today's Schedule ===
            $todayAppointments = Appointment::with('doctor', 'service')
                ->where('doctor_id', $user->id)
                // ->whereDate('appointment_date', $today)
                ->orderBy('appointment_date', 'asc')
                ->get()
                ->map(function ($appt) {
                    return [
                        'id'              => $appt->id,
                        'patient_name'    => $appt->patient_name,
                        'service_name'    => $appt->service->name ?? 'General Consultation',
                        'appointment_time' => $appt->appointment_time,
                        'appointment_date' => $appt->appointment_date,
                        'status'          => $appt->status,
                    ];
                });


            // === Recent Patients ===
            $recentPatients = Appointment::where('doctor_id', $user->id)
                ->latest() // orders by created_at desc
                ->take(8)
                ->get()
                ->map(function ($appointment) {
                    return [
                        'id'         => $appointment->id,
                        'name'       => $appointment->patient_name,
                        'phone'      => $appointment->phone_number,
                        'service'    => optional($appointment->service)->name, // if relation exists
                        'last_visit' => $appointment->appointment_date,
                        'time'       => $appointment->appointment_time,
                        'status'     => $appointment->status,
                    ];
                });

            return response()->json([
                'todayStats' => [
                    'appointments'   => $todayAppointmentsCount,
                    'totalPatients'  => $totalPatients,
                    'pending'        => $pending,
                    'completed'      => $completed,
                ],
                'todayAppointments' => $todayAppointments,
                'recentPatients'    => $recentPatients,
            ]);
        }

        return response()->json([
            'message' => 'Dashboard not available for this role'
        ], 403);
    }
}
