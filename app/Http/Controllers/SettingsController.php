<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function fetchCompanySettings()
    {
        return Utility::fetchCompanySettings();
    }

    public function saveCompanySettings(Request $request)
    {
        return Utility::saveCompanySettings($request);
    }
}
