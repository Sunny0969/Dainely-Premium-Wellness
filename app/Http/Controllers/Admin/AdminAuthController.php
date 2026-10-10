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

    public function profile()
    {
        return view('admin.auth.profile');
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $path = base_path('.env');
        if (file_exists($path)) {
            $env = file_get_contents($path);
            
            // Update ADMIN_EMAIL
            if (preg_match('/^ADMIN_EMAIL=.*$/m', $env)) {
                $env = preg_replace('/^ADMIN_EMAIL=.*$/m', 'ADMIN_EMAIL="' . $validated['email'] . '"', $env);
            } else {
                $env .= "\nADMIN_EMAIL=\"" . $validated['email'] . "\"";
            }

            // Update ADMIN_PASSWORD
            if (preg_match('/^ADMIN_PASSWORD=.*$/m', $env)) {
                $env = preg_replace('/^ADMIN_PASSWORD=.*$/m', 'ADMIN_PASSWORD="' . $validated['password'] . '"', $env);
            } else {
                $env .= "\nADMIN_PASSWORD=\"" . $validated['password'] . "\"";
            }
            file_put_contents($path, $env);
        }

        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        
        return back()->with('success', 'Admin credentials updated successfully.');
    }

    public function logout()
    {
        session()->forget('admin_authenticated');
        return redirect('/dainely-admin-panel/login')->with('success', 'Logged out successfully.');
    }
}