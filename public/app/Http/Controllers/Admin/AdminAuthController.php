<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    public function entry()
    {
        return session('admin_authenticated')
            ? redirect('/dainely-admin-panel/dashboard')
            : redirect('/dainely-admin-panel/login');
    }

    public function login()
    {
        if (session()->get('admin_authenticated')) {
            return redirect('/dainely-admin-panel/dashboard');
        }
        return view('admin.auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $adminEmail = config('app.admin_email', env('ADMIN_EMAIL', 'admin@dainelylab.com'));
        $adminPassword = config('app.admin_password', env('ADMIN_PASSWORD', 'admin12345'));

        if ($credentials['email'] === $adminEmail && $credentials['password'] === $adminPassword) {
            session()->put('admin_authenticated', true);
            return redirect('/dainely-admin-panel/dashboard')->with('success', 'Welcome back, Administrator!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout()
    {
        session()->forget('admin_authenticated');
        return redirect('/dainely-admin-panel/login')->with('success', 'Logged out successfully.');
    }
}
