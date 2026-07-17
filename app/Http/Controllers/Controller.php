<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Retrieve the active Gym ID context.
     * Supports tenant-switching for superadmins.
     */
    protected function getActiveGymId()
    {
        if (auth()->check() && auth()->user()->role === 'superadmin') {
            if (session()->has('superadmin_gym_id')) {
                return session('superadmin_gym_id');
            }
        }
        
        return auth()->check() ? auth()->user()->gym_id : null;
    }
}
