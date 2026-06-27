<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Zone;



class AdminController extends Controller
{

    function index()
    {
        $totalVehicles = Vehicle::count();
        $totalPersonal = User::count();
        $totalZones = Zone::count();
        
        return view('admin.index', compact(
            'totalVehicles', 
            'totalPersonal',
            'totalZones'
        ));
    }
}