<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
    link-item-class="text-base" />

    <x-card title="Payment Requisition Approvals" separator class="mt-5 border-2 border-gray-200">
     
        @if ($workflow)
            <div class="space-y-4">
                {{-- Step 0: HOD Recommendation (Submitted) --}}
                @can("payment.requisition.recommend.hod")
                    @php
                        $awaitingRequisitions = $awaitingrecommendation;
                        $awaitingCount = $awaitingRequisitions->count();
                        $isExpanded = $this->isStageExpanded('Submitted');
                    @endphp

                    <div class="border-2 rounded-lg border-gray-200 shadow-sm">
                        <!-- Stage Header -->
                        <div class="p-4 bg-gray-50 rounded-t-lg cursor-pointer hover:bg-gray-100 transition-colors"
                            wire:click="toggleStage('Submitted')">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <x-icon name="o-chevron-{{ $isExpanded ? 'down' : 'right' }}" class="w-5 h-5" />
                                        <div>
                                            <div class="text-xl font-bold">HOD Recommendation</div>
                                            <div class="text-sm text-gray-600">Step 0 - Submitted</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <x-badge value="{{ $awaitingCount }}" class="badge-secondary badge-lg" />
                                    @if ($awaitingCount > 0)
                                        @can('payment.requisition.recommend.hod')
                                            @php
                                                $stageUuids = $awaitingRequisitions->pluck('uuid')->toArray();
                                                $selectedInStage = array_intersect($selectedForBulk, $stageUuids);
                                            @endphp
                                            <x-button icon="o-check-circle" class="btn-success btn-sm" 
                                                label="Bulk Recommend ({{ count($selectedInStage) }})"
                                                wire:click.stop="openBulkApprovalModal('Submitted')"
                                                :disabled="count($selectedInStage) == 0" />
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Stage Content - Requisitions List -->
                        @if ($isExpanded)
                            <div class="p-4 space-y-3">
                                <!-- Select All / Clear Selection for this stage -->
                                @if ($awaitingRequisitions->count() > 0)
                                    @can('payment.requisition.recommend.hod')
                                        <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                                            @php
                                                $stageUuids = $awaitingRequisitions->pluck('uuid')->toArray();
                                                $selectedInStage = array_intersect($selectedForBulk, $stageUuids);
                                                $allSelectedInStage = count($selectedInStage) == count($stageUuids);
                                            @endphp
                                            <x-checkbox 
                                                :checked="$allSelectedInStage && count($stageUuids) > 0"
                                                wire:click="selectAllForBulk('Submitted')"
                                                label="Select All ({{ count($stageUuids) }})" />
                                            @if (count($selectedInStage) > 0)
                                                <x-button icon="o-x-mark" class="btn-ghost btn-xs" 
                                                    label="Clear Selection ({{ count($selectedInStage) }})"
                                                    wire:click="clearBulkSelection" />
                                            @endif
                                        </div>
                                    @endcan
                                @endif
                                @forelse ($awaitingRequisitions as $requisition)
                                    @php
                                        $isRequisitionExpanded = $this->isRequisitionExpanded($requisition->uuid);
                                        $isSelected = in_array($requisition->uuid, $selectedForBulk);
                                    @endphp
                                    <div class="border border-gray-200 rounded-lg shadow-sm {{ $isSelected ? 'ring-2 ring-primary ring-offset-2' : '' }}">
                                        <!-- Requisition Header -->
                                        <div class="p-3 bg-white rounded-t-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                            wire:click="toggleRequisition('{{ $requisition->uuid }}')">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4">
                                                    @can('payment.requisition.recommend.hod')
                                                        <x-checkbox 
                                                            :checked="$isSelected"
                                                            wire:click.stop="toggleBulkSelection('{{ $requisition->uuid }}')"
                                                            class="checkbox-sm" />
                                                    @endcan
                                                    <x-icon name="o-chevron-{{ $isRequisitionExpanded ? 'down' : 'right' }}" class="w-4 h-4" />
                                                    <div>
                                                        <div class="font-semibold">{{ $requisition->reference_number }}</div>
                                                        <div class="text-sm text-gray-600">{{ $requisition->budgetLineItem->activity ?? 'N/A' }} - {{ $requisition->department->name ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <div class="text-right">
                                                        <div class="font-semibold">{{ $requisition->currency->name ?? 'USD' }} {{ number_format($requisition->total_amount, 2) }}</div>
                                                        <div class="text-xs text-gray-500">{{ Str::limit($requisition->purpose, 30) }}</div>
                                                    </div>
                                                    <x-badge value="Submitted" class="badge-info" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Requisition Details (Expanded) -->
                                        @if ($isRequisitionExpanded)
                                            @php
                                                $requisitionDetails = $this->getRequisitionByUuid($requisition->uuid);
                                            @endphp
                                            <div class="p-4 bg-gray-50 space-y-4">
                                                <x-tabs wire:model="selectedTabs.{{ $requisition->uuid }}">
                                                    <!-- Details Tab -->
                                                    <x-tab name="details-{{ $requisition->uuid }}" label="Details" icon="o-document-text">
                                                        <div class="space-y-4 mt-4">
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
                                                                    // Filter awards to only show those relevant to this payment requisition
                                                                    // Match by payee_regnumber (customer regnumber) and tender number in line item description
                                                                    $allAwards = $purchaseRequisition->awards ?? collect();
                                                                    $relevantAwards = collect();
                                                                    
                                                                    if ($requisitionDetails->payee_regnumber && $requisitionDetails->lineItems) {
                                                                        // Extract tender numbers from line item descriptions
                                                                        $tenderNumbers = $requisitionDetails->lineItems->map(function($item) {
                                                                            // Description format: "Payment for delivery to {customer} - Tender: {tendernumber}"
                                                                            if (preg_match('/Tender:\s*([^\s-]+)/i', $item->description, $matches)) {
                                                                                return trim($matches[1]);
                                                                            }
                                                                            return null;
                                                                        })->filter()->unique()->values();
                                                                        
                                                                        // Match awards by customer regnumber and tender number
                                                                        $relevantAwards = $allAwards->filter(function($award) use ($requisitionDetails, $tenderNumbers) {
                                                                            $customerMatch = $award->customer && $award->customer->regnumber === $requisitionDetails->payee_regnumber;
                                                                            $tenderMatch = $tenderNumbers->isEmpty() || $tenderNumbers->contains($award->tendernumber);
                                                                            return $customerMatch && $tenderMatch;
                                                                        });
                                                                    }
                                                                @endphp
                                                                <div class="bg-white p-4 rounded-lg border mt-4">
                                                                    <h3 class="text-lg font-semibold mb-3 text-gray-700">Purchase Requisition Information</h3>
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
                                                                                            <div class="mt-4 pt-4 border-t">
                                                                                                <h5 class="text-sm font-semibold mb-2 text-gray-600">Delivery Records</h5>
                                                                                                <div class="space-y-2">
                                                                                                    @foreach($award->deliveries as $delivery)
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
                                                    </x-tab>

                                                    <!-- Approvals Tab -->
                                                    <x-tab name="approvals-{{ $requisition->uuid }}" label="Approvals" icon="o-document-check">
                                                        <div class="mt-4 space-y-3">
                                                            @if ($requisitionDetails->workflow)
                                                                @foreach ($requisitionDetails->workflow->workflowparameters->sortBy('order') as $wp)
                                                                    @php
                                                                        $approval = $requisitionDetails->approvals?->where('workflowparameter_id', $wp->id)->first();
                                                                        $status = $approval?->status ?? 'PENDING';
                                                                        $statusColor = match ($status) {
                                                                            'APPROVED', 'RECOMMEND' => 'bg-green-100 text-green-800',
                                                                            'REJECTED', 'NOT_RECOMMEND' => 'bg-red-100 text-red-800',
                                                                            'PENDING' => 'bg-yellow-100 text-yellow-800',
                                                                            default => 'bg-gray-100 text-gray-800',
                                                                        };
                                                                    @endphp
                                                                    <div class="bg-white p-3 rounded border">
                                                                        <div class="flex items-center justify-between mb-2">
                                                                            <span class="text-sm font-semibold">Step {{ $wp->order }}: {{ $wp->name }}</span>
                                                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">{{ $status }}</span>
                                                                        </div>
                                                                        @if ($approval)
                                                                            <div class="text-xs text-gray-600 space-y-1">
                                                                                <div>Approver: {{ $approval->user->name ?? 'N/A' }}</div>
                                                                                <div>Comment: {{ $approval->comment ?? '--' }}</div>
                                                                                <div>Date: {{ $approval->created_at?->format('Y-m-d H:i:s') ?? '--' }}</div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </x-tab>
                                                </x-tabs>

                                                <!-- Action Buttons -->
                                                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t">
                                                    @if ($requisitionDetails->status == 'Submitted')
                                                        @can('payment.requisition.recommend.hod')
                                                            <x-button icon="o-check-circle" class="btn-primary btn-sm" label="Make Decision"
                                                                @click="$wire.openDecisionModal('{{ $requisition->uuid }}')" />
                                                        @endcan
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-gray-500">
                                        No requisitions awaiting recommendation
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                @endcan

                {{-- Workflow Steps --}}
                @foreach ($workflow->workflowparameters->sortBy('order') as $workflowparameter)
                    @php
                        $stageRequisitions = $requisitions->where('status', $workflowparameter->status);
                        $count = $stageRequisitions->count();
                        $isExpanded = $this->isStageExpanded($workflowparameter->status);
                    @endphp

                    <div class="border-2 rounded-lg border-gray-200 shadow-sm">
                        <!-- Stage Header -->
                        <div class="p-4 bg-gray-50 rounded-t-lg cursor-pointer hover:bg-gray-100 transition-colors"
                            wire:click="toggleStage('{{ $workflowparameter->status }}')">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <x-icon name="o-chevron-{{ $isExpanded ? 'down' : 'right' }}" class="w-5 h-5" />
                                        <div>
                                            <div class="text-xl font-bold">{{ $workflowparameter->name }}</div>
                                            <div class="text-sm text-gray-600">Step {{ $workflowparameter->order }} - {{ $workflowparameter->status }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <x-badge value="{{ $count }}" class="badge-secondary badge-lg" />
                                    @if ($count > 0)
                                        @can($workflowparameter->permission->name)
                                            @php
                                                $stageUuids = $stageRequisitions->pluck('uuid')->toArray();
                                                $selectedInStage = array_intersect($selectedForBulk, $stageUuids);
                                            @endphp
                                            <x-button icon="o-check-circle" class="btn-success btn-sm" 
                                                label="Bulk Approve ({{ count($selectedInStage) }})"
                                                wire:click.stop="openBulkApprovalModal('{{ $workflowparameter->status }}')"
                                                :disabled="count($selectedInStage) == 0" />
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Stage Content - Requisitions List -->
                        @if ($isExpanded)
                            <div class="p-4 space-y-3">
                                <!-- Select All / Clear Selection for this stage -->
                                @if ($stageRequisitions->count() > 0)
                                    @can($workflowparameter->permission->name)
                                        <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                                            @php
                                                $stageUuids = $stageRequisitions->pluck('uuid')->toArray();
                                                $selectedInStage = array_intersect($selectedForBulk, $stageUuids);
                                                $allSelectedInStage = count($selectedInStage) == count($stageUuids);
                                            @endphp
                                            <x-checkbox 
                                                :checked="$allSelectedInStage && count($stageUuids) > 0"
                                                wire:click="selectAllForBulk('{{ $workflowparameter->status }}')"
                                                label="Select All ({{ count($stageUuids) }})" />
                                            @if (count($selectedInStage) > 0)
                                                <x-button icon="o-x-mark" class="btn-ghost btn-xs" 
                                                    label="Clear Selection ({{ count($selectedInStage) }})"
                                                    wire:click="clearBulkSelection" />
                                            @endif
                                        </div>
                                    @endcan
                                @endif
                                @forelse ($stageRequisitions as $requisition)
                                    @php
                                        $isRequisitionExpanded = $this->isRequisitionExpanded($requisition->uuid);
                                        $isSelected = in_array($requisition->uuid, $selectedForBulk);
                                    @endphp
                                    <div class="border border-gray-200 rounded-lg shadow-sm {{ $isSelected ? 'ring-2 ring-primary ring-offset-2' : '' }}">
                                        <!-- Requisition Header -->
                                        <div class="p-3 bg-white rounded-t-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                            wire:click="toggleRequisition('{{ $requisition->uuid }}')">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4">
                                                    @can($workflowparameter->permission->name)
                                                        <x-checkbox 
                                                            :checked="$isSelected"
                                                            wire:click.stop="toggleBulkSelection('{{ $requisition->uuid }}')"
                                                            class="checkbox-sm" />
                                                    @endcan
                                                    <x-icon name="o-chevron-{{ $isRequisitionExpanded ? 'down' : 'right' }}" class="w-4 h-4" />
                                                    <div>
                                                        <div class="font-semibold">{{ $requisition->reference_number }}</div>
                                                        <div class="text-sm text-gray-600">{{ $requisition->budgetLineItem->activity ?? 'N/A' }} - {{ $requisition->department->name ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <div class="text-right">
                                                        <div class="font-semibold">{{ $requisition->currency->name ?? 'USD' }} {{ number_format($requisition->total_amount, 2) }}</div>
                                                        <div class="text-xs text-gray-500">{{ Str::limit($requisition->purpose, 30) }}</div>
                                                    </div>
                                                    @php
                                                        $statusColor = match ($requisition->status) {
                                                            'HOD_RECOMMENDED' => 'badge-info',
                                                            'ADMIN_REVIEWED' => 'badge-info',
                                                            'ADMIN_RECOMMENDED' => 'badge-info',
                                                            'AWAITING_PAYMENT_VOUCHER' => 'badge-success',
                                                            'Rejected' => 'badge-error',
                                                            default => 'badge-ghost',
                                                        };
                                                    @endphp
                                                    <x-badge :value="$requisition->status" class="{{ $statusColor }}" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Requisition Details (Expanded) -->
                                        @if ($isRequisitionExpanded)
                                            @php
                                                $requisitionDetails = $this->getRequisitionByUuid($requisition->uuid);
                                            @endphp
                                            <div class="p-4 bg-gray-50 space-y-4">
                                                <x-tabs wire:model="selectedTabs.{{ $requisition->uuid }}">
                                                    <!-- Details Tab -->
                                                    <x-tab name="details-{{ $requisition->uuid }}" label="Details" icon="o-document-text">
                                                        <div class="space-y-4 mt-4">
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
                                                                    // Filter awards to only show those relevant to this payment requisition
                                                                    // Match by payee_regnumber (customer regnumber) and tender number in line item description
                                                                    $allAwards = $purchaseRequisition->awards ?? collect();
                                                                    $relevantAwards = collect();
                                                                    
                                                                    if ($requisitionDetails->payee_regnumber && $requisitionDetails->lineItems) {
                                                                        // Extract tender numbers from line item descriptions
                                                                        $tenderNumbers = $requisitionDetails->lineItems->map(function($item) {
                                                                            // Description format: "Payment for delivery to {customer} - Tender: {tendernumber}"
                                                                            if (preg_match('/Tender:\s*([^\s-]+)/i', $item->description, $matches)) {
                                                                                return trim($matches[1]);
                                                                            }
                                                                            return null;
                                                                        })->filter()->unique()->values();
                                                                        
                                                                        // Match awards by customer regnumber and tender number
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
                                                                                    @php
                                                                                        // Get the payment requisition line item quantity for this award
                                                                                        $paymentRequisitionQuantity = 0;
                                                                                        if ($requisitionDetails->lineItems) {
                                                                                            foreach ($requisitionDetails->lineItems as $lineItem) {
                                                                                                // Check if line item description contains this award's tender number
                                                                                                if (stripos($lineItem->description, $award->tendernumber) !== false) {
                                                                                                    $paymentRequisitionQuantity += $lineItem->quantity;
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

                                                                                        <!-- Delivery Information - Only for Payment Requisition -->
                                                                                        @if($relevantDeliveries->count() > 0)
                                                                                            <div class="mt-4 pt-4 border-t">
                                                                                                <h5 class="text-sm font-semibold mb-2 text-gray-600">Delivery Records (Payment Requisition: {{ $paymentRequisitionQuantity }} items)</h5>
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
                                                    </x-tab>

                                                    <!-- Approvals Tab -->
                                                    <x-tab name="approvals-{{ $requisition->uuid }}" label="Approvals" icon="o-document-check">
                                                        <div class="mt-4 space-y-3">
                                                            @if ($requisitionDetails->workflow)
                                                                @foreach ($requisitionDetails->workflow->workflowparameters->sortBy('order') as $wp)
                                                                    @php
                                                                        $approval = $requisitionDetails->approvals?->where('workflowparameter_id', $wp->id)->first();
                                                                        $status = $approval?->status ?? 'PENDING';
                                                                        $statusColor = match ($status) {
                                                                            'APPROVED', 'RECOMMEND' => 'bg-green-100 text-green-800',
                                                                            'REJECTED', 'NOT_RECOMMEND' => 'bg-red-100 text-red-800',
                                                                            'PENDING' => 'bg-yellow-100 text-yellow-800',
                                                                            default => 'bg-gray-100 text-gray-800',
                                                                        };
                                                                    @endphp
                                                                    <div class="bg-white p-3 rounded border">
                                                                        <div class="flex items-center justify-between mb-2">
                                                                            <span class="text-sm font-semibold">Step {{ $wp->order }}: {{ $wp->name }}</span>
                                                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">{{ $status }}</span>
                                                                        </div>
                                                                        @if ($approval)
                                                                            <div class="text-xs text-gray-600 space-y-1">
                                                                                <div>Approver: {{ $approval->user->name ?? 'N/A' }}</div>
                                                                                <div>Comment: {{ $approval->comment ?? '--' }}</div>
                                                                                <div>Date: {{ $approval->created_at?->format('Y-m-d H:i:s') ?? '--' }}</div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </x-tab>
                                                </x-tabs>

                                                <!-- Action Buttons -->
                                                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t">
                                                    @if ($requisitionDetails->status == $workflowparameter->status && !in_array($requisitionDetails->status, ['AWAITING_PAYMENT_VOUCHER', 'Rejected']))
                                                        @can($workflowparameter->permission->name)
                                                            <x-button icon="o-check-circle" class="btn-primary btn-sm" label="Make Decision"
                                                                @click="$wire.openDecisionModal('{{ $requisition->uuid }}')" />
                                                        @endcan
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-gray-500">
                                        No requisitions in this stage
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex items-center justify-center h-64">
                  <x-alert type="error" message="Workflow Not Found" />
            </div>
        @endif
    </x-card>

    <!-- Approval/Rejection Modal -->
    <x-modal wire:model="decisionmodal" title="{{ $currentStepName ?? 'Make Decision' }}">
        <x-form wire:submit="savedecision">
            <x-select label="Decision" wire:model.live="decision" placeholder="Select Decision"
                :options="$this->decisionOptions" />
            <x-textarea label="Comment" wire:model="comment" placeholder="Enter your comment (optional)" />
            <x-pin label="Approval Code" wire:model="approvalcode" size="6" hide />
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.decisionmodal = false" />
                <x-button icon="o-check" class="btn-primary" label="Submit" type="submit" spinner="savedecision" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <!-- Bulk Approval Modal -->
    <x-modal wire:model="bulkapprovalmodal" title="Bulk {{ $this->bulkDecisionLabel ?? 'Approve' }} - {{ $bulkStepName ?? '' }}">
        <x-form wire:submit="executeBulkApproval">
            <x-alert icon="o-information-circle" class="alert-info mb-4">
                You are about to {{ strtolower($this->bulkDecisionLabel ?? 'approve') }} {{ count($selectedForBulk) }} requisition(s).
            </x-alert>
            <x-textarea label="Comment (Optional)" wire:model="bulkComment" placeholder="Enter a comment for all selected requisitions" />
            <x-pin label="Approval Code" wire:model="bulkApprovalCode" size="6" hide />
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.bulkapprovalmodal = false" />
                <x-button icon="o-check" class="btn-primary" label="Bulk {{ $this->bulkDecisionLabel ?? 'Approve' }}" type="submit" spinner="executeBulkApproval" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
