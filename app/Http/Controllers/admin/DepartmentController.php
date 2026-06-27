<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province; 

class DepartmentController extends Controller
{
    public function getProvinces($department_id)
    {
        $provinces = Province::where('department_id', $department_id)->get(['id', 'name']);
        return response()->json($provinces);
    }
}