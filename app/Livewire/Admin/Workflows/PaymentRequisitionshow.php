<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\services\ipaymentrequisitionService;
use Livewire\Component;
use Mary\Traits\Toast;

class PaymentRequisitionshow extends Component
{
    use Toast;

    public $breadcrumbs = [];

    public $uuid;

    public $paymentrequisition;

    protected $paymentrequisitionService;

    public function boot(ipaymentrequisitionService $paymentrequisitionService)
    {
        $this->paymentrequisitionService = $paymentrequisitionService;
    }

    public function mount($uuid)
    {
        $this->uuid = $uuid;
        $this->paymentrequisition = $this->paymentrequisitionService->getpaymentrequisitionbyuuid($uuid);

        if (! $this->paymentrequisition) {
            $this->error('Payment Requisition not found');

            return redirect()->route('admin.paymentrequisitions');
        }

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Payment Requisitions', 'link' => route('admin.paymentrequisitions')],
            ['label' => $this->paymentrequisition->reference_number],
        ];
    }

    public function render()
    {
        return view('livewire.admin.workflows.payment-requisitionshow', [
            'breadcrumbs' => $this->breadcrumbs,
            'paymentrequisition' => $this->paymentrequisition,
        ]);
    }
}
