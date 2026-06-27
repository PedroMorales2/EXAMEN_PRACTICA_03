<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Province;

class ProvinceController extends Controller
{
    public function getProvinces($department_id) {
        return response()->json(Province::where('department_id', $department_id)->get(['id', 'name']));
    }
}
