<?php

namespace App\Interfaces\repositories;

interface irevenuepostingInterface
{
    public function getRevenuePostingJobs($year);
    public function getRevenuePostingJob($id);
    public function getrevenuepostinginvoices($id);
    public function createRevenuePostingJob($data);
    public function updateRevenuePostingJob($id, $data);
    public function deleteRevenuePostingJob($id);
    public function approveRevenuePostingJob($id);
    public function getRevenuePostingJobItems($id);
    public function deleteRevenuePostingJobItems($id);
    public function processPendingRevenuePostingJobs();
}
