<?php

namespace App\Interfaces\services;

interface ICalendarService
{
    public function initializeYear($year);
    public function getMonthData($year, $month);
    public function getWeekData($date);
    public function getWeekInfo($date);
    public function saveMultipleDates(array $dates);
    public function getYearStatistics($year);
    public function isWeekday($date);
    public function getNextWeekday($date);
    public function getPreviousWeekday($date);
    public function getcalenderuserweektasks($startDate, $endDate);
    public function getusercalendarweektasks($calendarweek_id);
    public function generateWeekdayRange($startDate, $endDate);
    public function getweeks($year);
    public function sendforapproval($calendarweek_id);
    public function gettasksbydepartment($department_id,$startDate,$endDate);
    public function gettasksbydepartmentbycalenderweek($department_id,$calendarweek_id);
    public function gettasksbycalenderweek($calendarweek_id);
}

