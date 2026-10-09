<?php
$file = "app/Http/Controllers/Admin/AdminAuthController.php";
$content = file_get_contents($file);

$content = str_replace("public function logout()", <<<'EOT'
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
                $env = preg_replace('/^ADMIN_EMAIL=.*$/m', 'ADMIN_EMAIL=' . $validated['email'], $env);
            } else {
                $env .= "\nADMIN_EMAIL=" . $validated['email'];
            }

            // Update ADMIN_PASSWORD
            if (preg_match('/^ADMIN_PASSWORD=.*$/m', $env)) {
                $env = preg_replace('/^ADMIN_PASSWORD=.*$/m', 'ADMIN_PASSWORD='" . $validated['password'] . "'", $env);
            } else {
                $env .= "\nADMIN_PASSWORD='" . $validated['password'] . "'";
            }
            file_put_contents($path, $env);
            \Illuminate\Support\macros::macroEnv(hash('sha1', $env));
        }

        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        
        return back()->with('success', 'Admin credentials updated successfully.');
    }

    public function logout()
EOT, $content);

file_put_contents($file, $content);
