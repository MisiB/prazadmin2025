<?php

namespace App\Livewire\Admin\Configuration;

use App\Interfaces\services\ipaymentvoucherService;
use Livewire\Component;
use Mary\Traits\Toast;

class PaymentVoucherConfigs extends Component
{
    use Toast;

    public $breadcrumbs = [];

    public $modal = false;

    public $id;

    // Form fields
    public $config_key;

    public $config_value;

    public $description;

    public $search = '';

    protected $paymentvoucherService;

    public function boot(ipaymentvoucherService $paymentvoucherService)
    {
        $this->paymentvoucherService = $paymentvoucherService;
    }

    public function mount()
    {
        // Check if user has permission to view
        if (! auth()->user()->can('payment.voucher.config.manage')) {
            abort(403, 'You do not have permission to access payment voucher configuration.');
        }

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Payment Voucher Configuration'],
        ];
    }

    public function updatedSearch()
    {
        // Reset pagination if needed
    }

    public function headers(): array
    {
        return [
            ['key' => 'config_key', 'label' => 'Config Key'],
            ['key' => 'config_value', 'label' => 'Config Value'],
            ['key' => 'description', 'label' => 'Description'],
            ['key' => 'updated_by', 'label' => 'Updated By'],
            ['key' => 'updated_at', 'label' => 'Updated At'],
            ['key' => 'action', 'label' => ''],
        ];
    }

    public function getConfigs()
    {
        $configs = $this->paymentvoucherService->getallconfigs();

        if ($this->search) {
            $configs = $configs->filter(function ($config) {
                return stripos($config->config_key, $this->search) !== false
                    || stripos($config->config_value ?? '', $this->search) !== false
                    || stripos($config->description ?? '', $this->search) !== false;
            });
        }

        return $configs;
    }

    public function openModal($configId = null)
    {
        $this->id = $configId;
        if ($configId) {
            $configs = $this->getConfigs();
            $config = $configs->firstWhere('id', $configId);
            if ($config) {
                $this->config_key = $config->config_key;
                $this->config_value = $config->config_value;
                $this->description = $config->description;
            }
        } else {
            $this->reset(['config_key', 'config_value', 'description']);
        }
        $this->modal = true;
    }

    public function closeModal()
    {
        $this->modal = false;
        $this->reset(['id', 'config_key', 'config_value', 'description']);
    }

    public function save()
    {
        $this->validate([
            'config_key' => 'required|string|max:255',
            'config_value' => 'required|string',
            'description' => 'nullable|string|max:1000',
        ]);

        $response = $this->paymentvoucherService->setconfig(
            $this->config_key,
            $this->config_value,
            $this->description
        );

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->closeModal();
        } else {
            $this->error($response['message']);
        }
    }

    public function delete($id)
    {
        // Note: Delete functionality may need to be added to service/repository
        $this->error('Delete functionality not yet implemented');
    }

    public function render()
    {
        return view('livewire.admin.configuration.payment-voucher-configs', [
            'configs' => $this->getConfigs(),
            'headers' => $this->headers(),
        ]);
    }
}
