<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DTOs\UssdInput;
use App\Services\ussd\UssdMenuService;
use App\Services\AppointmentService;
use App\Services\ussd\UssdSessionService;

class UssdController extends Controller
{
    private UssdSessionService $sessionService;
    private UssdMenuService $menuService;
    private AppointmentService $appointmentService;

    private array $languages = ['1' => 'en', '2' => 'ny'];
    private array $messages;
    private array $services;

    public function __construct(
        UssdSessionService $sessionService,
        UssdMenuService $menuService,
        AppointmentService $appointmentService
    ) {
        $this->sessionService = $sessionService;
        $this->menuService = $menuService;
        $this->appointmentService = $appointmentService;
        $this->messages = ['en' => ['welcome' => "Welcome to Wezi Clinic", 'choose_language' => "Choose Language:\n1. English\n2. Chichewa", 'main_menu' => "Menu:\n1. Book Appointment\n2. Find Your Way\n3. Chat with Us", 'enter_name' => "Please enter your Full Name:", 'select_service' => "Select a service:", 'select_doctor' => "Select a doctor:", 'enter_date' => "Enter appointment date (YYYY-MM-DD):", 'invalid_choice' => "Invalid choice, try again.", 'invalid_date' => "Invalid date. Please enter today or a future date.", 'invalid_format' => "Invalid date format. Use YYYY-MM-DD.", 'appointment_confirmed' => "Thank you {name}! Appointment for {service} with {doctor} on {date} booked. SMS confirmation sent.", 'find_way' => "Wezi Clinic is located along Main Street, Blantyre.", 'chat_support' => "For support, call 265-999-123-456", 'back' => "Back to Main Menu", 'next' => "Next"], 'ny' => ['welcome' => "Takulandirani ku Wezi Clinic", 'choose_language' => "Sankhani Chikhalidwe:\n1. Chingerezi\n2. Chichewa", 'main_menu' => "Menyu:\n1. Kukonzekera Appointment\n2. Kupeza Mapaulani\n3. Lankhula na Ife", 'enter_name' => "Chonde lowetsani dzina lanu lonse:", 'select_service' => "Sankhani utumiki:", 'select_doctor' => "Sankhani dokotala:", 'enter_date' => "Lowetsani tsiku la appointment (YYYY-MM-DD):", 'invalid_choice' => "Sankho losaloledwa, yesani kachiwiri.", 'invalid_date' => "Tsiku silili bwino. Chonde lowetsani lero kapena mtsogolo.", 'invalid_format' => "Format ya tsiku si bwino. Gwiritsani YYYY-MM-DD.", 'appointment_confirmed' => "Zikomo {name}! Appointment ya {service} ndi {doctor} pa {date} yatsimikizika. SMS yatumizidwa.", 'find_way' => "Wezi Clinic ili pa Main Street, Blantyre.", 'chat_support' => "Kuti mulandire thandizo, foni 265-999-123-456", 'back' => "Bwerera ku Main Menu", 'next' => "Zotsatirazi"]];
        $this->services = [['id' => 1, 'name' => 'General Consultation', 'doctors' => [['id' => 1, 'name' => 'Dr. Banda'], ['id' => 2, 'name' => 'Dr. Phiri'],],], ['id' => 2, 'name' => 'Dental Care', 'doctors' => [['id' => 3, 'name' => 'Dr. Chirwa'], ['id' => 4, 'name' => 'Dr. Kumwenda'],],], ['id' => 3, 'name' => 'Pediatrics', 'doctors' => [['id' => 5, 'name' => 'Dr. Mwale'], ['id' => 6, 'name' => 'Dr. Moyo'],],], ['id' => 4, 'name' => 'Laboratory', 'doctors' => [['id' => 7, 'name' => 'Dr. Tembo'],],],];
    }

