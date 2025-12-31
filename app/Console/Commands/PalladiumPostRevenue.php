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
        $progressBar = null;
        $totalItems = 0;
        $currentItem = 0;

        $result = $this->revenuepostingrepository->processPendingRevenuePostingJobs(
            function ($count, $current) use (&$progressBar, &$totalItems, &$currentItem) {
                if ($progressBar === null && $count > 0) {
                    $totalItems = $count;
                    $currentItem = 0;
                    $this->info("Processing {$count} revenue posting items...");
                    $progressBar = $this->output->createProgressBar($count);
                    $progressBar->start();
                }

                if ($progressBar !== null) {
                    $progressBar->advance();
                    $currentItem = $current;
                }
            }
        );

        if ($progressBar !== null) {
            $progressBar->finish();
            $this->newLine();
        }

        if ($result['status'] === 'success') {
            $this->info($result['message']);
        } else {
            $this->error($result['message']);
        }
    }
}
