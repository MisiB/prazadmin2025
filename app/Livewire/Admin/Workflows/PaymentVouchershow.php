<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\services\ipaymentvoucherService;
use Livewire\Component;
use Mary\Traits\Toast;

class PaymentVouchershow extends Component
{
    use Toast;

    public $breadcrumbs = [];

    public $uuid;

    public $voucher;

    protected $paymentvoucherService;

    public function boot(ipaymentvoucherService $paymentvoucherService)
    {
        $this->paymentvoucherService = $paymentvoucherService;
    }

    public function mount($uuid)
    {
        $this->uuid = $uuid;
        $this->voucher = $this->paymentvoucherService->getvoucherbyuuid($uuid);

        if (! $this->voucher) {
            $this->error('Payment Voucher not found');

            return redirect()->route('admin.paymentvouchers');
        }

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Payment Vouchers', 'link' => route('admin.paymentvouchers')],
            ['label' => $this->voucher->voucher_number],
        ];
    }

    public function render()
    {
        $voucher = $this->voucher;

        // Add currency to each item so it's accessible in @scope closures
        if ($voucher && $voucher->items) {
            $voucher->items->each(function ($item) use ($voucher) {
                $item->voucher_currency = $voucher->currency;
            });
        }

        return view('livewire.admin.workflows.payment-vouchershow', [
            'breadcrumbs' => $this->breadcrumbs,
            'voucher' => $voucher,
        ]);
    }
}
