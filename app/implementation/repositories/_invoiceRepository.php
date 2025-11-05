<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\iexchangerateInterface;
use App\Interfaces\repositories\iinventoryitemInterface;
use App\Interfaces\repositories\invoiceInterface;
use App\Interfaces\repositories\isuspenseInterface;
use App\Models\Invoice;
use App\Models\WorkshopInvoice;
use App\Models\Workshoporder;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Mary\Traits\Toast;

class _invoiceRepository implements invoiceInterface
{
  
    /**
     * Create a new class instance.
     */
    protected $invoice;
    protected $workshopinvoice;
    protected $workshoporder;
    protected $currencyrepo;

    protected $customerrepo;

    protected $inventoryitemrepo;

    protected $exchangeraterepo;

    protected $suspenserepo;

    public function __construct(Invoice $invoice, WorkshopInvoice $workshopinvoice, Workshoporder $workshoporder, icurrencyInterface $currencyrepo, icustomerInterface $customerrepo, iinventoryitemInterface $inventoryitemrepo, iexchangerateInterface $exchangeraterepo, isuspenseInterface $suspenserepo)
    {
        $this->invoice = $invoice;
        $this->currencyrepo = $currencyrepo;
        $this->customerrepo = $customerrepo;
        $this->inventoryitemrepo = $inventoryitemrepo;
        $this->exchangeraterepo = $exchangeraterepo;
        $this->suspenserepo = $suspenserepo;
        $this->workshopinvoice = $workshopinvoice;
        $this->workshoporder = $workshoporder;
    }

    public function getInvoices($fromDate, $toDate, $status, array $inventoryItems, array $currencyItems)
    {
        return $this->invoice->with('inventoryitem', 'currency', 'customer', 'receipts.suspense.onlinepayment', 'receipts.suspense.banktransaction')
            ->where('created_at', '>=', $fromDate)->where('created_at', '<=', $toDate)
            ->where('status', $status)
            ->whereIn('inventoryitem_id', $inventoryItems)
            ->whereIn('currency_id', $currencyItems)
            ->get();
    }

    public function getInvoicespaginated($fromDate, $toDate, $status, array $inventoryItems, array $currencyItems)
    {
        return $this->invoice->with('inventoryitem', 'currency', 'customer', 'receipts')
            ->where('created_at', '>=', $fromDate)->where('created_at', '<=', $toDate)
            ->where('status', $status)
            ->whereIn('inventoryitem_id', $inventoryItems)->whereIn('currency_id', $currencyItems)
            ->paginate(500);
    }

    public function getInvoiceDetails($invoiceId)
    {
        return $this->invoice->with('inventoryitem', 'currency', 'customer', 'receipts')->find($invoiceId);
    }

    public function getInvoiceByInvoiceNumber($invoiceNumber)
    {
        return $this->invoice->with('inventoryitem', 'currency', 'customer', 'receipts')->where('invoicenumber', $invoiceNumber)->first();
    }

    public function getcomparisonreport($firstfromDate, $firsttoDate, $secondfromDate, $secondtoDate, $status, array $inventoryItems, array $currencyItems)
    {
        $firstdata = $this->invoice->with('inventoryitem', 'currency', 'customer')->where('created_at', '>=', $firstfromDate)->where('created_at', '<=', $firsttoDate)
            ->where('status', $status)
            ->whereIn('inventoryitem_id', $inventoryItems)->whereIn('currency_id', $currencyItems)
            ->get();
        $seconddata = $this->invoice->with('inventoryitem', 'currency', 'customer')->where('created_at', '>=', $secondfromDate)->where('created_at', '<=', $secondtoDate)
            ->where('status', $status)
            ->whereIn('inventoryitem_id', $inventoryItems)->whereIn('currency_id', $currencyItems)
            ->get();

        return ['firstdata' => $firstdata, 'seconddata' => $seconddata];
    }

    public function getquarterlyreport($year, $status, array $inventoryItems, array $currencyItems)
    {
        $date = Carbon::parse($year)->startOfYear();
        $firstdata = $this->invoice->with('inventoryitem', 'currency')->where('created_at', '>=', $date)->where('created_at', '<=', $date->copy()->addMonths(3))
            ->where('status', $status)
            ->whereIn('inventoryitem_id', $inventoryItems)->whereIn('currency_id', $currencyItems)
            ->get();
        $seconddata = $this->invoice->with('inventoryitem', 'currency')->where('created_at', '>=', $date->copy()->addMonths(3))->where('created_at', '<=', $date->copy()->addMonths(6))
            ->where('status', $status)
            ->whereIn('inventoryitem_id', $inventoryItems)->whereIn('currency_id', $currencyItems)
            ->get();
        $thirddata = $this->invoice->with('inventoryitem', 'currency')->where('created_at', '>=', $date->copy()->addMonths(6))->where('created_at', '<=', $date->copy()->addMonths(9))
            ->where('status', $status)
            ->whereIn('inventoryitem_id', $inventoryItems)->whereIn('currency_id', $currencyItems)
            ->get();
        $fourthdata = $this->invoice->with('inventoryitem', 'currency')->where('created_at', '>=', $date->copy()->addMonths(9))->where('created_at', '<=', $date->copy()->addMonths(12))
            ->where('status', $status)
            ->whereIn('inventoryitem_id', $inventoryItems)->whereIn('currency_id', $currencyItems)
            ->get();

        return ['firstdata' => $firstdata, 'seconddata' => $seconddata, 'thirddata' => $thirddata, 'fourthdata' => $fourthdata];
    }

