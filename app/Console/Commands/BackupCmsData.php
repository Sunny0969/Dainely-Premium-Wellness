<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class BackupCmsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup all CMS data to a JSON file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting CMS backup...');
        
        try {
            $filename = BackupService::run();
            $this->info("Backup created successfully: {$filename}");
        } catch (\Throwable $e) {
            $this->error("Backup failed: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
