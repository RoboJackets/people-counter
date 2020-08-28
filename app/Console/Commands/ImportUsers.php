<?php

namespace App\Console\Commands;

use App\Imports\UsersImport;
use Illuminate\Console\Command;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users {file : example.csv} {space : RoboJackets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from file';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->output->title('Starting import');
        $import = new UsersImport($this->argument('space'));
        $import->withOutput($this->output)->import($this->argument('file'));
        $success = true;

        if (count($import->failures()) > 0) {
            $success = false;
            foreach ($import->failures() as $failure) {
                $row = $failure->row();
                $errors = $failure->errors();
                $this->output->error("Row $row: " . implode(", ", $errors));
            }
        }

        if (count($import->errors()) > 0) {
            $success = false;
            foreach ($import->errors() as $error) {
                dd($error);
            }
        }

        if ($success) {
            $this->output->success('Import successful');
        }
    }
}
