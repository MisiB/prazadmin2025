<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Organization Health Scorecard Configuration
    |--------------------------------------------------------------------------

    | These weights determine the relative importance of each metric in the
    | overall organization health scorecard. All weights must sum to 1.0 (100%).
    |
    | Adjust these values based on your organization's strategic priorities.
    | Higher weights indicate greater importance in the overall score.
    |
    */

    'organization' => [
        'weights' => [
            'task_completion' => 0.25,      // 25% - Task completion rate
            'budget_management' => 0.20,    // 20% - Budget utilization efficiency
            'issue_resolution' => 0.20,     // 20% - Issue resolution rate
            'workplan_progress' => 0.15,    // 15% - Workplan approval/progress
            'department_health' => 0.20,    // 20% - Average department health
        ],

        'thresholds' => [
            'excellent' => 80,
            'good' => 65,
            'fair' => 50,
        ],
    ],

    'department' => [
        'weights' => [
            'task_completion' => 0.30,      // 30% - Task completion rate
            'budget_management' => 0.20,    // 20% - Budget utilization efficiency
            'issue_resolution' => 0.25,     // 25% - Issue resolution rate
            'workplan_progress' => 0.15,    // 15% - Workplan approval/progress
            'overdue_penalty' => 0.10,      // 10% - Penalty for overdue tasks
        ],

        'thresholds' => [
            'excellent' => 80,
            'good' => 65,
            'fair' => 50,
        ],

        'overdue_penalty' => [
            'max_penalty' => 20,
            'penalty_per_overdue' => 2,
        ],
    ],
];
