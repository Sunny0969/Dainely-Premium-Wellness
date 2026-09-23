<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class AdminBackupController extends AdminController
{
    public function index()
    {
        $backupPath = base_path('backups');
        
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $files = File::files($backupPath);
        
        $backups = [];
        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => number_format($file->getSize() / 1024 / 1024, 2) . ' MB',
                    'date' => date('Y-m-d H:i:s', $file->getMTime()),
                    'timestamp' => $file->getMTime(),
                ];
            }
        }
        
        usort($backups, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return view('admin.backups.index', compact('backups'));
    }

    public function create()
    {
        try {
            // Increase max execution time to 5 minutes for manual backups
            // since fetching data from remote Supabase can take over 60s
            set_time_limit(300);
            
            \App\Services\BackupService::run();
            return back()->with('success', 'Backup created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
    
    public function download($filename)
    {
        $backupPath = base_path('backups/' . basename($filename));
        if (File::exists($backupPath)) {
            return response()->download($backupPath);
        }
        return back()->with('error', 'File not found');
    }
}
