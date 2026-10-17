<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

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

        $savedCreds = $this->getSavedCredentials();

        if ($credentials['email'] === $savedCreds['email'] && $credentials['password'] === $savedCreds['password']) {
            session()->put('admin_authenticated', true);
            return redirect('/dainely-admin-panel/dashboard')->with('success', 'Welcome back, Administrator!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    private function getSavedCredentials()
    {
        // 1. JSON file se uthao
        $jsonPath = storage_path('app/admin_credentials.json');
        if (File::exists($jsonPath)) {
            $data = json_decode(File::get($jsonPath), true);
            if ($data && isset($data['email']) && isset($data['password'])) {
                return $data;
            }
        }

        // 2. Agar json nahi hai to .env se uthao
        return [
            'email' => config('app.admin_email', env('ADMIN_EMAIL', 'admin@dainelylab.com')),
            'password' => config('app.admin_password', env('ADMIN_PASSWORD', 'admin12345'))
        ];
    }

    private function saveCredentialsPermanently($newEmail, $newPassword)
    {
        // JSON file mein update karo
        $jsonPath = storage_path('app/admin_credentials.json');
        File::put($jsonPath, json_encode(['email' => $newEmail, 'password' => $newPassword]));

        // .env file ko update karne ki koshish karo
        $envPath = base_path('.env');
        if (File::exists($envPath) && is_writable($envPath)) {
            $env = File::get($envPath);
            if (preg_match('/^ADMIN_EMAIL=.*$/m', $env)) {
                $env = preg_replace('/^ADMIN_EMAIL=.*$/m', 'ADMIN_EMAIL="' . $newEmail . '"', $env);
            } else {
                $env .= "\nADMIN_EMAIL=\"" . $newEmail . "\"";
            }

            if (preg_match('/^ADMIN_PASSWORD=.*$/m', $env)) {
                $env = preg_replace('/^ADMIN_PASSWORD=.*$/m', 'ADMIN_PASSWORD="' . $newPassword . '"', $env);
            } else {
                $env .= "\nADMIN_PASSWORD=\"" . $newPassword . "\"";
            }
            File::put($envPath, $env);
        }

        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
        } catch (\Exception $e) {}
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

        $this->saveCredentialsPermanently($validated['email'], $validated['password']);
        
        return back()->with('success', 'Admin credentials updated successfully! New password is now active.');
    }

    public function logout(Request $request)
    {
        session()->forget('admin_authenticated');
        return redirect('/dainely-admin-panel/login')->with('success', 'You have been logged out.');
    }
}