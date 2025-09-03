<?php
namespace App\Services;

use App\Models\Appointment;
use AfricasTalking\SDK\AfricasTalking;
use Carbon\Carbon;

class AppointmentService
{
    public function createAppointment(string $fullName, string $phone, array $service, array $doctor, string $date): void
    {
        // Appointment::create([
        //     'full_name' => $fullName,
        //     'phone_number' => $phone,
        //     'service' => $service['name'],
        //     'doctor' => $doctor['name'],
        //     'appointment_date' => $date
        // ]);
        $this->sendSms($phone, $fullName, $service['name'], $doctor['name'], $date);
    }

    private function sendSms(string $phone, string $name, string $service, string $doctor, string $date): void
    {
        $username = env('AFRICASTALKING_USERNAME');
        $apiKey = env('AFRICASTALKING_API_KEY');
        $AT = new AfricasTalking($username, $apiKey);
        $sms = $AT->sms();

        $msg = "Thank you $name! Appointment for $service with $doctor on $date booked.";

        try {
            $sms->send(['to' => $phone, 'message' => $msg]);
        } catch (\Exception $e) {
            \Log::error('USSD SMS error: ' . $e->getMessage());
        }
    }

    public function validateDate(string $date): bool
    {
        try {
            $appointmentDate = Carbon::createFromFormat('Y-m-d', $date);
            return !$appointmentDate->isPast();
        } catch (\Exception $e) {
            return false;
        }
    }
}
