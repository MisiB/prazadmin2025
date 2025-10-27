<?php

namespace App\implementation\services;

use App\Models\Invoice;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Workshop;
use App\Models\Exchangerate;
use App\Models\Workshoporder;
use App\Models\WorkshopDelegate;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Storage;
use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\iworkshopInterface;
use App\Interfaces\repositories\iexchangerateInterface;
use App\Interfaces\repositories\icustomerInterface;

class _publicworkshopService
{
    protected $workshopRepo;
    protected $currencyRepo;
    protected $exchangeRateRepo;
    protected $workshop;
    protected $customerrepo;
    protected $workshoporder;

    public function __construct(
        Workshop $workshop,
        Workshoporder $workshoporder,
        icustomerInterface $customerrepo,
        iworkshopInterface $workshopRepo,
        icurrencyInterface $currencyRepo,
        iexchangerateInterface $exchangeRateRepo
    ) {
        $this->workshop = $workshop;
        $this->workshoporder = $workshoporder;
        $this->customerrepo = $customerrepo;
        $this->workshopRepo = $workshopRepo;
        $this->currencyRepo = $currencyRepo;
        $this->exchangeRateRepo = $exchangeRateRepo;
    }

    /**
     * Get workshop details with currency
     */
    public function getWorkshopDetails($id)
    {
        try {
            $workshop = $this->workshop->with('currency')->findOrFail($id);
            return [
                'status' => 'success',
                'data' => $workshop
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get active currencies
     */
    public function getCurrencies()
    {
        try {
            return $this->currencyRepo->getcurrencies()->where('status', 'Active');
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get order statistics for a workshop
     */
    public function getOrderStats($workshopId)
    {
        try {
            $workshop = $this->workshop->findOrFail($workshopId);
            $totalOrders = $this->workshop->orders()->count();
            $totalDelegates = $this->workshop->orders()->where('status', 'PAID')->sum('delegates');
            $remainingSeats = max(0, $workshop->limit - $totalDelegates);

            return [
                'status' => 'success',
                'data' => [
                    'total_orders' => $totalOrders,
                    'total_delegates' => $totalDelegates,
                    'remaining_seats' => $remainingSeats
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Search for customer by registration number
     */
    public function searchCustomer($prnumber)
    {
        try {
            $customer = $this->customerrepo->getCustomerByRegnumber($prnumber);
            if (!$customer) {
                return [
                    'status' => 'error',
                    'message' => 'Customer not found'
                ];
            }

            return [
                'status' => 'success',
                'data' => $customer
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get existing workshop order for customer
     */
    public function getWorkshopOrder($workshopId, $customerId)
    {
        try {
            $workshopOrder = $this->workshoporder->with('currency')
                ->where('workshop_id', $workshopId)
                ->where('customer_id', $customerId)
                ->first();

            return [
                'status' => 'success',
                'data' => $workshopOrder
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create or update workshop order
     */
    public function createOrUpdateOrder($workshopId, $customerId, array $data)
    {
        try {
            $workshop = $this->workshop->findOrFail($workshopId);
            $customer = $this->customerrepo->getCustomerById($customerId);

            // Check remaining seats
            $orderStats = $this->getOrderStats($workshopId);
            if ($orderStats['status'] === 'error') {
                return $orderStats;
            }

            if ($data['delegates'] > $orderStats['data']['remaining_seats']) {
                return [
                    'status' => 'error',
                    'message' => 'Not enough remaining seats available'
                ];
            }

            $existingOrder = $this->workshoporder->where('workshop_id', $workshopId)
                ->where('customer_id', $customer->id)
                ->first();

            if ($existingOrder) {
                // Update existing order
                $existingOrder->update([
                    'delegates' => $data['delegates'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'name' => $data['name'],
                    'surname' => $data['surname'],
                    'amount' => $workshop->Cost * $data['delegates']
                ]);

                return [
                    'status' => 'success',
                    'message' => 'Order successfully updated!',
                    'data' => $existingOrder->fresh()
                ];
            } else {
                // Create new order
                $ordernumber = 'Order-' . date('Y') . '-' . str_pad($this->workshoporder->count() + 1, 5, '0', STR_PAD_LEFT);

                $order = Workshoporder::create([
                    'ordernumber' => $ordernumber,
                    'workshop_id' => $workshopId,
                    'delegates' => $data['delegates'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'name' => $data['name'],
                    'surname' => $data['surname'],
                    'customer_id' => $customerId,
                    'amount' => $workshop->Cost * $data['delegates'],
                    'currency_id' => $workshop->currency_id,
                    'invoicenumber' => null,
                    'status' => 'PENDING'
                ]);

                return [
                    'status' => 'success',
                    'message' => 'Order successfully created!',
                    'data' => $order
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get exchange rates for currency
     */
    public function getExchangeRates($currencyId, $workshopCreatedAt = null)
    {
        try {
            if (!$currencyId) {
                return new Collection();
            }

            $query = $this->exchangeRateRepo->getexchangeratesbyprimarycurrency($currencyId);
            
            if ($currencyId != 1 && $workshopCreatedAt) {
                $query->whereDate('created_at', '>=', $workshopCreatedAt);
            }

            $rates = $query->get();
            
            $array = [];
            foreach ($rates as $rate) {
                $array[] = [
                    'id' => $rate->id,
                    'name' => 'Date: ' . $rate->created_at . ' => Rate 1:' . $rate->Value,
                    'value' => $rate->value
                ];
            }

            return new Collection($array);
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Save payment for workshop order
     */
    public function savePayment($orderId, array $data)
    {
        try {
            $order = Workshoporder::findOrFail($orderId);
            $customer = Customer::findOrFail($order->customer_id);
            $workshop = Workshop::findOrFail($order->workshop_id);

            $random = rand(1000, 1000000) . '' . Workshoporder::count() + 1;
            $invoicenumber = 'INVW-' . date('Y') . '-' . $random;

            $documenturl = $data['document']->store('workshopspop', 'public');

            $order->update([
                'documenturl' => $documenturl,
                'exchangerate_id' => $data['exchangerate'],
                'currency_id' => $data['currency'],
                'amount' => $data['amountdue'],
                'status' => 'AWAITING',
                'invoicenumber' => $invoicenumber
            ]);

            Invoice::create([
                'customer_id' => $customer->id, // Changed from AccountId
                'inventoryitem_id' => 6,
                'currency_id' => $data['currency'],
                'invoicenumber' => $invoicenumber,
                'amount' => $data['amountdue'],
                'status' => 'PENDING',
                'invoicesource' => 'manual',
                'source_id' => $order->id,
                'description' => $workshop->Title,
                'exchangerate_id' => $data['exchangerate']
            ]);

            return [
                'status' => 'success',
                'message' => 'Payment successfully saved'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get delegates for an order
     */
    public function getDelegates($orderId)
    {
        try {
            $delegates = WorkshopDelegate::where('workshoporder_id', $orderId)->get();
            return [
                'status' => 'success',
                'data' => $delegates
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create delegate
     */
    public function createDelegate(array $data)
    {
        try {
            $order = Workshoporder::findOrFail($data['workshoporder_id']);
            $existingDelegatesCount = WorkshopDelegate::where('workshoporder_id', $data['workshoporder_id'])->count();

            if ($order->delegates <= $existingDelegatesCount) {
                return [
                    'status' => 'error',
                    'message' => 'Maximum delegates reached. You cannot add more delegates than the number specified in the order.'
                ];
            }

            $delegate = WorkshopDelegate::create([
                'workshop_id' => $data['workshop_id'],
                'workshoporder_id' => $data['workshoporder_id'],
                'name' => $data['name'],
                'surname' => $data['surname'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
                'national_id' => $data['national_id'] ?? null, // Fixed: was 'nationalId'
                'gender' => $data['gender'] ?? null,
                'designation' => $data['designation'] ?? null,
                'title' => $data['title'] ?? null,
            ]);

            return [
                'status' => 'success',
                'message' => 'Delegate added successfully',
                'data' => $delegate
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update delegate
     */
    public function updateDelegate($delegateId, array $data)
    {
        try {
            $delegate = WorkshopDelegate::findOrFail($delegateId);
            $delegate->update([
                'name' => $data['name'],
                'surname' => $data['surname'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'title' => $data['title'] ?? null,
                'national_id' => $data['national_id'] ?? null,
                'gender' => $data['gender'] ?? null,
                'designation' => $data['designation'] ?? null
            ]);

            return [
                'status' => 'success',
                'message' => 'Delegate updated successfully',
                'data' => $delegate
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete delegate
     */
    public function deleteDelegate($delegateId)
    {
        try {
            WorkshopDelegate::destroy($delegateId);
            return [
                'status' => 'success',
                'message' => 'Delegate deleted successfully'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate and download order PDF
     */
    public function downloadOrder($orderId)
    {
        try {
            $order = Workshoporder::with(['workshop', 'customer', 'currency'])->findOrFail($orderId);
            
            $data = [
                'order' => $order,
                'workshop' => $order->workshop,
                'customer' => $order->customer,
                'date' => now()->format('d M Y'),
            ];

            // Always use HTML generation since we don't have the view file
            $html = $this->generateSimpleOrderHtml($data);
            $pdf = PDF::loadHTML($html);
            $pdf->setPaper('a4');

            return [
                'status' => 'success',
                'pdf' => $pdf,
                'filename' => "workshop_order_{$order->ordernumber}.pdf"
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate simple HTML for PDF when view doesn't exist
     */
    private function generateSimpleOrderHtml($data)
    {
        $order = $data['order'];
        $workshop = $data['workshop'];
        $customer = $data['customer'];
        $date = $data['date'];

        // Handle potential null values
        $customerName = $customer->name ?? 'N/A';
        $customerRegnumber = $customer->regnumber ?? 'N/A';
        $workshopLocation = $workshop->location ?? 'N/A';
        $orderName = ($order->name ?? '') . ' ' . ($order->surname ?? '');
        $orderEmail = $order->email ?? 'N/A';
        $orderPhone = $order->phone ?? 'N/A';

        return "
        <html>
        <head>
            <title>Workshop Order - {$order->ordernumber}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .details { margin-bottom: 20px; }
                h1, h2, h3 { color: #333; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>Workshop Order</h1>
                <h2>{$workshop->title}</h2>
                <p>Order Number: {$order->ordernumber}</p>
                <p>Date: {$date}</p>
            </div>
            <div class='details'>
                <h3>Customer Details</h3>
                <p><strong>Name:</strong> {$customerName}</p>
                <p><strong>Registration:</strong> {$customerRegnumber}</p>
                
                <h3>Contact Person</h3>
                <p><strong>Name:</strong> {$orderName}</p>
                <p><strong>Email:</strong> {$orderEmail}</p>
                <p><strong>Phone:</strong> {$orderPhone}</p>
                
                <h3>Workshop Details</h3>
                <p><strong>Location:</strong> {$workshopLocation}</p>
                <p><strong>Delegates:</strong> {$order->delegates}</p>
                <p><strong>Amount:</strong> " . number_format($order->amount, 2) . "</p>
                <p><strong>Status:</strong> {$order->status}</p>
            </div>
        </body>
        </html>";
    }

    /**
     * Get document URL
     */
    public function getDocumentUrl($documentUrl)
    {
        if ($documentUrl) {
            return Storage::url($documentUrl);
        }
        return null;
    }

    /**
     * Calculate amount due based on delegates, workshop cost, and exchange rate
     */
    public function calculateAmountDue($delegates, $workshopCost, $exchangeRateId)
    {
        try {
            $rate = Exchangerate::findOrFail($exchangeRateId);
            return $delegates * $workshopCost * $rate->Value;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get published workshops
     */
    public function getPublishedWorkshops()
    {
        try {
            $workshops = Workshop::with('currency')
                ->where('Status', 'PUBLISHED')
                ->where('end_date', '>=', now())
                ->orderBy('start_date')
                ->distinct()
                ->get();

            return [
                'status' => 'success',
                'data' => $workshops
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create order without customer (for public registration)
     */
    public function createPublicOrder(array $data)
    {
        try {
            $workshop = Workshop::findOrFail($data['workshop_id']);

            // Check remaining seats
            $orderStats = $this->getOrderStats($data['workshop_id']);
            if ($orderStats['status'] === 'error') {
                return $orderStats;
            }

            if ($data['delegates'] > $orderStats['data']['remaining_seats']) {
                return [
                    'status' => 'error',
                    'message' => 'Not enough remaining seats available'
                ];
            }

            $ordernumber = 'Order-' . date('Y') . '-' . str_pad(Workshoporder::count() + 1, 5, '0', STR_PAD_LEFT);
            
            // Store document
            $documentPath = null;
            if (isset($data['document'])) {
                $documentPath = $data['document']->store('workshop-documents', 'public');
            }

            $order = Workshoporder::create([
                'ordernumber' => $ordernumber,
                'name' => $data['name'],
                'surname' => $data['surname'],
                'email' => $data['email'],
                'delegates' => $data['delegates'],
                'workshop_id' => $data['workshop_id'],
                'amount' => $data['delegates'] * $workshop->Cost,
                'currency_id' => $workshop->CurrencyId,
                'status' => 'AWAITING',
                'documenturl' => $documentPath,
                'customer_id' => null // No customer for public orders
            ]);

            return [
                'status' => 'success',
                'message' => 'Order created successfully! Please add delegate details.',
                'data' => $order
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get order with delegate count
     */
    public function getOrderWithDelegateCount($orderId)
    {
        try {
            $order = Workshoporder::with(['workshop', 'delegatelist'])->findOrFail($orderId);
            
            return [
                'status' => 'success',
                'data' => [
                    'order' => $order,
                    'delegate_count' => $order->delegatelist->count(),
                    'remaining_delegates' => $order->delegates - $order->delegatelist->count()
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
