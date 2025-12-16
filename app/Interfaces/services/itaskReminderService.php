<?php

namespace App\Interfaces\services;

interface itaskReminderService
{
    /**
     * Get outstanding tasks from previous days for a user
     *
     * @param  int  $userId
     * @return \Illuminate\Support\Collection
     */
    public function getpreviousdaystasks($userId);

    /**
     * Get pending or ongoing tasks from previous week for a user
     *
     * @param  int  $userId
     * @return \Illuminate\Support\Collection
     */
    public function getpreviousweektasks($userId);

    /**
     * Rollover tasks from previous week to current week
     *
     * @param  int  $userId
     * @return array
     */
    public function rolloverpreviousweektasks($userId);
}
