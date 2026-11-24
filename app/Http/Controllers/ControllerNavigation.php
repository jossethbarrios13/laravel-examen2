<?php

namespace App\Http\Controllers;

class ControllerNavigation extends Controller
{
    public function login()
    {
        return view('modules.navigation.login');
    }
}
