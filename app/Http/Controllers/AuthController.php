<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();

        $homeRoute = match (true) {
            $user?->hasRole('admin') => 'admin.login',
            $user?->hasRole('owner') => 'owner.login',
            default => 'index.page',
        };

        $user?->forceFill(['last_logout_at' => now()])->save();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($homeRoute);
    }
}