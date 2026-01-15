<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\ipaymentrequisitionInterface;
use App\Interfaces\repositories\istaffwelfareloanInterface;
use App\Interfaces\repositories\itsallowanceInterface;
use App\Interfaces\services\ipaymentvoucherService;
use Livewire\Component;
use Mary\Traits\Toast;

class PaymentVouchershow extends Component
{
    use Toast;

    public $breadcrumbs = [];

    public $uuid;

    public $voucher;

    public $viewItemModal = false;

    public $viewedItemDetails = null;

    public $viewedItemSourceType = null;

    public $viewedItemLineId = null;

    public $selectedTab = 'details';

    protected $paymentvoucherService;

    protected $paymentrequisitionrepo;

    protected $tsallowancerepo;

    protected $staffwelfareloanrepo;

    public function boot(
        ipaymentvoucherService $paymentvoucherService,
        ipaymentrequisitionInterface $paymentrequisitionrepo,
        itsallowanceInterface $tsallowancerepo,
        istaffwelfareloanInterface $staffwelfareloanrepo
    ) {
        $this->paymentvoucherService = $paymentvoucherService;
        $this->paymentrequisitionrepo = $paymentrequisitionrepo;
        $this->tsallowancerepo = $tsallowancerepo;
        $this->staffwelfareloanrepo = $staffwelfareloanrepo;
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

    public function viewItemDetails($itemId)
    {
        $item = \App\Models\PaymentVoucherItem::with('paymentVoucher')->find($itemId);

        if (! $item) {
            $this->error('Item not found');

            return;
        }

        $this->viewedItemSourceType = $item->source_type;
        $this->viewedItemDetails = null;
        $this->viewedItemLineId = $item->source_line_id;

        try {
            if ($this->viewedItemSourceType === 'PAYMENT_REQUISITION') {
                $pr = \App\Models\PaymentRequisition::find($item->source_id);
                if ($pr && $pr->uuid) {
                    $this->viewedItemDetails = $this->paymentrequisitionrepo->getpaymentrequisitionbyuuid($pr->uuid);
                }
            } elseif ($this->viewedItemSourceType === 'TNS') {
                $ts = \App\Models\TsAllowance::find($item->source_id);
                if ($ts && $ts->uuid) {
                    $this->viewedItemDetails = $this->tsallowancerepo->getallowancebyuuid($ts->uuid);
                }
            } elseif ($this->viewedItemSourceType === 'STAFF_WELFARE') {
                $loan = \App\Models\StaffWelfareLoan::find($item->source_id);
                if ($loan && $loan->uuid) {
                    $this->viewedItemDetails = $this->staffwelfareloanrepo->getloanbyuuid($loan->uuid);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error loading item details: '.$e->getMessage());
            $this->error('Failed to load item details');

            return;
        }

        if (! $this->viewedItemDetails) {
            $this->error('Item details not found');

            return;
        }

        $this->viewItemModal = true;
    }

    public function closeViewItemModal()
    {
        $this->viewItemModal = false;
        $this->viewedItemDetails = null;
        $this->viewedItemSourceType = null;
        $this->viewedItemLineId = null;
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
