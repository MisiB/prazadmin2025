<?php

namespace App\Console\Commands;

use App\Interfaces\services\IImportService;
use Illuminate\Console\Command;

class importdataCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:importdata-cmd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    protected $importService;
    public function __construct(IImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }
    public function handle()
    {
        
        $input = $this->ask('Which table do you want to import? (customers or banktransactions)');
        if($input == 'customers'){
            $this->importService->importcustomers();
        }
        if($input == 'banktransactions'){
            $this->importService->importbanktransactions();
        }
    }
}
