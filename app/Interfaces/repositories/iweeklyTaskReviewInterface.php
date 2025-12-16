<?php

namespace App\Interfaces\repositories;

interface iweeklyTaskReviewInterface
{
    public function getreviewsbyuserids(array $userIds, $filters = []);

    public function getreviewbyuserid($userId, $filters = []);
}
