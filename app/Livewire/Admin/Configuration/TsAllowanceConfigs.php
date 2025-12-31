<?php

namespace App\Livewire\Admin\Configuration;

use App\Interfaces\services\itsallowanceconfigService;
use App\Models\Currency;
use App\Models\GradeBand;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class TsAllowanceConfigs extends Component
{
    use Toast, WithPagination;

    public $breadcrumbs = [];

    public $modal = false;

    public $id;

    // Form fields
    public $grade_band_id;

    public $currency_id;

    public $out_of_station_subsistence_rate = 0;

    public $overnight_allowance_rate = 0;

    public $bed_allowance_rate = 0;

    public $breakfast_rate = 0;

    public $lunch_rate = 0;

    public $dinner_rate = 0;

    public $fuel_rate = 0;

    public $toll_gate_rate = 0;

    public $mileage_rate_per_km = 0;

    public $effective_from;

    public $status = 'DRAFT';

    public $search = '';

    protected $tsallowanceconfigService;

    public function boot(itsallowanceconfigService $tsallowanceconfigService)
    {
        $this->tsallowanceconfigService = $tsallowanceconfigService;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'T&S Allowance Configuration'],
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function headers(): array
    {
        return [
            ['key' => 'grade_band', 'label' => 'Grade Band'],
            ['key' => 'currency', 'label' => 'Currency'],
            ['key' => 'subsistence', 'label' => 'Subsistence'],
            ['key' => 'overnight', 'label' => 'Overnight'],
            ['key' => 'effective_from', 'label' => 'Effective From'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'action', 'label' => ''],
        ];
    }

    public function getConfigs()
    {
        return $this->tsallowanceconfigService->getconfigs($this->search);
    }

    public function getGradeBands()
    {
        return GradeBand::orderBy('code')->get();
    }

    public function getCurrencies()
    {
        return Currency::orderBy('name')->get();
    }

    public function save()
    {
        $this->validate([
            'grade_band_id' => 'required|exists:grade_bands,id',
            'currency_id' => 'required|exists:currencies,id',
            'out_of_station_subsistence_rate' => 'nullable|numeric|min:0',
            'overnight_allowance_rate' => 'nullable|numeric|min:0',
            'bed_allowance_rate' => 'nullable|numeric|min:0',
            'breakfast_rate' => 'nullable|numeric|min:0',
            'lunch_rate' => 'nullable|numeric|min:0',
            'dinner_rate' => 'nullable|numeric|min:0',
            'fuel_rate' => 'nullable|numeric|min:0',
            'toll_gate_rate' => 'nullable|numeric|min:0',
            'mileage_rate_per_km' => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        $data = [
            'grade_band_id' => $this->grade_band_id,
            'currency_id' => $this->currency_id,
            'out_of_station_subsistence_rate' => $this->out_of_station_subsistence_rate,
            'overnight_allowance_rate' => $this->overnight_allowance_rate,
            'bed_allowance_rate' => $this->bed_allowance_rate,
            'breakfast_rate' => $this->breakfast_rate,
            'lunch_rate' => $this->lunch_rate,
            'dinner_rate' => $this->dinner_rate,
            'fuel_rate' => $this->fuel_rate,
            'toll_gate_rate' => $this->toll_gate_rate,
            'mileage_rate_per_km' => $this->mileage_rate_per_km,
            'effective_from' => $this->effective_from,
            'status' => $this->status,
        ];

        if ($this->id) {
            $response = $this->tsallowanceconfigService->updateconfig($this->id, $data);
        } else {
            $response = $this->tsallowanceconfigService->createconfig($data);
        }

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->modal = false;
            $this->reset([
                'id',
                'grade_band_id',
                'currency_id',
                'out_of_station_subsistence_rate',
                'overnight_allowance_rate',
                'bed_allowance_rate',
                'breakfast_rate',
                'lunch_rate',
                'dinner_rate',
                'fuel_rate',
                'toll_gate_rate',
                'mileage_rate_per_km',
                'effective_from',
                'status',
            ]);
        } else {
            $this->error($response['message']);
        }
    }

    public function edit($id)
    {
        $config = $this->tsallowanceconfigService->getconfig($id);
        if (! $config) {
            $this->error('Configuration not found.');

            return;
        }

        $this->id = $id;
        $this->grade_band_id = $config->grade_band_id;
        $this->currency_id = $config->currency_id;
        $this->out_of_station_subsistence_rate = $config->out_of_station_subsistence_rate;
        $this->overnight_allowance_rate = $config->overnight_allowance_rate;
        $this->bed_allowance_rate = $config->bed_allowance_rate;
        $this->breakfast_rate = $config->breakfast_rate;
        $this->lunch_rate = $config->lunch_rate;
        $this->dinner_rate = $config->dinner_rate;
        $this->fuel_rate = $config->fuel_rate;
        $this->toll_gate_rate = $config->toll_gate_rate;
        $this->mileage_rate_per_km = $config->mileage_rate_per_km;
        $this->effective_from = $config->effective_from?->format('Y-m-d');
        $this->status = $config->status;
        $this->modal = true;
    }

    public function delete($id)
    {
        $response = $this->tsallowanceconfigService->deleteconfig($id);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function approve($id)
    {
        $data = [
            'approved_by' => Auth::user()->id,
            'approved_at' => now(),
        ];

        $response = $this->tsallowanceconfigService->approveconfig($id, $data);
        if ($response['status'] == 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function archive($id)
    {
        $data = [
            'status' => 'ARCHIVED',
        ];

        $response = $this->tsallowanceconfigService->updateconfig($id, $data);
        if ($response['status'] == 'success') {
            $this->success('Configuration archived successfully');
        } else {
            $this->error($response['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.configuration.ts-allowance-configs', [
            'configs' => $this->getConfigs(),
            'gradeBands' => $this->getGradeBands(),
            'currencies' => $this->getCurrencies(),
            'headers' => $this->headers(),
        ]);
    }
}
