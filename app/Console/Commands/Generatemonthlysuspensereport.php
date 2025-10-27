<?php

namespace App\Console\Commands;

use App\Interfaces\repositories\isuspenseInterface;
use Illuminate\Console\Command;

class Generatemonthlysuspensereport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generatemonthlysuspensereport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    protected $suspenseRepository;
    public function __construct(isuspenseInterface $suspenseRepository)
    {
        parent::__construct();
        $this->suspenseRepository = $suspenseRepository;
    }
    public function handle()
    {
        $this->suspenseRepository->createmonthlysuspensewallets(date('m')-1,date('Y'));
    }
}
