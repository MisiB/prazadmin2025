<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\icalendarInterface;
use App\Models\Calendarday;
use App\Models\Calendarweek;
use App\Models\Calendaryear;
use App\Models\Calenderworkusertask;
use App\Models\Departmentuser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class _calenderRepository implements icalendarInterface
{
    /**
     * Create a new class instance.
     */
    protected $calendaryear;

    protected $calendarweek;

    protected $calendarday;

    protected $calenderworkusertask;

    protected $userdepartment;

    protected $user;

    public function __construct(Calendaryear $calendaryear, Calendarweek $calendarweek, Calendarday $calendarday, Calenderworkusertask $calenderworkusertask, Departmentuser $userdepartment, User $user)
    {
        $this->calendaryear = $calendaryear;
        $this->calendarweek = $calendarweek;
        $this->calendarday = $calendarday;
        $this->calenderworkusertask = $calenderworkusertask;
        $this->userdepartment = $userdepartment;
        $this->user = $user;
    }

    /**
     * Create calendar year with only weekdays (Monday to Friday)
     */
    public function createcalendaryear($year)
    {
        // Check if calendar year already exists
        $existingYear = $this->calendaryear->where('year', $year)->first();
        if ($existingYear) {
            return $existingYear;
        }

        // Create calendar year
        $calendarYear = $this->calendaryear->create(['year' => $year]);

        // Generate all weeks for the year
        $startDate = Carbon::createFromDate($year, 1, 1);
        $endDate = Carbon::createFromDate($year, 12, 31);

        $currentDate = $startDate->copy();
        $weekNumber = 1;

        while ($currentDate->lte($endDate)) {
            // Get the Monday of the current week
            $monday = $currentDate->copy()->startOfWeek();

            // Only process if the Monday is within the year
            if ($monday->year == $year) {
                $monthName = $monday->format('F');

                // Create calendar week with start and end dates
                $friday = $monday->copy()->addDays(4); // Friday of the same week
                $calendarWeek = $this->calendarweek->create([
                    'calendaryear_id' => $calendarYear->id,
                    'month' => $monthName,
                    'week' => 'Week '.$weekNumber,
                    'start_date' => $monday->format('Y-m-d'),
                    'end_date' => $friday->format('Y-m-d'),
                ]);

                // Create days for Monday to Friday only
                for ($dayOffset = 0; $dayOffset < 5; $dayOffset++) {
                    $dayDate = $monday->copy()->addDays($dayOffset);

                    // Only create if the date is within the year
                    if ($dayDate->year == $year) {
                        $this->calendarday->create([
                            'calendarweek_id' => $calendarWeek->id,
                            'maindate' => $dayDate->format('Y-m-d'),
                            'day' => $dayDate->format('l'), // Day name (Monday, Tuesday, etc.)
                        ]);
                    }
                }

                $weekNumber++;
            }

            // Move to next week
            $currentDate->addWeek();
        }

        return $calendarYear;
    }

    /**
     * Get current week data
     */
    public function getcurrentweek()
    {
        $today = Carbon::now();
        $monday = $today->copy()->startOfWeek();

        // Find the calendar week for this Monday
        $calendarWeek = $this->calendarweek->whereHas('calendaryears', function ($query) use ($monday) {
            $query->where('year', $monday->year);
        })->where('month', $monday->format('F'))->first();

        if (! $calendarWeek) {
            // Create the year if it doesn't exist
            $this->createcalendaryear($monday->year);
            $calendarWeek = $this->calendarweek->whereHas('calendaryears', function ($query) use ($monday) {
                $query->where('year', $monday->year);
            })->where('month', $monday->format('F'))->first();
        }

        return $calendarWeek ? $calendarWeek->calendardays : collect();
    }

    public function getweeks($year)
    {
        return $this->calendarweek->whereHas('calendaryears', function ($query) use ($year) {
            $query->where('year', $year);
        })->get();
    }

    public function getusercalendarweektasks($calendarweek_id)
    {
        return $this->calendarweek->with(['calenderworkusertasks' => function ($query) {
            $query->where('user_id', Auth::user()->id);
        }, 'calendardays.tasks' => function ($query) {
            $query->where('user_id', Auth::user()->id);
        }])->where('id', $calendarweek_id)->first();
    }

    public function getcalenderuserweektasks($startDate, $endDate)
    {
        $today = Carbon::parse($startDate);
        $monday = $today->copy()->startOfWeek();
        $endDate = $today->copy()->endOfWeek();
        $calendarWeek = $this->calendarweek->with(['calenderworkusertasks' => function ($query) {
            $query->where('user_id', Auth::user()->id);
        }, 'calendardays.tasks' => function ($query) {
            $query->where('user_id', Auth::user()->id);
        }])->where('start_date', '>=', $startDate)->where('end_date', '<=', $endDate)->first();

        return $calendarWeek ? $calendarWeek : collect();
    }

    /**
     * Get weekdays for a specific date range
     */
    public function getweekdays($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $weekdays = collect();

        while ($start->lte($end)) {
            // Only include Monday to Friday
            if ($start->dayOfWeek >= 1 && $start->dayOfWeek <= 5) {
                $weekdays->push([
                    'date' => $start->format('Y-m-d'),
                    'day' => $start->format('l'),
                    'formatted' => $start->format('M d, Y'),
                ]);
            }
            $start->addDay();
        }

        return $weekdays;
    }

    /**
     * Save a specific date (weekday only)
     */
    public function savedate($date)
    {
        $carbonDate = Carbon::parse($date);

        // Only allow Monday to Friday
        if ($carbonDate->dayOfWeek < 1 || $carbonDate->dayOfWeek > 5) {
            throw new \Exception('Only weekdays (Monday to Friday) are allowed');
        }

        // Ensure the calendar year exists
        $calendarYear = $this->calendaryear->where('year', $carbonDate->year)->first();
        if (! $calendarYear) {
            $calendarYear = $this->createcalendaryear($carbonDate->year);
        }

        // Find or create the calendar week
        $monday = $carbonDate->copy()->startOfWeek();
        $calendarWeek = $this->calendarweek->where('calendaryear_id', $calendarYear->id)
            ->where('month', $monday->format('F'))
            ->first();

        if (! $calendarWeek) {
            $friday = $monday->copy()->addDays(4); // Friday of the same week
            $calendarWeek = $this->calendarweek->create([
                'calendaryear_id' => $calendarYear->id,
                'month' => $monday->format('F'),
                'week' => 'Week '.$monday->weekOfYear,
                'start_date' => $monday->format('Y-m-d'),
                'end_date' => $friday->format('Y-m-d'),
            ]);
        }

        // Check if date already exists
        $existingDay = $this->calendarday->where('calendarweek_id', $calendarWeek->id)
            ->where('maindate', $carbonDate->format('Y-m-d'))
            ->first();

        if (! $existingDay) {
            return $this->calendarday->create([
                'calendarweek_id' => $calendarWeek->id,
                'maindate' => $carbonDate->format('Y-m-d'),
                'day' => $carbonDate->format('l'),
            ]);
        }

        return $existingDay;
    }

    /**
     * Get all saved dates for a specific year
     */
    public function getyearlydates($year)
    {
        $calendarYear = $this->calendaryear->where('year', $year)->first();

        if (! $calendarYear) {
            return collect();
        }

        return $this->calendarday->whereHas('calendarweeks', function ($query) use ($calendarYear) {
            $query->where('calendaryear_id', $calendarYear->id);
        })->orderBy('maindate')->get();
    }

    public function sendforapproval($calendarweek_id)
    {
        $check = $this->calenderworkusertask->where('calendarweek_id', $calendarweek_id)->where('user_id', Auth::user()->id)->first();
        if ($check) {
            return ['status' => 'error', 'message' => 'You have already sent for approval'];
        }
        $this->calenderworkusertask->create([
            'calendarweek_id' => $calendarweek_id,
            'user_id' => Auth::user()->id,
            'status' => 'pending',
        ]);

        return ['status' => 'success', 'message' => 'Sent for approval successfully'];
    }

    public function gettasksbydepartment($department_id, $startDate, $endDate)
    {

        $calenderweek = $this->calendarweek->with('calendardays')->where('start_date', '>=', $startDate)->where('end_date', '<=', $endDate)->first();
        if (! $calenderweek) {
            return collect();
        }
        $users = $this->user->with(['calenderworkusertasks' => function ($query) use ($calenderweek) {
            $query->where('calendarweek_id', $calenderweek->id);
        }, 'calenderworkusertasks.calendarweek.calendardays.tasks'])->wherehas('department', function ($query) use ($department_id) {
            $query->where('department_id', $department_id);
        })->get();

        return ['users' => $users, 'calendarweek' => $calenderweek];

    }

    public function gettasksbydepartmentbycalenderweek($department_id, $calendarweek_id)
    {
        $calenderweek = $this->calendarweek->with('calendardays')->where('id', $calendarweek_id)->first();
        if (! $calenderweek) {
            return collect();
        }
        $users = $this->user->with(['calenderworkusertasks' => function ($query) use ($calenderweek) {
            $query->where('calendarweek_id', $calenderweek->id);
        }, 'calenderworkusertasks.calendarweek.calendardays.tasks'])->wherehas('department', function ($query) use ($department_id) {
            $query->where('department_id', $department_id);
        })->get();

        return ['users' => $users, 'calendarweek' => $calenderweek];
    }

    public function gettasksbycalenderweek($calendarweek_id)
    {
        $calenderweek = $this->calendarweek->where('id', $calendarweek_id)->first();

        if (! $calenderweek) {
            return ['users' => collect(), 'calendarweek' => null];
        }

        // Get all users with their tasks for this week
        $users = $this->user->with([
            'department.department',
            'calenderworkusertasks' => function ($query) use ($calendarweek_id) {
                $query->where('calendarweek_id', $calendarweek_id);
            },
        ])->get();

        // Load tasks for each user for this specific week
        $users->each(function ($user) use ($calendarweek_id) {
            // Get tasks for this user in this week
            $tasks = \App\Models\Task::whereHas('calendarday', function ($query) use ($calendarweek_id) {
                $query->where('calendarweek_id', $calendarweek_id);
            })->where('user_id', $user->id)->get();

            // Load the relationship dynamically for this week's days
            $user->week_tasks = $tasks;
        });

        return ['users' => $users, 'calendarweek' => $calenderweek];
    }
}
