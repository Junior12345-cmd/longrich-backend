<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Country;


class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::all(['id', 'title', 'currency_code','phone_prefix']);
        return response()->json($countries);
    }
}
