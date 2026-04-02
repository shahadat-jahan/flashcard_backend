<?php

namespace App\Console\Commands;

use App\Imports\UsersImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class ImportUsers extends Command
{
    // Example usage: php artisan users:import users.xlsx
    protected $signature = 'users:import {file}';

    protected $description = 'Import users from an Excel file';

    public function handle(): void
    {
        $file = $this->argument('file');

        if (! file_exists(storage_path('app/'.$file))) {
            $this->error('File not found: '.storage_path('app/'.$file));

            return;
        }

        $this->info('Starting import...');

        $import = new UsersImport;
        Excel::import($import, $file);
        $insertedCount = $import->getInsertedCount();

        $this->info("Users imported successfully! Total inserted: {$insertedCount}");
    }
}