    public function handle(Request $request)
    {
        $input = new UssdInput(
            $request->input('sessionId'),
            $request->input('phoneNumber'),
            $request->input('text', '')
        );

        // handle instant Main Menu
        if ($input->lastInput() === '00') {
            $this->sessionService->forget($input->sessionId, [
                'stack',
                'full_name',
                'selected_service',
                'selected_doctor',
                'service_page',
                'doctor_page',
                'lang'
            ]);
            return response("CON " . $this->messages['en']['main_menu']);
        }

        // one step back
        if ($input->lastInput() === '0') {
            $inputArray = $this->sessionService->pop($input->sessionId);
            $input->inputArray = $inputArray;
        } else {
            $this->sessionService->push($input->sessionId, $input->inputArray);
        }

        $level = $input->level();

        // langauge selection
        if ($level === 0 || !isset($input->inputArray[0]) || !in_array($input->inputArray[0], ['1', '2'])) {
            return response("CON " . $this->messages['en']['choose_language']);
        }

        $lang = $this->languages[$input->inputArray[0]];
        $this->sessionService->set($input->sessionId, 'lang', $lang);

        // main menu
        if ($level === 1) return response("CON " . $this->messages[$lang]['main_menu']);

        // book appointment
        if ($input->inputArray[1] == '1') {
            return $this->handleAppointmentFlow($input, $lang);
        }

        // find your way
        if ($input->inputArray[1] == '2') return response("END " . $this->messages[$lang]['find_way']);

        // chat support
        if ($input->inputArray[1] == '3') return response("END " . $this->messages[$lang]['chat_support']);

        return response("END " . $this->messages[$lang]['invalid_choice']);
    }

    private function handleAppointmentFlow(UssdInput $input, string $lang)
    {
        $sessionId = $input->sessionId;

        if ($input->level() === 2) return response("CON " . $this->messages[$lang]['enter_name']);

        if ($input->level() === 3) {
            $fullName = $input->inputArray[2];
            $this->sessionService->set($sessionId, 'full_name', $fullName);
            return response($this->menuService->buildMenu($this->services, 'service', $lang, 1, $this->messages[$lang]));
        }

        if ($input->level() === 4) {
            return $this->handlePaginatedSelection($input, 'service', 'selected_service', $lang);
        }

        if ($input->level() === 5) {
            $service = $this->sessionService->get($sessionId, 'selected_service');
            return $this->handlePaginatedSelection($input, 'doctor', 'selected_doctor', $lang, $service['doctors']);
        }

        if ($input->level() === 6) {
            $dateInput = $input->inputArray[5];
            $fullName = $this->sessionService->get($sessionId, 'full_name');
            $service = $this->sessionService->get($sessionId, 'selected_service');
            $doctor = $this->sessionService->get($sessionId, 'selected_doctor');

            if (!$this->appointmentService->validateDate($dateInput)) {
                return response("END " . $this->messages[$lang]['invalid_date']);
            }

            $this->appointmentService->createAppointment($fullName, $input->phone, $service, $doctor, $dateInput);

            $this->sessionService->forget($sessionId, [
                'stack',
                'full_name',
                'selected_service',
                'selected_doctor',
                'service_page',
                'doctor_page',
                'lang'
            ]);

            return response("END " . str_replace(
                ['{name}', '{service}', '{doctor}', '{date}'],
                [$fullName, $service['name'], $doctor['name'], $dateInput],
                $this->messages[$lang]['appointment_confirmed']
            ));
        }
    }

    private function handlePaginatedSelection(UssdInput $input, string $type, string $cacheKey, string $lang, array $items = null)
    {
        $itemsList = $items ?? $this->services;
        $choiceInput = strtolower($input->lastInput());

        $page = $this->sessionService->get($input->sessionId, $type . '_page', 1);

        if ($choiceInput === 'n' && ($page * 3) < count($itemsList)) {
            $page++;
        }

        $this->sessionService->set($input->sessionId, $type . '_page', $page);

        if (is_numeric($choiceInput)) {
            $index = ((int)$choiceInput - 1) + (($page - 1) * 3);
            if (!isset($itemsList[$index])) return response("END " . $this->messages[$lang]['invalid_choice']);

            $this->sessionService->set($input->sessionId, $cacheKey, $itemsList[$index]);

            if ($type === 'service') {
                return response($this->menuService->buildMenu($itemsList[$index]['doctors'], 'doctor', $lang, 1, $this->messages[$lang]));
            }

            if ($type === 'doctor') return response("CON " . $this->messages[$lang]['enter_date']);
        }

        return response($this->menuService->buildMenu($itemsList, $type, $lang, $page, $this->messages[$lang]));
    }
}