    public function createInvoice($data)
    {
        try {
            if (strtoupper($data['currency']) === 'ZWL') {
                return ['status' => 'ERROR', 'message' => 'Invoice generation in ZWL is suspended', 'data' => null];
            }
            $check = $this->invoice->where('invoicenumber', $data['invoicenumber'])->first();
            if ($check) {
                return ['status' => 'ERROR', 'message' => 'Invoice number already exists', 'data' => null];
            }
            $customer = $this->customerrepo->getCustomerByRegnumber($data['regnumber']);
            if (! $customer) {
                return ['status' => 'ERROR', 'message' => 'Regnumber not found', 'data' => null];
            }
            $inventoryitem = $this->inventoryitemrepo->getInventoryItemByItemcode($data['itemcode']);
            if (! $inventoryitem) {
                return ['status' => 'ERROR', 'message' => 'Inventory item not found', 'data' => null];
            }
            $currency = $this->currencyrepo->getCurrencyByCode($data['currency']);
            if (! $currency) {
                return ['status' => 'ERROR', 'message' => 'Currency not found', 'data' => null];
            }
            $exchangerate = $this->exchangeraterepo->getexchangeratebycurrency($currency->id);
            if (! $exchangerate) {
                return ['status' => 'ERROR', 'message' => 'Exchange rate not configured', 'data' => null];
            }
            $inventoryItemId = $inventoryitem->id;
            if (Str::startsWith($data['invoicenumber'], 'INAPP') && $inventoryitem->name != 'APP') {
                $newinventoryitem = $this->inventoryitemrepo->getInventoryItemByItemcode('APP');
                $inventoryItemId = $newinventoryitem->id;
            } elseif (Str::startsWith($data['invoicenumber'], 'INTP-BDS') && $inventoryitem->name != 'BIDBOARD') {
                $newinventoryitem = $this->inventoryitemrepo->getInventoryItemByItemcode('BIDBOND');
                $inventoryItemId = $newinventoryitem->id;
            } elseif (Str::startsWith($data['invoicenumber'], 'INTP-EST') && $inventoryitem->name != 'ESTABLISHMENT') {
                $newinventoryitem = $this->inventoryitemrepo->getInventoryItemByItemcode('ESTABLISHMENT');
                $inventoryItemId = $newinventoryitem->id;
            } elseif (Str::startsWith($data['invoicenumber'], 'INTP-SPOC') && $inventoryitem->name != 'SPOC') {
                $newinventoryitem = $this->inventoryitemrepo->getInventoryItemByItemcode('SPOC');
                $inventoryItemId = $newinventoryitem->id;
            } elseif (Str::startsWith($data['invoicenumber'], 'INKP') && $inventoryitem->name != 'KEYPAIR') {
                $newinventoryitem = $this->inventoryitemrepo->getInventoryItemByItemcode('KEYPAIR');
                $inventoryItemId = $newinventoryitem->id;
            }
            $newarray = [
                'customer_id' => $customer->id,
                'inventoryitem_id' => $inventoryItemId,
                'currency_id' => $currency->id,
                'status' => 'PENDING',
                'invoicenumber' => $data['invoicenumber'],
                'amount' => $data['amount'],
                'invoicesource' => $data['invoicesource'],
                'invoicetype' => $inventoryitem->requiretender == 'Y' ? 'Tender' : 'Non-Tender',
                'description' => $inventoryitem->name,
                'exchangerate_id' => $exchangerate->id,
            ];

            $this->invoice->create($newarray);

            return ['status' => 'SUCCESS', 'message' => 'Invoice successfully created', 'data' => $this->invoice->find($this->invoice->id)];
        } catch (\Exception $e) {
            return ['status' => 'ERROR', 'message' => $e->getMessage(), 'data' => null];
        }
    }

    public function markInvoiceAsPaid($invoiceNumber)
    {
        $invoice = $this->invoice->where('invoicenumber', $invoiceNumber)->first();
        if (! $invoice) {
            return ['status' => 'ERROR', 'message' => 'Invoice not found', 'data' => null];
        }

        $invoice->status = 'PAID';
        $invoice->settled_at = Carbon::now();
        $invoice->save();

        return ['status' => 'SUCCESS', 'message' => 'Invoice successfully marked as paid', 'data' => $invoice];
    }

