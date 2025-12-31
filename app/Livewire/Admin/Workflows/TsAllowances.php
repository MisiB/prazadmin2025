<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\services\itsallowanceService;
use App\Models\GradeBand;
use App\Models\TsAllowanceConfig;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class TsAllowances extends Component
{
    use Toast, WithFileUploads, WithPagination;

    public $breadcrumbs = [];

    public $search;

    public $year;

    public $modal;

    public $id;

    // Applicant Section Fields
    public $job_title;

    public $grade_band_id;

    public $trip_start_date;

    public $trip_end_date;

    public $reason_for_allowances;

    public $trip_attachment;

    public $existing_attachment_path;

    public $number_of_days = 0;

    // Rate fields (from config - readonly)
    public $out_of_station_rate = 0;

    public $overnight_rate = 0;

    public $bed_rate = 0;

    public $breakfast_rate = 0;

    public $lunch_rate = 0;

    public $dinner_rate = 0;

    public $mileage_rate_per_km = 0;

    // Checkboxes for optional allowances
    public $include_bed_allowance = false;

    public $include_breakfast = false;

    public $include_lunch = false;

    public $include_dinner = false;

    // Allowance day options - which days/nights apply
    // Options: 'all', 'first_day_only', 'last_day_only', 'first_and_last', 'middle_days_only', 'excluding_first_day', 'excluding_last_day'
    public $bed_option = 'all';

    public $breakfast_option = 'all';

    public $lunch_option = 'all';

    public $dinner_option = 'all';

    // Calculated days/nights
    public $bed_nights = 0;

    public $breakfast_days = 0;

    public $lunch_days = 0;

    public $dinner_days = 0;

    // Calculated allowance amounts
    public $out_of_station_subsistence = 0;

    public $overnight_allowance = 0;

    public $bed_allowance = 0;

    public $breakfast = 0;

    public $lunch = 0;

    public $dinner = 0;

    // Transport - Manual entry
    public $fuel = 0;

    public $toll_gates = 0;

    public $mileage_estimated_distance = 0;

    // Total
    public $total_allowance = 0;

    // Selected config
    public $selectedConfig = null;

    protected $tsallowanceService;

    public function boot(itsallowanceService $tsallowanceService)
    {
        $this->tsallowanceService = $tsallowanceService;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'T&S Allowances'],
        ];
        $this->year = date('Y');
        $this->search = '';
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedGradeBandId()
    {
        $this->loadConfigRates();
        $this->calculateAllowances();
    }

    public function updatedTripStartDate()
    {
        $this->calculateDays();
        $this->calculateAllowances();
    }

    public function updatedTripEndDate()
    {
        $this->calculateDays();
        $this->calculateAllowances();
    }

    public function updatedMileageEstimatedDistance()
    {
        $this->calculateAllowances();
    }

    public function updatedIncludeBedAllowance()
    {
        $this->calculateAllowances();
    }

    public function updatedBedOption()
    {
        $this->calculateAllowances();
    }

    public function updatedIncludeBreakfast()
    {
        $this->calculateAllowances();
    }

    public function updatedIncludeLunch()
    {
        $this->calculateAllowances();
    }

    public function updatedIncludeDinner()
    {
        $this->calculateAllowances();
    }

    public function updatedBreakfastOption()
    {
        $this->calculateAllowances();
    }

    public function updatedLunchOption()
    {
        $this->calculateAllowances();
    }

    public function updatedDinnerOption()
    {
        $this->calculateAllowances();
    }

    public function updatedFuel()
    {
        $this->calculateAllowances();
    }

    public function updatedTollGates()
    {
        $this->calculateAllowances();
    }

    public function calculateDays()
    {
        if ($this->trip_start_date && $this->trip_end_date) {
            $start = \Carbon\Carbon::parse($this->trip_start_date);
            $end = \Carbon\Carbon::parse($this->trip_end_date);
            $this->number_of_days = $start->diffInDays($end) + 1;
        } else {
            $this->number_of_days = 0;
        }
    }

    public function loadConfigRates()
    {
        if (! $this->grade_band_id) {
            $this->resetRates();

            return;
        }

        // Find active config for the selected grade band
        $config = TsAllowanceConfig::where('grade_band_id', $this->grade_band_id)
            ->where('status', 'ACTIVE')
            ->first();

        if ($config) {
            $this->selectedConfig = $config;
            $this->out_of_station_rate = $config->out_of_station_subsistence_rate ?? 0;
            $this->overnight_rate = $config->overnight_allowance_rate ?? 0;
            $this->bed_rate = $config->bed_allowance_rate ?? 0;
            $this->breakfast_rate = $config->breakfast_rate ?? 0;
            $this->lunch_rate = $config->lunch_rate ?? 0;
            $this->dinner_rate = $config->dinner_rate ?? 0;
            $this->mileage_rate_per_km = $config->mileage_rate_per_km ?? 0;
        } else {
            $this->resetRates();
            $this->warning('No active T&S configuration found for the selected grade band.');
        }
    }

    public function resetRates()
    {
        $this->selectedConfig = null;
        $this->out_of_station_rate = 0;
        $this->overnight_rate = 0;
        $this->bed_rate = 0;
        $this->breakfast_rate = 0;
        $this->lunch_rate = 0;
        $this->dinner_rate = 0;
        $this->mileage_rate_per_km = 0;
    }

    public function calculateMealDays($option, $isBreakfastOrLunch = true)
    {
        $days = $this->number_of_days ?: 0;

        // For breakfast and lunch - can have all days
        // For dinner - excludes last day
        $maxDays = $isBreakfastOrLunch ? $days : max(0, $days - 1);
        $middleDays = max(0, $days - 2); // Excluding first and last day

        return match ($option) {
            'all' => $maxDays,
            'first_day_only' => min(1, $maxDays),
            'last_day_only' => $isBreakfastOrLunch ? min(1, $maxDays) : 0, // Dinner can't be on last day
            'first_and_last' => $isBreakfastOrLunch ? min(2, $maxDays) : min(1, $maxDays), // Dinner excludes last
            'middle_days_only' => min($middleDays, $maxDays),
            'excluding_first_day' => max(0, $maxDays - 1),
            'excluding_last_day' => $isBreakfastOrLunch ? max(0, $maxDays - 1) : $maxDays, // For dinner, last day already excluded
            default => $maxDays,
        };
    }

    public function calculateNights($option)
    {
        $days = $this->number_of_days ?: 0;
        // Nights = days - 1 (you don't stay overnight on the last day)
        $maxNights = max(0, $days - 1);
        $middleNights = max(0, $maxNights - 1); // Excluding first night

        return match ($option) {
            'all' => $maxNights,
            'first_night_only' => min(1, $maxNights),
            'last_night_only' => min(1, $maxNights),
            'first_and_last_night' => min(2, $maxNights),
            'middle_nights_only' => $middleNights,
            'excluding_first_night' => max(0, $maxNights - 1),
            'excluding_last_night' => max(0, $maxNights - 1),
            default => $maxNights,
        };
    }

    public function calculateAllowances()
    {
        $days = $this->number_of_days ?: 0;

        // Out of station is ALWAYS multiplied by 1 (not by days)
        $this->out_of_station_subsistence = $this->out_of_station_rate * 1;

        // Overnight allowance - excludes end date, so (days - 1)
        // If only 1 day trip, overnight = 0 (no overnight stay)
        $overnightDays = max(0, $days - 1);
        $this->overnight_allowance = $this->overnight_rate * $overnightDays;

        // Optional allowances - only calculate if checkbox is checked
        // Bed Allowance - calculate nights based on option
        $this->bed_nights = $this->include_bed_allowance ? $this->calculateNights($this->bed_option) : 0;
        $this->bed_allowance = $this->include_bed_allowance ? ($this->bed_rate * $this->bed_nights) : 0;

        // Breakfast - calculate days based on option
        $this->breakfast_days = $this->include_breakfast ? $this->calculateMealDays($this->breakfast_option, true) : 0;
        $this->breakfast = $this->include_breakfast ? ($this->breakfast_rate * $this->breakfast_days) : 0;

        // Lunch - calculate days based on option
        $this->lunch_days = $this->include_lunch ? $this->calculateMealDays($this->lunch_option, true) : 0;
        $this->lunch = $this->include_lunch ? ($this->lunch_rate * $this->lunch_days) : 0;

        // Dinner - special rule: no dinner on the last day, so max is (days - 1)
        // Calculate days based on option
        $this->dinner_days = $this->include_dinner ? $this->calculateMealDays($this->dinner_option, false) : 0;
        $this->dinner = $this->include_dinner ? ($this->dinner_rate * $this->dinner_days) : 0;

        // Transport - Fuel and Toll Gates are manually entered by user (not calculated)
        // $this->fuel and $this->toll_gates are already set by user input

        // Mileage is calculated based on distance
        $distance = $this->mileage_estimated_distance ?: 0;
        $mileageTotal = $distance * $this->mileage_rate_per_km;

        // Calculate total
        $this->total_allowance = $this->out_of_station_subsistence
            + $this->overnight_allowance
            + $this->bed_allowance
            + $this->breakfast
            + $this->lunch
            + $this->dinner
            + floatval($this->fuel)
            + floatval($this->toll_gates)
            + $mileageTotal;
    }

    public function getGradeBands()
    {
        return GradeBand::where('is_active', true)->orderBy('code')->get();
    }

    public function getMealOptionsProperty()
    {
        return [
            ['id' => 'all', 'name' => 'All Days'],
            ['id' => 'first_day_only', 'name' => 'First Day Only'],
            ['id' => 'last_day_only', 'name' => 'Last Day Only'],
            ['id' => 'first_and_last', 'name' => 'First & Last Day Only'],
            ['id' => 'middle_days_only', 'name' => 'Middle Days Only (Excl. First & Last)'],
            ['id' => 'excluding_first_day', 'name' => 'Excluding First Day'],
            ['id' => 'excluding_last_day', 'name' => 'Excluding Last Day'],
        ];
    }

    public function getNightOptionsProperty()
    {
        return [
            ['id' => 'all', 'name' => 'All Nights'],
            ['id' => 'first_night_only', 'name' => 'First Night Only'],
            ['id' => 'last_night_only', 'name' => 'Last Night Only'],
            ['id' => 'first_and_last_night', 'name' => 'First & Last Night Only'],
            ['id' => 'middle_nights_only', 'name' => 'Middle Nights Only (Excl. First)'],
            ['id' => 'excluding_first_night', 'name' => 'Excluding First Night'],
            ['id' => 'excluding_last_night', 'name' => 'Excluding Last Night'],
        ];
    }

    public function getallowances()
    {
        return $this->tsallowanceService->getallowancesbyapplicant(Auth::user()->id, $this->year, $this->search);
    }

    public function edit($id)
    {
        $allowance = $this->tsallowanceService->getallowance($id);
        $this->id = $id;
        $this->job_title = $allowance->job_title;
        $this->grade_band_id = $allowance->grade_band_id;
        $this->trip_start_date = $allowance->trip_start_date?->format('Y-m-d');
        $this->trip_end_date = $allowance->trip_end_date?->format('Y-m-d');
        $this->reason_for_allowances = $allowance->reason_for_allowances;
        $this->existing_attachment_path = $allowance->trip_attachment_path;
        $this->number_of_days = $allowance->number_of_days;
        $this->out_of_station_subsistence = $allowance->out_of_station_subsistence;
        $this->overnight_allowance = $allowance->overnight_allowance;
        $this->bed_allowance = $allowance->bed_allowance;
        $this->breakfast = $allowance->breakfast;
        $this->lunch = $allowance->lunch;
        $this->dinner = $allowance->dinner;
        $this->fuel = $allowance->fuel;
        $this->toll_gates = $allowance->toll_gates;
        $this->mileage_estimated_distance = $allowance->mileage_estimated_distance;

        // Set checkboxes based on whether values are > 0
        $this->include_bed_allowance = $allowance->bed_allowance > 0;
        $this->include_breakfast = $allowance->breakfast > 0;
        $this->include_lunch = $allowance->lunch > 0;
        $this->include_dinner = $allowance->dinner > 0;

        // Load bed and meal options
        $this->bed_option = $allowance->bed_option ?? 'all';
        $this->bed_nights = $allowance->bed_nights ?? 0;
        $this->breakfast_option = $allowance->breakfast_option ?? 'all';
        $this->lunch_option = $allowance->lunch_option ?? 'all';
        $this->dinner_option = $allowance->dinner_option ?? 'all';
        $this->breakfast_days = $allowance->breakfast_days ?? 0;
        $this->lunch_days = $allowance->lunch_days ?? 0;
        $this->dinner_days = $allowance->dinner_days ?? 0;

        // Load the rates for the grade band
        $this->loadConfigRates();

        // Recalculate total
        $this->calculateAllowances();

        $this->modal = true;
    }

    public function save()
    {
        $rules = [
            'job_title' => 'required|string',
            'grade_band_id' => 'required|exists:grade_bands,id',
            'trip_start_date' => 'required|date',
            'trip_end_date' => 'required|date|after_or_equal:trip_start_date',
            'reason_for_allowances' => 'required|string',
            'trip_attachment' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ];

        $this->validate($rules);

        // Recalculate before saving
        $this->calculateDays();
        $this->calculateAllowances();

        if ($this->id != null) {
            $this->update();
        } else {
            $this->create();
        }

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'job_title',
            'grade_band_id',
            'trip_start_date',
            'trip_end_date',
            'reason_for_allowances',
            'trip_attachment',
            'existing_attachment_path',
            'number_of_days',
            'out_of_station_rate',
            'overnight_rate',
            'bed_rate',
            'breakfast_rate',
            'lunch_rate',
            'dinner_rate',
            'mileage_rate_per_km',
            'include_bed_allowance',
            'include_breakfast',
            'include_lunch',
            'include_dinner',
            'bed_option',
            'bed_nights',
            'breakfast_option',
            'lunch_option',
            'dinner_option',
            'breakfast_days',
            'lunch_days',
            'dinner_days',
            'out_of_station_subsistence',
            'overnight_allowance',
            'bed_allowance',
            'breakfast',
            'lunch',
            'dinner',
            'fuel',
            'toll_gates',
            'mileage_estimated_distance',
            'total_allowance',
            'selectedConfig',
            'id',
        ]);
        $this->bed_option = 'all';
        $this->breakfast_option = 'all';
        $this->lunch_option = 'all';
        $this->dinner_option = 'all';
    }

    public function create()
    {
        // Get the grade band code to store as 'grade' for historical reference
        $gradeBand = GradeBand::find($this->grade_band_id);

        // Handle file upload
        $attachmentPath = null;
        if ($this->trip_attachment) {
            $attachmentPath = $this->trip_attachment->store('ts-allowance-attachments', 'public');
        }

        $response = $this->tsallowanceService->createallowance([
            'job_title' => $this->job_title,
            'grade' => $gradeBand?->code, // Store grade band code for display
            'grade_band_id' => $this->grade_band_id,
            'trip_start_date' => $this->trip_start_date,
            'trip_end_date' => $this->trip_end_date,
            'reason_for_allowances' => $this->reason_for_allowances,
            'trip_attachment_path' => $attachmentPath,
            'number_of_days' => $this->number_of_days,
            'out_of_station_subsistence' => $this->out_of_station_subsistence,
            'overnight_allowance' => $this->overnight_allowance,
            'bed_allowance' => $this->bed_allowance,
            'bed_option' => $this->bed_option,
            'bed_nights' => $this->bed_nights,
            'breakfast' => $this->breakfast,
            'breakfast_option' => $this->breakfast_option,
            'breakfast_days' => $this->breakfast_days,
            'lunch' => $this->lunch,
            'lunch_option' => $this->lunch_option,
            'lunch_days' => $this->lunch_days,
            'dinner' => $this->dinner,
            'dinner_option' => $this->dinner_option,
            'dinner_days' => $this->dinner_days,
            'fuel' => $this->fuel,
            'toll_gates' => $this->toll_gates,
            'mileage_estimated_distance' => $this->mileage_estimated_distance,
            'balance_due' => $this->total_allowance,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->modal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function update()
    {
        // Get the grade band code to store as 'grade' for historical reference
        $gradeBand = GradeBand::find($this->grade_band_id);

        // Handle file upload
        $attachmentPath = $this->existing_attachment_path;
        if ($this->trip_attachment) {
            $attachmentPath = $this->trip_attachment->store('ts-allowance-attachments', 'public');
        }

        $response = $this->tsallowanceService->updateallowance($this->id, [
            'job_title' => $this->job_title,
            'grade' => $gradeBand?->code, // Store grade band code for display
            'grade_band_id' => $this->grade_band_id,
            'trip_start_date' => $this->trip_start_date,
            'trip_end_date' => $this->trip_end_date,
            'reason_for_allowances' => $this->reason_for_allowances,
            'trip_attachment_path' => $attachmentPath,
            'number_of_days' => $this->number_of_days,
            'out_of_station_subsistence' => $this->out_of_station_subsistence,
            'overnight_allowance' => $this->overnight_allowance,
            'bed_allowance' => $this->bed_allowance,
            'bed_option' => $this->bed_option,
            'bed_nights' => $this->bed_nights,
            'breakfast' => $this->breakfast,
            'breakfast_option' => $this->breakfast_option,
            'breakfast_days' => $this->breakfast_days,
            'lunch' => $this->lunch,
            'lunch_option' => $this->lunch_option,
            'lunch_days' => $this->lunch_days,
            'dinner' => $this->dinner,
            'dinner_option' => $this->dinner_option,
            'dinner_days' => $this->dinner_days,
            'fuel' => $this->fuel,
            'toll_gates' => $this->toll_gates,
            'mileage_estimated_distance' => $this->mileage_estimated_distance,
            'balance_due' => $this->total_allowance,
        ]);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->modal = false;
        } else {
            $this->error($response['message']);
        }
    }

    public function delete($id)
    {
        $response = $this->tsallowanceService->deleteallowance($id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function submit($id)
    {
        $response = $this->tsallowanceService->submitallowance($id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'application_number', 'label' => 'Application #'],
            ['key' => 'full_name', 'label' => 'Applicant'],
            ['key' => 'trip_start_date', 'label' => 'Start Date'],
            ['key' => 'trip_end_date', 'label' => 'End Date'],
            ['key' => 'reason_for_allowances', 'label' => 'Reason'],
            ['key' => 'balance_due', 'label' => 'Amount'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'action', 'label' => ''],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.ts-allowances', [
            'breadcrumbs' => $this->breadcrumbs,
            'allowances' => $this->getallowances(),
            'headers' => $this->headers(),
            'gradeBands' => $this->getGradeBands(),
        ]);
    }
}
