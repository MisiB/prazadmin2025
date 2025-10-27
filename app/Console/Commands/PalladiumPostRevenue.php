<?php

namespace App\Console\Commands;

use App\Interfaces\repositories\irevenuepostingInterface;
use Illuminate\Console\Command;

class PalladiumPostRevenue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:palladium-post-revenue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    protected $revenuepostingrepository;
    public function __construct(irevenuepostingInterface $revenuepostingrepository)
    {
        parent::__construct();
        $this->revenuepostingrepository = $revenuepostingrepository;
    }
    public function handle()
    {
        $this->revenuepostingrepository->processPendingRevenuePostingJobs();
    }
}