    public function updateInvoice($data)
    {
        $invoice = $this->invoice->with('currency')->where('invoice_number', $data['invoicenumber'])->first();
        if (! $invoice) {
            return ['status' => 'ERROR', 'message' => 'Invoice not found', 'data' => null];
        }
        if ($invoice->status == 'PAID') {
            return ['status' => 'ERROR', 'message' => 'Invoice already paid', 'data' => null];
        }
        if ($invoice->currency->name != $data['currency']) {
            $currency = $this->currencyrepo->getCurrencyByCode($data['currency']);
            if (! $currency) {
                return ['status' => 'ERROR', 'message' => 'Currency not found', 'data' => null];
            }
            $invoice->currency_id = $currency->id;
            $invoice->exchangerate_id = $this->exchangeraterepo->getexchangeratebycurrency($data['currency'])->id;
        }

        $invoice->amount = number_format($data['amount'], 2);
        $invoice->status = $data['status'];
        $invoice->save();

        return ['status' => 'SUCCESS', 'message' => 'Invoice successfully updated', 'data' => $invoice];

    }

    public function deleteInvoice($invoicenumber)
    {
        $invoice = $this->invoice->where('invoice_number', $invoicenumber)->first();
        if (! $invoice) {
            return ['status' => 'ERROR', 'message' => 'Invoice not found', 'data' => null];
        }
        if ($invoice->status == 'PAID') {
            return ['status' => 'ERROR', 'message' => 'Invoice already settled', 'data' => null];
        }
        $invoice->delete();

        return ['status' => 'SUCCESS', 'message' => 'Invoice successfully deleted', 'data' => null];
    }

    public function getInvoicebyCustomer($customerId)
    {
        return $this->invoice->with('inventoryitem', 'currency')->where('customer_id', $customerId)->get();
    }

    public function settleInvoice($invoicenumber, $receiptnumber = null)
    {
        $invoice = $this->invoice->with('customer', 'inventoryitem', 'currency')->where('invoicenumber', $invoicenumber)->first();
        if (! $invoice) {
            return ['status' => 'error', 'message' => 'Invoice not found', 'data' => null];
        }
        if ($invoice->status == 'PAID') {
            return ['status' => 'error', 'message' => 'Invoice already settled', 'data' => null];
        }
        $walletbalance = $this->suspenserepo->getwalletbalance($invoice->customer->regnumber, $invoice->inventoryitem->type, $invoice->currency->name);
        if ($walletbalance < $invoice->amount) {
            return ['status' => 'error', 'message' => 'Insufficient balance', 'data' => null];
        }
        if ($invoice->invoicesource == 'MANUAL') {
            $receiptnumber = 'RPT'.date('Y').$invoice->id.rand(1000, 99999);
        }
        $response = $this->suspenserepo->deductwallet($invoice->customer->regnumber, $invoice->id, $invoice->inventoryitem->type, $invoice->currency->name, $invoice->amount, $receiptnumber);
        if ($response['status'] == 'error') {
            return $response;
        }
        $invoice->status = 'PAID';
        $invoice->save();

        return ['status' => 'success', 'message' => 'Invoice successfully settled', 'data' => $invoice];

    }
    public function settleworkshopinvoice($invoicenumber){
        $invoice = $this->workshopinvoice->with('customer', 'currency')->where('invoicenumber', $invoicenumber)->first();
        if (! $invoice) {
            return ['status' => 'error', 'message' => 'Invoice not found', 'data' => null];
        }
        if ($invoice->status == 'PAID') {
            return ['status' => 'error', 'message' => 'Invoice already settled', 'data' => null];
        }
        $walletbalance = $this->suspenserepo->getwalletbalance($invoice->customer->regnumber, 'NONREFUNDABLE', $invoice->currency->name);
        $balance = $invoice->cost - str_replace(',', '', $walletbalance);
        if ($balance > 0) {
            return ['status' => 'error', 'message' => 'Insufficient wallet please top up your wallet with '.$invoice->currency->name.' '.$balance.', required to settle invoice', 'data' => null];
        }
      
            $receiptnumber = 'RPT'.date('Y').$invoice->id.rand(1000, 99999);
        
        $response = $this->suspenserepo->deductwallet($invoice->customer->regnumber, $invoice->id, 'NONREFUNDABLE', $invoice->currency->name, $invoice->cost, $receiptnumber);
        if ($response['status'] == 'error') {
            return $response;
        }
        $invoice->status = 'PAID';
        $invoice->save();
        $order = $this->workshoporder->where('invoicenumber', $invoicenumber)->first();
        if ($order) {
            $order->status = 'PAID';
            $order->save();
        }
        return ['status' => 'success', 'message' => 'Invoice successfully settled', 'data' => $invoice];
    }
}
