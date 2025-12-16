<?php

namespace App\Interfaces\repositories;

interface itaskInterface
{
    public function getmytasks($year);

    public function gettask($id);

    public function createtask($data);

    public function updatetask($id, $data);

    public function deletetask($id);

    public function marktask($id, $status, $evidencePath = null, $originalName = null);

    public function approvetask(array $data);

    public function bulkapprovetasks(array $data);

    public function gettasksbyuseranddaterange($userId, $startDate, $endDate);

    public function getpendingorongoingtasksbyuseranddaterange($userId, $startDate, $endDate);

    public function gettasksbyuseridsanddaterange($userIds, $startDate, $endDate);

    public function gettasksbyuserids($userIds, $filters = []);

    public function getlinkedtaskscountbyuserids($userIds);

    public function gettotaltaskscountbyuserids($userIds);

    public function gettaskidsbyuseridsanddaterange($userIds, $startDate, $endDate);

    public function gettasksbyuseridanddaterange($userId, $startDate, $endDate);

    public function gettaskswithcalendardaybyuseridsanddaterange($userIds, $startDate, $endDate);

    public function gettasksbyuseridsandcalendarweek($userIds, $calendarweekId);
}
