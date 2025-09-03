<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;

class SmsService
{
    protected $sms;

    public function __construct()
    {
        $username = env('AFRICASTALKING_USERNAME');
        $apiKey = env('AFRICASTALKING_API_KEY');

        $AT = new AfricasTalking($username, $apiKey);

        $this->sms = $AT->sms();
    }

    public function sendSMS(string $to, string $message)
    {
        try {
            $result = $this->sms->send([
                'to' => $to,
                'message' => $message,
            ]);

            return $result;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
