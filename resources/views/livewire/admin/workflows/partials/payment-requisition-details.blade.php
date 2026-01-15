<div class="space-y-4">
    <!-- Payment Requisition Section -->
    <div class="bg-white p-4 rounded-lg border">
        <h3 class="text-lg font-semibold mb-3 text-gray-700">Payment Requisition Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <x-input label="Reference Number" value="{{ $requisitionDetails->reference_number }}" readonly />
            <x-input label="Year" value="{{ $requisitionDetails->year }}" readonly />
            <x-input label="Source Type" value="{{ $requisitionDetails->source_type }}" readonly />
            <x-input label="Department" value="{{ $requisitionDetails->department->name ?? 'N/A' }}" readonly />
            <x-input label="Budget Line Item" value="{{ $requisitionDetails->budgetLineItem->activity ?? 'N/A' }}" readonly />
            <x-input label="Currency" value="{{ $requisitionDetails->currency->name ?? 'USD' }}" readonly />
            <x-input label="Created By" value="{{ $requisitionDetails->createdBy->name ?? 'N/A' }}" readonly />
            <x-input label="Total Amount" value="{{ $requisitionDetails->currency->name ?? 'USD' }} {{ number_format($requisitionDetails->total_amount, 2) }}" readonly />
            <x-input label="Status" value="{{ $requisitionDetails->status }}" readonly />
        </div>
        
        @if($requisitionDetails->payee_type && $requisitionDetails->payee_name)
            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <h4 class="text-md font-semibold mb-2 text-blue-800 dark:text-blue-300">Payee Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <x-input label="Payee Type" value="{{ $requisitionDetails->payee_type }}" readonly />
                    <x-input label="Payee Registration Number" value="{{ $requisitionDetails->payee_regnumber ?? 'N/A' }}" readonly />
                    <x-input label="Payee Name" value="{{ $requisitionDetails->payee_name ?? 'N/A' }}" readonly />
                </div>
            </div>
        @endif
        
        <div class="mt-3">
            <x-textarea label="Purpose" readonly rows="3">{{ $requisitionDetails->purpose }}</x-textarea>
        </div>
    </div>

    <!-- Purchase Requisition Information Section -->
    @if($requisitionDetails->source_type === 'PURCHASE_REQUISITION' && $requisitionDetails->purchaseRequisition)
        @php
            $purchaseRequisition = $requisitionDetails->purchaseRequisition;
            $allAwards = $purchaseRequisition->awards ?? collect();
            $relevantAwards = collect();
            
            if ($requisitionDetails->payee_regnumber && $requisitionDetails->lineItems) {
                $tenderNumbers = $requisitionDetails->lineItems->map(function($item) {
                    if (preg_match('/Tender:\s*([^\s-]+)/i', $item->description, $matches)) {
                        return trim($matches[1]);
                    }
                    return null;
                })->filter()->unique()->values();
                
                $relevantAwards = $allAwards->filter(function($award) use ($requisitionDetails, $tenderNumbers) {
                    $customerMatch = $award->customer && $award->customer->regnumber === $requisitionDetails->payee_regnumber;
                    $tenderMatch = $tenderNumbers->isEmpty() || $tenderNumbers->contains($award->tendernumber);
                    return $customerMatch && $tenderMatch;
                });
            }
        @endphp
        <div class="bg-white p-4 rounded-lg border mt-4">
            <h3 class="text-lg font-semibold mb-3 text-gray-700">Award Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
                <x-input label="PR Number" value="{{ $purchaseRequisition->prnumber ?? 'N/A' }}" readonly />
                <x-input label="Purpose" value="{{ $purchaseRequisition->purpose ?? 'N/A' }}" readonly />
                <x-input label="Status" value="{{ $purchaseRequisition->status ?? 'N/A' }}" readonly />
            </div>

            @if($relevantAwards->count() > 0)
                <div class="mt-4">
                    <h4 class="text-md font-semibold mb-3 text-gray-600">Award Details</h4>
                    <div class="space-y-4">
                        @foreach($relevantAwards as $award)
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-3">
                                    <x-input label="Customer" value="{{ $award->customer->name ?? 'N/A' }}" readonly />
                                    <x-input label="Tender Number" value="{{ $award->tendernumber ?? 'N/A' }}" readonly />
                                    <x-input label="Quantity" value="{{ $award->quantity ?? 'N/A' }}" readonly />
                                    <x-input label="Amount" value="{{ $award->currency->name ?? 'USD' }} {{ number_format($award->amount ?? 0, 2) }}" readonly />
                                    <x-input label="Award Currency" value="{{ $award->paymentcurrency->name ?? 'N/A' }}" readonly />
                                    @if($award->is_split_payment && $award->secondpaymentcurrency)
                                        <x-input label="Second Currency" value="{{ $award->secondpaymentcurrency->name ?? 'N/A' }}" readonly />
                                    @endif
                                    @if($award->pay_at_prevailing_rate)
                                        <x-input label="Payment Rate" value="Pay at prevailing bank rate of the day" readonly />
                                    @endif
                                    <x-input label="Status" value="{{ $award->status ?? 'N/A' }}" readonly />
                                    @if($award->quantity_delivered)
                                        <x-input label="Quantity Delivered" value="{{ $award->quantity_delivered }} / {{ $award->quantity }}" readonly />
                                    @endif
                                </div>
                                
                                @if($award->item)
                                    <div class="mt-2">
                                        <x-textarea label="Item Description" readonly rows="2">{{ $award->item }}</x-textarea>
                                    </div>
                                @endif

                                <!-- Award Documents -->
                                @if($award->documents && $award->documents->count() > 0)
                                    <div class="mt-4 pt-4 border-t">
                                        <h5 class="text-sm font-semibold mb-2 text-gray-600">Award Documents</h5>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                            @foreach($award->documents as $document)
                                                <div class="border rounded p-2 hover:bg-gray-100 transition-colors">
                                                    <div class="flex items-center gap-2">
                                                        <x-icon name="o-document-text" class="w-5 h-5 text-blue-500" />
                                                        <div class="flex-1 min-w-0">
                                                            <div class="text-xs font-medium truncate">{{ $document->document ?? 'Document' }}</div>
                                                            <div class="text-xs text-gray-500 truncate">{{ basename($document->filepath ?? '') }}</div>
                                                        </div>
                                                        @if($document->filepath)
                                                            <a href="{{ asset('storage/' . $document->filepath) }}" target="_blank" class="btn btn-ghost btn-xs">
                                                                <x-icon name="o-eye" class="w-4 h-4" />
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Delivery Information -->
                                @if($award->deliveries && $award->deliveries->count() > 0)
                                    @php
                                        // Get the payment requisition line item quantity for this award
                                        $paymentRequisitionQuantity = 0;
                                        if (isset($viewedLineItemId) && $requisitionDetails->lineItems) {
                                            foreach ($requisitionDetails->lineItems as $lineItem) {
                                                // Check if this is the line item being viewed and if it matches this award
                                                if ($lineItem->id == $viewedLineItemId) {
                                                    // Check if line item description contains this award's tender number
                                                    if (stripos($lineItem->description, $award->tendernumber) !== false) {
                                                        $paymentRequisitionQuantity = $lineItem->quantity;
                                                        break;
                                                    }
                                                }
                                            }
                                        }

                                        // Filter deliveries to only show those relevant to this payment requisition
                                        // Show deliveries in reverse chronological order (most recent first)
                                        // and limit to the payment requisition quantity
                                        $allDeliveries = $award->deliveries ?? collect();
                                        $sortedDeliveries = $allDeliveries->sortByDesc('delivery_date')->values();
                                        $relevantDeliveries = collect();
                                        $quantitySum = 0;

                                        foreach ($sortedDeliveries as $delivery) {
                                            if ($quantitySum < $paymentRequisitionQuantity) {
                                                $relevantDeliveries->push($delivery);
                                                $quantitySum += $delivery->quantity_delivered;
                                            } else {
                                                break;
                                            }
                                        }
                                    @endphp
                                    <div class="mt-4 pt-4 border-t">
                                        <h5 class="text-sm font-semibold mb-2 text-gray-600">Delivery Records 
                                            @if($paymentRequisitionQuantity > 0)
                                                (Payment Requisition: {{ $paymentRequisitionQuantity }} items)
                                            @endif
                                        </h5>
                                        <div class="space-y-2">
                                            @foreach($relevantDeliveries as $delivery)
                                                <div class="bg-white p-2 rounded border text-xs">
                                                    <div class="grid grid-cols-2 gap-2">
                                                        <div><span class="font-medium">Date:</span> {{ \Carbon\Carbon::parse($delivery->delivery_date)->format('Y-m-d') }}</div>
                                                        <div><span class="font-medium">Quantity:</span> {{ $delivery->quantity_delivered }}</div>
                                                        @if($delivery->delivery_notes)
                                                            <div class="col-span-2"><span class="font-medium">Notes:</span> {{ $delivery->delivery_notes }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Line Items Section -->
    @if($requisitionDetails->lineItems && $requisitionDetails->lineItems->count() > 0)
        <div class="bg-white p-4 rounded-lg border mt-4">
            <h3 class="text-lg font-semibold mb-3 text-gray-700">Line Items</h3>
            <x-table :headers="[['key' => 'quantity', 'label' => 'Qty'], ['key' => 'description', 'label' => 'Description'], ['key' => 'unit_amount', 'label' => 'Unit Amount'], ['key' => 'line_total', 'label' => 'Total']]" :rows="$requisitionDetails->lineItems" class="table-xs">
                @scope('cell_unit_amount', $item)
                    <div>{{ $requisitionDetails->currency->name ?? 'USD' }} {{ number_format($item->unit_amount, 2) }}</div>
                @endscope
                @scope('cell_line_total', $item)
                    <div>{{ $requisitionDetails->currency->name ?? 'USD' }} {{ number_format($item->line_total, 2) }}</div>
                @endscope
            </x-table>
        </div>
    @endif

    <!-- Attachments Section -->
    @if($requisitionDetails->documents && $requisitionDetails->documents->count() > 0)
        <div class="bg-white p-4 rounded-lg border mt-4">
            <h3 class="text-lg font-semibold mb-3 text-gray-700">Attachments</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($requisitionDetails->documents as $document)
                    <div class="border rounded-lg p-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            @if(str_ends_with($document->filepath, '.pdf'))
                                <x-icon name="o-document-text" class="w-8 h-8 text-red-500" />
                            @else
                                <x-icon name="o-photo" class="w-8 h-8 text-blue-500" />
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm">
                                    @if($document->document_type === 'invoice')
                                        <x-badge value="Invoice" class="badge-error badge-sm" />
                                    @elseif($document->document_type === 'tax_clearance')
                                        <x-badge value="Tax Clearance" class="badge-warning badge-sm" />
                                    @else
                                        <x-badge value="Other" class="badge-info badge-sm" />
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 truncate">{{ basename($document->filepath) }}</div>
                            </div>
                            <a href="{{ asset('storage/' . $document->filepath) }}" target="_blank" class="btn btn-ghost btn-xs">
                                <x-icon name="o-eye" class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

