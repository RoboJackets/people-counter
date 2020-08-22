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
     * @return int
     */
    public function handle()
    {
        $this->output->title('Starting import');
        (new UsersImport($this->argument('space')))->withOutput($this->output)->import($this->argument('file'));
        $this->output->success('Import successful');
    }
}
