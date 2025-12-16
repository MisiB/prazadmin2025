<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iweeklyTaskReviewInterface;
use App\Models\WeeklyTaskReview;

class _weeklyTaskReviewRepository implements iweeklyTaskReviewInterface
{
    protected $weeklyTaskReview;

    public function __construct(WeeklyTaskReview $weeklyTaskReview)
    {
        $this->weeklyTaskReview = $weeklyTaskReview;
    }

    public function getreviewsbyuserids(array $userIds, $filters = [])
    {
        $query = $this->weeklyTaskReview->whereIn('user_id', $userIds);

        if (isset($filters['orderBy'])) {
            $query->orderBy($filters['orderBy']['column'], $filters['orderBy']['direction'] ?? 'asc');
        }

        return $query->get();
    }

    public function getreviewbyuserid($userId, $filters = [])
    {
        $query = $this->weeklyTaskReview->where('user_id', $userId);

        if (isset($filters['orderBy'])) {
            $query->orderBy($filters['orderBy']['column'], $filters['orderBy']['direction'] ?? 'desc');
        }

        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->get();
    }
}
