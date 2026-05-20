<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Room;

class HomeController extends Controller
{
    public function welcome()
    {
        $stats = [
            'patients' => User::where('role_id', 3)->count(),
            'doctors' => Doctor::count(),
            'appointments_today' => Appointment::whereDate('appointment_time', today())->count(),
            'total_appointments' => Appointment::count(),
            'services' => Service::count(),
            'rooms' => Room::count(),
        ];

        return view('welcome', compact('stats'));
    }

    public function index()
    {
        $stats = [
            'patients' => User::where('role_id', 3)->count(),
            'doctors' => Doctor::count(),
            'appointments_today' => Appointment::whereDate('appointment_time', today())->count(),
            'total_appointments' => Appointment::count(),
            'services' => Service::count(),
            'rooms' => Room::count(),
        ];

        return view('welcome', compact('stats'));
    }
}
