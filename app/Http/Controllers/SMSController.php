<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AfricaTalkingService;
use App\Services\SmsService;

class SMSController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function send(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $this->smsService->sendSMS($request->phone, $request->message);

        return response()->json($result);
    }
}
