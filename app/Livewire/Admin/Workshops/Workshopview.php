<?php

namespace App\Livewire\Admin\Workshops;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\WithFileUploads;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\isuspenseInterface;
use App\implementation\services\_workshopService;   

class Workshopview extends Component
{
    use Toast;
    use WithFileUploads;

    protected $workshopService;
    protected $customerRepo;
    protected $suspenseRepo;

    public $id;
    public $selectedTab = "awaiting-tab";
    public $showCreateModal = false;
    public $showOrderModal = false;
    public $showEditOrderModal = false;
    public $showCreateDelegateModal = false;
    public $showEditDelegateModal = false;
    public $showPaymentModal = false; // New payment modal
    public $selectedOrder = null;
    public $selectedDelegate = null;
    public $showDelegateModal = false;
    public $delegatelist;

    // Payment form fields
    public $paymentAmount = 0;
    public $receiptNumber = '';
    public $selectedSuspenseId = null;
    public $availableSuspenses = [];

    // Delegate form fields
    public $delegateName;
    public $delegateSurname;
    public $delegateEmail;
    public $delegatePhone;
    public $delegateTitle;
    public $delegatenationalId;
    public $delegategender;
    public $delegatedesignation;

    // Invoice form fields
    public $name;
    public $surname;
    public $email;
    public $delegates;
    public $currencyId;
    public $cost;
    public $price;
    public ?int $customer_id = null;
    public $document;
    public $accounts;
    public $exchangerate_id;
    public $search = "";
    public $orderid;

    protected $rules = [
        'name' => 'required|string|max:255',
        'surname' => 'required|string|max:255',
        'email' => 'required|email',
        'delegates' => 'required|integer|min:1',
        'currencyId' => 'required|exists:currencies,id',
        'customer_id' => 'required',
        'document' => 'required',
        'delegateName' => 'required|string|max:255',
        'delegateSurname' => 'required|string|max:255',
        'delegateEmail' => 'required|email',
        'delegatePhone' => 'nullable|string|max:20',
        'delegateTitle' => 'nullable|string|max:255',
        'delegatenationalId' => 'nullable|string|max:20',
        'delegategender' => 'nullable|string|max:20',
        'delegatedesignation' => 'nullable|string|max:20',
        // Payment validation rules
        'receiptNumber' => 'nullable|string|max:255',
        'paymentAmount' => 'required|numeric|min:0.01',
        'selectedSuspenseId' => 'required',
    ];

    public function boot(_workshopService $workshopService, icustomerInterface $customerRepo, isuspenseInterface $suspenseRepo)
    {
        $this->workshopService = $workshopService;
        $this->customerRepo = $customerRepo;
        $this->suspenseRepo = $suspenseRepo;
    }

    public function mount($id)
    {
        $this->id = $id;
        $workshop = $this->workshopService->getWorkshopWithDetails($id);
        $this->currencyId = $workshop->currency_id;
        $exchangeRates = $this->workshopService->getExchangeRates($this->currencyId);
        $this->exchangerate_id = $exchangeRates->first()['id'] ?? null;
        $this->accounts = new Collection();
        $this->delegatelist = new Collection();
    }

    public function getinvoices()
    {
        return $this->workshopService->getWorkshopOrders($this->id);
    }

    public function workshop()
    {
        return $this->workshopService->getWorkshopWithDetails($this->id);
    }

    public function getAwaitingInvoices()
    {
        return $this->workshopService->getOrdersByStatus($this->id, 'AWAITING');
    }

    public function getPendingInvoices()
    {
        return $this->workshopService->getOrdersByStatus($this->id, 'PENDING');
    }

    public function getPaidInvoices()
    {
        return $this->workshopService->getOrdersByStatus($this->id, 'PAID');
    }

    public function getCurrencies()
    {
        return $this->workshopService->getCurrencies();
    }

    public function getExchangerates()
    {
        return $this->workshopService->getExchangeRates($this->currencyId);
    }

    public function headers(): array
    {
        return [
            ['key' => 'ordernumber', 'label' => 'Order number'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'surname', 'label' => 'Surname'],
            ['key' => 'customer.name', 'label' => 'Organisation'], // Changed from customer.Name
            ['key' => 'delegates', 'label' => 'Delegates'],
            ['key' => 'amount', 'label' => 'Cost'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'action', 'label' => 'Action'],
        ];
    }

