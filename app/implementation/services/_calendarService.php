<?php

namespace App\implementation\services;

use App\Interfaces\services\ICalendarService;
use App\Interfaces\repositories\icalendarInterface;
use Carbon\Carbon;

class _calendarService implements ICalendarService
{
    protected $calendarRepository;

    public function __construct(icalendarInterface $calendarRepository)
    {
        $this->calendarRepository = $calendarRepository;
    }

    /**
     * Initialize calendar for a year
     */
    public function initializeYear($year)
    {
        return $this->calendarRepository->createcalendaryear($year);
    }

    /**
     * Get calendar data for a specific month
     */
    public function getMonthData($year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        return $this->calendarRepository->getweekdays($startDate->format('Y-m-d'), $endDate->format('Y-m-d'));
    }

    /**
     * Get calendar data for a specific week
     */
    public function getWeekData($date)
    {
        $carbonDate = Carbon::parse($date);
        $monday = $carbonDate->copy()->startOfWeek();
        $friday = $monday->copy()->addDays(4);
        
        return $this->calendarRepository->getweekdays($monday->format('Y-m-d'), $friday->format('Y-m-d'));
    }

    /**
     * Get week information with start and end dates
     */
    public function getWeekInfo($date)
    {
        $carbonDate = Carbon::parse($date);
        $monday = $carbonDate->copy()->startOfWeek();
        $friday = $monday->copy()->addDays(4);
        
        return [
            'start_date' => $monday->format('Y-m-d'),
            'end_date' => $friday->format('Y-m-d'),
            'week_number' => $monday->weekOfYear,
            'month' => $monday->format('F'),
            'year' => $monday->year,
            'days' => $this->calendarRepository->getweekdays($monday->format('Y-m-d'), $friday->format('Y-m-d'))
        ];
    }

    /**
     * Save multiple dates at once
     */
    public function saveMultipleDates(array $dates)
    {
        $results = [];
        $errors = [];
        
        foreach ($dates as $date) {
            try {
                $results[] = $this->calendarRepository->savedate($date);
            } catch (\Exception $e) {
                $errors[] = "Failed to save {$date}: " . $e->getMessage();
            }
        }
        
        return [
            'saved' => $results,
            'errors' => $errors
        ];
    }

    /**
     * Get calendar statistics for a year
     */
    public function getYearStatistics($year)
    {
        $yearlyDates = $this->calendarRepository->getyearlydates($year);
        
        return [
            'total_weekdays' => $yearlyDates->count(),
            'by_month' => $yearlyDates->groupBy(function($date) {
                return Carbon::parse($date->maindate)->format('F');
            })->map->count(),
            'by_day' => $yearlyDates->groupBy('day')->map->count()
        ];
    }

    /**
     * Check if a date is a weekday
     */
    public function isWeekday($date)
    {
        $carbonDate = Carbon::parse($date);
        return $carbonDate->dayOfWeek >= 1 && $carbonDate->dayOfWeek <= 5;
    }

    public function getweeks($year){
        return $this->calendarRepository->getweeks($year);
    }

    /**
     * Get next weekday from a given date
     */
    public function getNextWeekday($date)
    {
        $carbonDate = Carbon::parse($date);
        
        do {
            $carbonDate->addDay();
        } while ($carbonDate->dayOfWeek < 1 || $carbonDate->dayOfWeek > 5);
        
        return $carbonDate->format('Y-m-d');
    }

    /**
     * Get previous weekday from a given date
     */
    public function getPreviousWeekday($date)
    {
        $carbonDate = Carbon::parse($date);
        
        do {
            $carbonDate->subDay();
        } while ($carbonDate->dayOfWeek < 1 || $carbonDate->dayOfWeek > 5);
        
        return $carbonDate->format('Y-m-d');
    }

    /**
     * Generate date range for weekdays only
     */
    public function generateWeekdayRange($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $weekdays = [];
        
        while ($start->lte($end)) {
            if ($this->isWeekday($start->format('Y-m-d'))) {
                $weekdays[] = [
                    'date' => $start->format('Y-m-d'),
                    'day' => $start->format('l'),
                    'formatted' => $start->format('M d, Y')
                ];
            }
            $start->addDay();
        }
        
        return $weekdays;
    }
    public function getcalenderuserweektasks($startDate, $endDate)
    {
        return $this->calendarRepository->getcalenderuserweektasks($startDate, $endDate);
    }
    public function getusercalendarweektasks($calendarweek_id){
        return $this->calendarRepository->getusercalendarweektasks($calendarweek_id);
    }
    public function sendforapproval($calendarweek_id){
        return $this->calendarRepository->sendforapproval($calendarweek_id);
    }
    public function gettasksbydepartment($department_id,$startDate,$endDate){
        return $this->calendarRepository->gettasksbydepartment($department_id,$startDate,$endDate);
    }
    public function gettasksbydepartmentbycalenderweek($department_id,$calendarweek_id){
        return $this->calendarRepository->gettasksbydepartmentbycalenderweek($department_id,$calendarweek_id);
    }
    public function gettasksbycalenderweek($calendarweek_id){
        return $this->calendarRepository->gettasksbycalenderweek($calendarweek_id);
    }
}

