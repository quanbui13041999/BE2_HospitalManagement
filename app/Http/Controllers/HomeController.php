<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Phải có dòng này để lấy user

class HomeController extends Controller
{
    public function index()
    {
       
        $user = Auth::user();

     
        return view('Home.trangchu', compact('user'));
    }
}