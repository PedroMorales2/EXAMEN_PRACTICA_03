<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;

class DistrictController extends Controller
{
    public function getDistricts($province_id) {
        return response()->json(District::where('province_id', $province_id)->get(['id', 'name']));
    }
}
