<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Interfaces\repositories\icalendarInterface;
use Carbon\Carbon;

class generatecalender extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generatecalender {year?} {--force : Force recreation of existing calendar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate calendar with weekdays (Monday-Friday) for a specific year';

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
        $force = $this->option('force');
        
        $this->info("🗓️  Generating calendar for year: {$year}");
        $this->newLine();
        
        // Check if calendar already exists
        $existingYear = \App\Models\Calendaryear::where('year', $year)->first();
        
        if ($existingYear && !$force) {
            $this->warn("Calendar for year {$year} already exists!");
            if (!$this->confirm("Do you want to recreate it? This will delete existing data.")) {
                $this->info("Calendar generation cancelled.");
                return 0;
            }
            $this->deleteExistingCalendar($year);
        }
        
        try {
            $this->info("Creating calendar structure...");
            $progressBar = $this->output->createProgressBar(12); // 12 months
            $progressBar->start();
            
            $calendarYear = $this->calendarRepository->createcalendaryear($year);
            $progressBar->finish();
            
            $this->newLine(2);
            $this->info("✅ Calendar created successfully!");
            
            // Display statistics
            $this->displayStatistics($year);
            
            // Show sample data
            $this->displaySampleData($year);
            
        } catch (\Exception $e) {
            $this->error("❌ Error creating calendar: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }

    /**
     * Delete existing calendar data
     */
    private function deleteExistingCalendar($year)
    {
        $this->info("Deleting existing calendar data...");
        
        $calendarYear = \App\Models\Calendaryear::where('year', $year)->first();
        if ($calendarYear) {
            // Delete calendar days
            \App\Models\Calendarday::whereHas('calendarweeks', function($query) use ($calendarYear) {
                $query->where('calendaryear_id', $calendarYear->id);
            })->delete();
            
            // Delete calendar weeks
            \App\Models\Calendarweek::where('calendaryear_id', $calendarYear->id)->delete();
            
            // Delete calendar year
            $calendarYear->delete();
            
            $this->info("✅ Existing calendar data deleted.");
        }
    }

    /**
     * Display calendar statistics
     */
    private function displayStatistics($year)
    {
        $this->info("📊 Calendar Statistics:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Weeks', \App\Models\Calendarweek::whereHas('calendaryears', function($query) use ($year) {
                    $query->where('year', $year);
                })->count()],
                ['Total Weekdays', \App\Models\Calendarday::whereHas('calendarweeks', function($query) use ($year) {
                    $query->whereHas('calendaryears', function($q) use ($year) {
                        $q->where('year', $year);
                    });
                })->count()],
                ['Months Covered', \App\Models\Calendarweek::whereHas('calendaryears', function($query) use ($year) {
                    $query->where('year', $year);
                })->distinct('month')->count()]
            ]
        );
    }

    /**
     * Display sample calendar data
     */
    private function displaySampleData($year)
    {
        $this->newLine();
        $this->info("📅 Sample Calendar Data:");
        
        // Get first month's data
        $firstWeek = \App\Models\Calendarweek::whereHas('calendaryears', function($query) use ($year) {
            $query->where('year', $year);
        })->orderBy('id')->first();
        
        if ($firstWeek) {
            $days = $firstWeek->calendardays()->orderBy('maindate')->get();
            
            $this->table(
                ['Date', 'Day'],
                $days->map(function($day) {
                    return [
                        Carbon::parse($day->maindate)->format('Y-m-d'),
                        $day->day
                    ];
                })->toArray()
            );
            
            $this->newLine();
            $this->info("📅 Week Information:");
            $this->line("Week: {$firstWeek->week}");
            $this->line("Month: {$firstWeek->month}");
            $this->line("Start Date: {$firstWeek->start_date}");
            $this->line("End Date: {$firstWeek->end_date}");
        }
        
        $this->newLine();
        $this->info("🎯 Calendar Features:");
        $this->line("• Only weekdays (Monday-Friday) are included");
        $this->line("• Organized by weeks and months");
        $this->line("• Each week includes start_date and end_date");
        $this->line("• Ready for date saving and management");
        $this->line("• Access via /calendar route in your application");
    }
}