    public function accountheaders(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'regnumber', 'label' => 'Regnumber'],
            ['key' => 'type', 'label' => 'Type'],
            ['key' => 'action', 'label' => 'Action'],
        ];
    }

    public function delegateheaders(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'surname', 'label' => 'Surname'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'phone', 'label' => 'Phone'],
            ['key' => 'title', 'label' => 'Title'],
            ['key' => 'national_id', 'label' => 'National ID'],
            ['key' => 'gender', 'label' => 'Gender'],
            ['key' => 'designation', 'label' => 'Designation'],
            ['key' => 'action', 'label' => 'Action'],
        ];
    }

    public function getInvoiceSummaries()
    {
        return $this->workshopService->getOrderSummaries($this->id);
    }

    public function updatedDelegates($value)
    {
        if ($value && is_numeric($value)) {
            $workshop = $this->workshop();
            $exchangeRates = $this->getExchangerates();
            $rate = $exchangeRates->firstWhere('id', $this->exchangerate_id);
            
            if ($rate) {
                $this->cost = $this->workshopService->calculateOrderCost($value, $workshop->Cost, $rate['value']);
            }
        }
    }

    public function updatedcurrencyId($value)
    {
        $this->getExchangerates();
        $workshop = $this->workshop();
        $rate = $this->getExchangerates()->firstWhere('id', $this->exchangerate_id);
        $this->cost = ($this->delegates * $workshop->Cost) * $rate['value'];
    }

    public function updatedExchangerateId($value)
    {
        $workshop = $this->workshop();
        $rate = $this->getExchangerates()->firstWhere('id', $value);
        $this->cost = ($this->delegates * $workshop->Cost) * $rate['value'];
    }

    public function getallDelegates()
    {
        return $this->workshopService->getWorkshopDelegates($this->id);
    }

    public function createInvoice()
    {
        $this->validate();

        $data = [
            'workshop_id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'delegates' => $this->delegates,
            'currency_id' => $this->currencyId,
            'amount' => $this->cost,
            'exchangerate_id' => $this->exchangerate_id,
            'customer_id' => $this->customer_id,
            'document' => $this->document,
            'description' => $this->workshop()->Title
        ];

        $result = $this->workshopService->createWorkshopOrder($data);
        
        if ($result['status'] === 'success') {
            $this->reset(['name', 'surname', 'email', 'delegates', 'currencyId', 'cost', 'customer_id']);
            $this->showCreateModal = false;
            $this->success('Success', $result['message']);
        } else {
            $this->error('Error', $result['message']);
        }
    }

    public function delete($id)
    {
        $result = $this->workshopService->deleteWorkshopOrder($id);
        
        if ($result['status'] === 'success') {
            $this->success($result['message'], "success");
        } else {
            $this->error($result['message'], "error");
        }
    }

    public function searchAccount()
    {
        if ($this->search != "") {
            $this->accounts = $this->customerRepo->searchAccounts($this->search);
        }
    }

    public function selectAccount($id)
    {
        $this->customer_id = $id;
    }

    public function viewOrder($id)
    {
        $this->selectedOrder = $this->workshopService->getWorkshopOrders($this->id)->firstWhere('id', $id);
        
        // Debug logging
        Log::info('=== VIEW ORDER DEBUG ===');
        Log::info('Selected Order ID: ' . ($this->selectedOrder->id ?? 'NULL'));
        Log::info('Customer ID: ' . ($this->selectedOrder->customer_id ?? 'NULL'));
        Log::info('Customer relationship loaded: ' . ($this->selectedOrder->customer ? 'YES' : 'NO'));
        if ($this->selectedOrder->customer) {
            Log::info('Customer name: ' . $this->selectedOrder->customer->name);
            Log::info('Customer regnumber: ' . $this->selectedOrder->customer->regnumber);
            Log::info('Customer type: ' . $this->selectedOrder->customer->type);
        }
        Log::info('Account relationship loaded: ' . ($this->selectedOrder->account ? 'YES' : 'NO'));
        
        $this->showOrderModal = true;
    }

    public function editOrder($id)
    {
        $order = $this->workshopService->getWorkshopOrders($this->id)->firstWhere('id', $id);
        $this->orderid = $order->id;
        $this->currencyId = $order->currency_id;
        $this->delegates = $order->delegates;
        $this->cost = $order->amount;
        $this->price = $order->workshop->currency->name . "" . $order->workshop->cost;
        $this->showEditOrderModal = true;
    }

    public function UpdateOrder()
    {
        $this->validate([
            'currencyId' => 'required',
            'delegates' => 'required',
            'exchangerate_id' => 'required',
            'cost' => 'required'
        ]);

        $data = [
            'currency_id' => $this->currencyId,
            'delegates' => $this->delegates,
            'amount' => $this->cost,
            'exchangerate_id' => $this->exchangerate_id
        ];

        $result = $this->workshopService->updateWorkshopOrder($this->orderid, $data);
        
        if ($result['status'] === 'success') {
            $this->success($result['message']);
        } else {
            $this->error($result['message']);
        }
    }

    public function getDocumentUrl()
    {
        if ($this->selectedOrder && $this->selectedOrder->documenturl) {
            return $this->workshopService->previewDocument($this->selectedOrder->documenturl);
        }
        return null;
    }

    public function createDelegate()
    {
        $this->validate([
            'delegateName' => 'required',
            'delegateSurname' => 'required',
            'delegateEmail' => 'required|email',
            'delegatePhone' => 'nullable',
            'delegateTitle' => 'nullable',
            'delegatenationalId' => 'nullable',
            'delegategender' => 'nullable',
            'delegatedesignation' => 'nullable'
        ]);

        if ($this->selectedOrder->delegates < $this->delegatelist->count() + 1) {
            $this->error('Maximum delegates reached', 'You cannot add more delegates than the number specified in the order.');
            return;
        }

        $data = [
            "workshop_id" => $this->id,
            "workshoporder_id" => $this->orderid,
            "name" => $this->delegateName,
            "surname" => $this->delegateSurname,
            "phone" => $this->delegatePhone,
            "email" => $this->delegateEmail,
            "national_id" => $this->delegatenationalId,
            "gender" => $this->delegategender,
            "designation" => $this->delegatedesignation,
            "title" => $this->delegateTitle,
        ];

        $result = $this->workshopService->createDelegate($data);
        
        if ($result['status'] === 'success') {
            $this->delegatelist = $this->workshopService->getDelegatesByOrder($this->orderid);
            $this->resetDelegateForm();
            $this->success($result['message']);
        } else {
            $this->error($result['message']);
        }
    }

    public function editDelegate($id)
    {
        $this->selectedDelegate = $this->workshopService->getDelegatesByOrder($id)->firstWhere('id', $id);
        $this->delegateName = $this->selectedDelegate->name;
        $this->delegateSurname = $this->selectedDelegate->surname;
        $this->delegateEmail = $this->selectedDelegate->email;
        $this->delegatePhone = $this->selectedDelegate->phone;
        $this->delegateTitle = $this->selectedDelegate->title;
        $this->delegatenationalId = $this->selectedDelegate->nationalId;
        $this->delegategender = $this->selectedDelegate->gender;
        $this->delegatedesignation = $this->selectedDelegate->designation;
        $this->showEditDelegateModal = true;
    }

    public function updateDelegate()
    {
        $this->validate([
            'delegateName' => 'required',
            'delegateSurname' => 'required',
            'delegateEmail' => 'required|email',
            'delegatePhone' => 'nullable',
            'delegateTitle' => 'nullable',
            'delegatenationalId' => 'nullable',
            'delegategender' => 'nullable',
            'delegatedesignation' => 'nullable'
        ]);

        $data = [
            "name" => $this->delegateName,
            "surname" => $this->delegateSurname,
            'email' => $this->delegateEmail,
            "phone" => $this->delegatePhone,
            "title" => $this->delegateTitle,
            "national_id" => $this->delegatenationalId,
            "gender" => $this->delegategender,
            "designation" => $this->delegatedesignation
        ];

        $result = $this->workshopService->updateDelegate($this->selectedDelegate->id, $data);
        
        if ($result['status'] === 'success') {
            $this->resetDelegateForm();
            $this->showEditDelegateModal = false;
            $this->success($result['message']);
        } else {
            $this->error($result['message']);
        }
    }

    public function deleteDelegate($id)
    {
        $result = $this->workshopService->deleteDelegate($id);
        
        if ($result['status'] === 'success') {
            $this->delegatelist = $this->workshopService->getDelegatesByOrder($this->orderid);
            $this->success($result['message']);
        } else {
            $this->error($result['message']);
        }
    }

    public function getDelegates($id)
    {
        $this->selectedOrder = $this->workshopService->getWorkshopOrders($this->id)->firstWhere('id', $id);
        $this->delegatelist = $this->workshopService->getDelegatesByOrder($id);
        $this->orderid = $id;
        $this->showDelegateModal = true;
    }

    public function exportDelegatesToCsv()
    {
        $delegates = $this->workshopService->exportDelegates($this->id);
        $filename = 'workshop_delegates_' . $this->id . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($delegates) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Name',
                'Surname',
                'Email',
                'Phone',
                'Title',
                'National ID',
                'Gender',
                'Designation',
                "Customer"
            ]);

            // Add delegate data
            foreach ($delegates as $delegate) {
                fputcsv($file, [
                    $delegate->name,
                    $delegate->surname,
                    $delegate->email,
                    $delegate->phone,
                    $delegate->title,
                    $delegate->national_id,
                    $delegate->gender,
                    $delegate->designation,
                    $delegate->workshoporder->customer->name // Changed from Name to name
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function resetDelegateForm()
    {
        $this->reset([
            'delegateName',
            'delegateSurname',
            'delegateEmail',
            'delegatePhone',
            'delegateTitle',
            'delegatenationalId',
            'delegategender',
            'delegatedesignation'
        ]);
    }

    public function openPaymentModal($orderId)
    {
        $this->selectedOrder = $this->workshopService->getWorkshopOrders($this->id)->firstWhere('id', $orderId);
        
        if (!$this->selectedOrder || !$this->selectedOrder->invoice) {
            $this->error('Error', 'Invoice not found for this order.');
            return;
        }

        if ($this->selectedOrder->invoice->status !== 'PENDING') {
            $this->error('Error', 'Only pending invoices can be settled.');
            return;
        }

        // Set payment amount to invoice amount
        $this->paymentAmount = (float)$this->selectedOrder->invoice->amount;
        
        // Auto-generate receipt number
        $this->receiptNumber = 'RPT' . date('Y') . str_pad($this->selectedOrder->invoice->id, 6, '0', STR_PAD_LEFT) . rand(1000, 9999);
        
        // Load available suspenses for this customer
        $this->loadAvailableSuspenses();
        
        $this->showPaymentModal = true;
    }

    public function loadAvailableSuspenses()
    {
        if (!$this->selectedOrder || !$this->selectedOrder->customer) {
            $this->availableSuspenses = [];
            return;
        }

        // Use getsuspensewallet method instead of getCustomerSuspenses
        $suspenses = $this->suspenseRepo->getsuspensewallet($this->selectedOrder->customer->regnumber);
        
        $this->availableSuspenses = collect($suspenses)->map(function ($suspense) {
            return [
                'id' => $suspense['type'] . '_' . $suspense['currency'], // Create unique ID
                'label' => "Type: {$suspense['type']} - {$suspense['currency']} {$suspense['balance']} (Available)",
                'balance' => (float)str_replace(',', '', $suspense['balance']),
                'currency' => $suspense['currency'],
                'type' => $suspense['type'],
                'regnumber' => $suspense['regnumber']
            ];
        })->filter(function ($suspense) {
            // For workshops, only show NONREFUNDABLE accounts with positive balance
            return $suspense['balance'] > 0 && $suspense['type'] === 'NONREFUNDABLE';
        })->toArray();
    }

    public function settleInvoice()
    {
        // Auto-generate receipt number if not provided
        if (empty($this->receiptNumber)) {
            $this->receiptNumber = 'RPT' . date('Y') . str_pad($this->selectedOrder->invoice->id, 6, '0', STR_PAD_LEFT) . rand(1000, 9999);
        }

        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'selectedSuspenseId' => 'required',
        ]);

        if (!$this->selectedOrder || !$this->selectedOrder->invoice) {
            $this->error('Error', 'Invoice not found.');
            return;
        }

        if ($this->selectedOrder->invoice->status !== 'PENDING') {
            $this->error('Error', 'Only pending invoices can be settled.');
            return;
        }

        try {
            // Parse the selected suspense ID to get type and currency
            $selectedSuspense = collect($this->availableSuspenses)->firstWhere('id', $this->selectedSuspenseId);
            
            if (!$selectedSuspense) {
                $this->error('Error', 'Selected suspense wallet not found.');
                return;
            }

            // Validate currency matches invoice currency
            $invoiceCurrency = $this->selectedOrder->invoice->currency->name;
            if ($selectedSuspense['currency'] !== $invoiceCurrency) {
                $this->error('Error', "Currency mismatch. Invoice is in {$invoiceCurrency} but selected suspense is in {$selectedSuspense['currency']}.");
                return;
            }

            // Validate sufficient balance
            if ($selectedSuspense['balance'] < $this->paymentAmount) {
                $this->error('Error', 'Insufficient balance in selected suspense wallet.');
                return;
            }

            // Validate payment amount matches invoice amount
            $invoiceAmount = (float)$this->selectedOrder->invoice->amount;
            if (abs($this->paymentAmount - $invoiceAmount) > 0.01) {
                $this->error('Error', 'Payment amount must match the invoice amount exactly.');
                return;
            }

            // Use the deductwallet method with proper parameters
            $result = $this->suspenseRepo->deductwallet(
                $this->selectedOrder->customer->regnumber,
                $this->selectedOrder->invoice->id,
                $selectedSuspense['type'], // NONREFUNDABLE for workshops
                $selectedSuspense['currency'],
                $this->paymentAmount,
                $this->receiptNumber
            );

            if ($result['status'] === 'success') {
                // Update invoice status to PAID
                $this->selectedOrder->invoice->update([
                    'status' => 'PAID'
                ]);

                // Update workshop order status to PAID
                $this->selectedOrder->update([
                    'status' => 'PAID'
                ]);

                $this->showPaymentModal = false;
                $this->reset(['paymentAmount', 'receiptNumber', 'selectedSuspenseId']);
                $this->success('Success', "Invoice settled successfully! Receipt Number: {$this->receiptNumber}");
                
                // Refresh the order data
                $this->selectedOrder = $this->workshopService->getWorkshopOrders($this->id)->firstWhere('id', $this->selectedOrder->id);
            } else {
                $this->error('Error', $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Invoice settlement error: ' . $e->getMessage());
            $this->error('Error', 'An error occurred while settling the invoice. Please try again.');
        }
    }

    public function updatedSelectedSuspenseId($value)
    {
        if ($value && $this->selectedOrder && $this->selectedOrder->invoice) {
            $selectedSuspense = collect($this->availableSuspenses)->firstWhere('id', $value);
            if ($selectedSuspense) {
                // Check currency match
                $invoiceCurrency = $this->selectedOrder->invoice->currency->name;
                if ($selectedSuspense['currency'] !== $invoiceCurrency) {
                    $this->error('Warning', "Currency mismatch. Invoice is in {$invoiceCurrency} but selected suspense is in {$selectedSuspense['currency']}.");
                    return;
                }
                
                // Check balance
                if ($selectedSuspense['balance'] < $this->paymentAmount) {
                    $this->error('Warning', 'Selected suspense wallet has insufficient balance.');
                    return;
                }
                
                // All validations passed
                $this->success('Info', 'Selected suspense wallet is valid for this transaction.');
            }
        }
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->reset(['paymentAmount', 'receiptNumber', 'selectedSuspenseId']);
    }

    public function render()
    {
        return view('livewire.admin.workshops.workshopview', [
            "headers" => $this->headers(),
            "workshop" => $this->workshop(),
            "awaiting" => $this->getAwaitingInvoices(),
            "pending" => $this->getPendingInvoices(),
            "paid" => $this->getPaidInvoices(),
            "accountheaders" => $this->accountheaders(),
            "summaries" => $this->getInvoiceSummaries(),
            "currencies" => $this->getCurrencies(),
            "exchangerates" => $this->getExchangerates(),
            "delegateheaders" => $this->delegateheaders(),
            "fulldelegatelist" => $this->getallDelegates()
        ]);
    }

    public function fixInvoiceAmounts()
    {
        $result = $this->workshopService->checkInvoiceConsistency($this->id);
        
        if ($result['status'] === 'success') {
            $message = $result['message'];
            if (count($result['issues']) > 0) {
                $message .= "\n\nIssues found:\n";
                foreach ($result['issues'] as $issue) {
                    $message .= "- Order {$issue['order_id']}: {$issue['issue']}\n";
                }
            }
            
            $this->success('Success', $result['message']);
            
            // Refresh data if issues were fixed
            if ($result['fixed_count'] > 0) {
                $this->selectedOrder = $this->workshopService->getWorkshopOrders($this->id)->firstWhere('id', $this->selectedOrder->id);
            }
        } else {
            $this->error('Error', $result['message']);
        }
    }

}
