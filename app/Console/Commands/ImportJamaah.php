<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportJamaah extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jamaah:import {file : Path to CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data jamaah from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $this->info("Starting import from $file...");
        
        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Services\JamaahImportService, $file);
            $this->info('Import completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }
    }
}
