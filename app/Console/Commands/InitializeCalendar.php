<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Interfaces\repositories\icalendarInterface;

class InitializeCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:init {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize calendar with weekdays for a specific year';

    protected $calendarRepository;

    public function __construct(icalendarInterface $calendarRepository)
    {
        parent::__construct();
        $this->calendarRepository = $calendarRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?? date('Y');
        
        $this->info("Initializing calendar for year: {$year}");
        
        try {
            $calendarYear = $this->calendarRepository->createcalendaryear($year);
            $this->info("Calendar initialized successfully for year: {$year}");
            
            // Get some statistics
            $yearlyDates = $this->calendarRepository->getyearlydates($year);
            $this->info("Total weekdays created: " . $yearlyDates->count());
            
        } catch (\Exception $e) {
            $this->error("Error initializing calendar: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}


