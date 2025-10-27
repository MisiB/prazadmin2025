<?php

namespace App\Interfaces\repositories;

interface icalendarInterface
{
    public function createcalendaryear($year);
    public function getcurrentweek();
    public function getcalenderuserweektasks($startDate, $endDate);
    public function getusercalendarweektasks($calendarweek_id);
    public function getweekdays($startDate, $endDate);
    public function savedate($date);
    public function getyearlydates($year);
    public function getweeks($year);
    public function sendforapproval($calendarweek_id);
    public function gettasksbydepartment($department_id,$startDate,$endDate);
    public function gettasksbydepartmentbycalenderweek($department_id,$calendarweek_id);
    public function gettasksbycalenderweek($calendarweek_id);
}
