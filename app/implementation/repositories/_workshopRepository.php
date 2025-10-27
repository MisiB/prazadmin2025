<?php

namespace App\implementation\repositories;

use App\Enums\ApiResponse;
use App\Interfaces\repositories\iworkshopInterface;
use App\Models\Workshop;
use App\Models\workshoporder;
use App\Models\WorkshopDelegate;
use App\Models\Invoice;
use App\Models\Currency;
use App\Models\Exchangerate;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;


class _workshopRepository implements iworkshopInterface
{
    
    protected $workshop;
    protected $workshopOrder;
    protected $workshopDelegate;
    protected $invoice;
    protected $currency;
    protected $exchangerate;
    protected $customer;

    public function __construct(
        Workshop $workshop,
        workshoporder $workshopOrder,
        WorkshopDelegate $workshopDelegate,
        Invoice $invoice,
        Currency $currency,
        Exchangerate $exchangerate,
        Customer $customer
    ) {
        $this->workshop = $workshop;
        $this->workshopOrder = $workshopOrder;
        $this->workshopDelegate = $workshopDelegate;
        $this->invoice = $invoice;
        $this->currency = $currency;
        $this->exchangerate = $exchangerate;
        $this->customer = $customer;
    }

    public function getAllWorkshops()
    {
        try {
            return $this->workshop->with('currency')->get();
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getWorkshopById($id)
    {
        try {
            $workshop = $this->workshop->find($id);
            return $workshop ? $workshop : ApiResponse::NOT_FOUND;
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getWorkshopWithDetails($id)
    {
        try {
            return $this->workshop->with(['orders.customer', 'currency'])
                ->where('id', $id)
                ->first();
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function createWorkshop(array $data)
    {
        try {
            $document_url = null;
            if (isset($data['document'])) {
                $document_url = $data['document']->store('workshop-documents', 'public');
                unset($data['document']);
            }

            $workshop = $this->workshop->create([
                ...$data,
                'document_url' => $document_url,
                'created_by' => Auth::user()->id
            ]);

            return ['status' => 'success', 'message' => 'Workshop created successfully.', 'data' => $workshop];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateWorkshop($id, array $data)
    {
        try {
            $workshop = $this->workshop->findOrFail($id);
            
            if (isset($data['editDocument'])) {
                $documentUrl = $data['editDocument']->store('workshop-documents', 'public');
                $data['document_url'] = $documentUrl;
                unset($data['editDocument']);
            }

            $workshop->update($data);
            return ['status' => 'success', 'message' => 'Workshop updated successfully.', 'data' => $workshop];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteWorkshop($id)
    {
        try {
            $workshop = $this->workshop->find($id);
            if (!$workshop) {
                return ['status' => 'error', 'message' => 'Workshop not found'];
            }
            
            $workshop->delete();
            return ['status' => 'success', 'message' => 'Workshop deleted successfully.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getWorkshopOrders($workshop_id)
    {
        try {
            return $this->workshopOrder->with(['currency', 'customer', 'invoice'])
                ->where('workshop_id', $workshop_id)
                ->get();
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getOrdersByStatus($workshop_id, $status)
    {
        try {
            return $this->workshopOrder->with(['currency', 'customer'])
                ->where('workshop_id', $workshop_id)
                ->where('status', $status)
                ->get();
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function createWorkshopOrder(array $data)
    {
        try {
            $ordernumber = 'Order-' . date('Y') . '-' . str_pad($this->workshopOrder->count() + 1, 5, '0', STR_PAD_LEFT);
            $invoicenumber = 'INVW-' . date('Y') . '-' . str_pad($this->workshopOrder->count() + 1, 5, '0', STR_PAD_LEFT);

            $documenturl = $data['document']->store('workshopspop', 'public');
            unset($data['document']);

            $order = $this->workshopOrder->create([
                ...$data,
                'ordernumber' => $ordernumber,
                'invoicenumber' => $invoicenumber,
                'documenturl' => $documenturl,
                'status' => 'AWAITING'
            ]);

            // Create corresponding invoice
            $this->invoice->create([
                'customer_id' => $data['customer_id'], // Changed from AccountId
                'inventoryitem_id' => 6,
                'currency_id' => $data['currency_id'],
                'invoicenumber' => $invoicenumber,
                'amount' => $data['amount'],
                'status' => 'PENDING',
                'user_id' => Auth::user()->id,
                'invoicesource' => 'manual',
                'source_id' => $order->id,
                'description' => $data['description'] ?? 'Workshop Order',
                'exchangerate_id' => $data['exchangerate_id']
            ]);

            return ['status' => 'success', 'message' => 'Order created successfully.', 'data' => $order];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateWorkshopOrder($order_id, array $data)
    {
        try {
            $order = $this->workshopOrder->findOrFail($order_id);
            $order->update($data);

            // Update corresponding invoice to keep amounts in sync
            $invoice = $this->invoice->where('source_id', $order->id)
                ->where('inventoryitem_id', 6)
                ->first();
            
            if ($invoice) {
                $invoice->update([
                    'currency_id' => $data['currency_id'] ?? $invoice->currency_id,
                    'amount' => $data['amount'] ?? $invoice->amount, // Ensure amount stays in sync
                    'exchangerate_id' => $data['exchangerate_id'] ?? $invoice->exchangerate_id
                ]);
            }

            return ['status' => 'success', 'message' => 'Order updated successfully.', 'data' => $order];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteWorkshopOrder($orderId)
    {
        try {
            $order = $this->workshopOrder->with('invoice')->findOrFail($orderId);
            
            if ($order->invoice && $order->invoice->status !== 'PENDING') {
                return ['status' => 'error', 'message' => 'Order cannot be deleted as invoice is not in PENDING status'];
            }

            if ($order->invoice) {
                $order->invoice->delete();
            }
            
            $order->delete();
            return ['status' => 'success', 'message' => 'Order deleted successfully.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getWorkshopDelegates($workshop_id)
    {
        try {
            return $this->workshopDelegate->with('workshoporder.customer')
                ->where('workshop_id', $workshop_id)
                ->get();
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function createDelegate(array $data)
    {
        try {
            $delegate = $this->workshopDelegate->create($data);
            return ['status' => 'success', 'message' => 'Delegate created successfully.', 'data' => $delegate];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateDelegate($delegate_id, array $data)
    {
        try {
            $delegate = $this->workshopDelegate->findOrFail($delegate_id);
            $delegate->update($data);
            return ['status' => 'success', 'message' => 'Delegate updated successfully.', 'data' => $delegate];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteDelegate($delegate_id)
    {
        try {
            $delegate = $this->workshopDelegate->findOrFail($delegate_id);
            $delegate->delete();
            return ['status' => 'success', 'message' => 'Delegate deleted successfully.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getDelegatesByOrder($order_id)
    {
        try {
            return $this->workshopDelegate->where('workshoporder_id', $order_id)->get();
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getOrderSummaries($workshop_id)
    {
        try {
            $orders = $this->getWorkshopOrders($workshop_id);
            
            $awaiting = $orders->where('status', 'AWAITING');
            $pending = $orders->where('status', 'PENDING');  
            $paid = $orders->where('status', 'PAID');

            return [
                'awaiting_count' => $awaiting->sum('delegates'),
                'pending_count' => $pending->sum('delegates'),
                'paid_count' => $paid->sum('delegates'),
                'awaiting_total' => $awaiting->sum('delegates'),
                'pending_total' => $pending->sum('delegates'),
                'paid_total' => $paid->sum('delegates'),
                'total_amount' => $orders->sum('delegates')
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function exportDelegates($workshop_id)
    {
        try {
            return $this->getWorkshopDelegates($workshop_id);
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Additional supporting methods
    public function getCurrencies()
    {
        try {
            return $this->currency->where('Status', 'Active')->get();
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getStatusList()
    {
        return [
            ['id' => 'PENDING', 'name' => 'Pending'],
            ['id' => 'PUBLISHED', 'name' => 'Published'],
            ['id' => 'CANCELLED', 'name' => 'Cancelled'],
        ];
    }

    public function getTargetList()
    {
        return [
            ['id' => 'PE', 'name' => 'Procurement entities'],
            ['id' => 'BIDDER', 'name' => 'Bidders'],
            ['id' => 'ALL', 'name' => 'ALL'],
        ];
    }

    public function getExchangeRates($currency_id)
    {
        try {
            if (!$currency_id) {
                return new Collection();
            }

            $rates = $this->exchangerate->where('SecondaryCurrencyId', $currency_id)->get();
            return $rates->map(function ($rate) {
                return [
                    'id' => $rate->id,
                    'name' => 'Date:' . $rate->created_at . '=> 1:' . $rate->Value
                ];
            });
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function calculateOrderCost($delegates, $workshop_cost, $exchange_rate)
    {
        return ($delegates * $workshop_cost) * $exchange_rate;
    }

    public function previewDocument($document_url)
    {
        return asset('storage/' . $document_url);
    }

    public function searchAccounts($search)
    {
        try {
            if (empty($search)) {
                return new Collection();
            }
            
            return $this->customer->where('Name', 'like', '%' . $search . '%')->get();
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function checkInvoiceNumberConsistency($workshop_id = null)
    {
        try {
            $query = $this->workshopOrder->with('invoice');
            
                if ($workshop_id) {
                $query->where('workshop_id', $workshop_id);
            }
            
            $orders = $query->get();
            $issues = [];
            $fixed = 0;
            
            foreach ($orders as $order) {
                // Check if invoice exists but numbers don't match
                if ($order->invoice && $order->invoicenumber !== $order->invoice->invoicenumber) {
                    $issues[] = [
                        'order_id' => $order->id,
                        'order_invoicenumber' => $order->invoicenumber,
                        'invoice_invoicenumber' => $order->invoice->invoicenumber,
                        'issue' => 'Invoice numbers mismatch'
                    ];
                    
                    // Fix by updating invoice number to match order
                    $order->invoice->update(['invoicenumber' => $order->invoicenumber]);
                    $fixed++;
                }
                
                // Check if order has invoicenumber but no invoice record
                if ($order->invoicenumber && !$order->invoice) {
                    $issues[] = [
                        'order_id' => $order->id,
                        'order_invoicenumber' => $order->invoicenumber,
                        'invoice_invoicenumber' => null,
                        'issue' => 'Order has invoice number but no invoice record'
                    ];
                }
                
                // Check if amounts don't match
                if ($order->invoice && $order->amount != $order->invoice->amount) {
                    $issues[] = [
                        'order_id' => $order->id,
                        'order_amount' => $order->amount,
                        'invoice_amount' => $order->invoice->amount,
                        'issue' => 'Amounts mismatch'
                    ];
                    
                    // Fix amount
                    $order->invoice->update(['amount' => $order->amount]);
                    $fixed++;
                }
            }
            
            return [
                'status' => 'success', 
                'message' => "Found " . count($issues) . " issues, fixed {$fixed}",
                'issues' => $issues,
                'fixed_count' => $fixed
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function syncOrderInvoiceAmounts($workshop_id = null)
    {
        return $this->checkInvoiceNumberConsistency($workshop_id);
    }
    
}
