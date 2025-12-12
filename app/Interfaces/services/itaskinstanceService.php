<?php

namespace App\Interfaces\services;

interface itaskinstanceService
{
    public function getinstancesbydate($date);

    public function getinstancesbydateanduser($date, $userId);

    public function createinstance(array $data);

    public function loghours($instanceId, $workedHours);

    public function updateplannedhours($instanceId, $plannedHours);

    public function rolloverinstance($instanceId, $nextDate = null);

    public function completeinstance($instanceId);
}
