<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Room;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function welcome()
    {
        return view('welcome', ['stats' => $this->loadHomeStats()]);
    }

    public function index()
    {
        return view('welcome', ['stats' => $this->loadHomeStats()]);
    }

    private function loadHomeStats(): array
    {
        try {
            return [
                'patients' => User::where('role_id', 3)->count(),
                'doctors' => Doctor::count(),
                'appointments_today' => Appointment::whereDate('appointment_time', today())->count(),
                'total_appointments' => Appointment::count(),
                'services' => Service::count(),
                'rooms' => Room::count(),
            ];
        } catch (QueryException $exception) {
            Log::error('HomeController could not load stats', [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ]);

            return [
                'patients' => 0,
                'doctors' => 0,
                'appointments_today' => 0,
                'total_appointments' => 0,
                'services' => 0,
                'rooms' => 0,
            ];
        }
    }
}
