<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
    link-item-class="text-base" />
    
    @if($paymentrequisition)
        <x-card title="Payment Requisition Details" separator class="mt-5 border-2 border-gray-200">
            <x-tabs>
                <!-- Details Tab -->
                <x-tab name="details" label="Details" icon="o-document-text">
                    <div class="space-y-4 mt-4">
                        <!-- Payment Requisition Information -->
                        <div class="bg-white p-4 rounded-lg border">
                            <h3 class="text-lg font-semibold mb-3 text-gray-700">Payment Requisition Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <x-input label="Reference Number" value="{{ $paymentrequisition->reference_number }}" readonly />
                                <x-input label="Year" value="{{ $paymentrequisition->year }}" readonly />
                                <x-input label="Source Type" value="{{ $paymentrequisition->source_type }}" readonly />
                                @if($paymentrequisition->source_type == 'PURCHASE_REQUISITION' && $paymentrequisition->purchaseRequisition)
                                    <x-input label="Purchase Requisition" value="{{ $paymentrequisition->purchaseRequisition->prnumber }}" readonly />
                                @endif
                                <x-input label="Department" value="{{ $paymentrequisition->department->name ?? 'N/A' }}" readonly />
                                <x-input label="Budget Line Item" value="{{ $paymentrequisition->budgetLineItem->activity ?? 'N/A' }}" readonly />
                                <x-input label="Currency" value="{{ $paymentrequisition->currency->name ?? 'USD' }}" readonly />
                                <x-input label="Created By" value="{{ $paymentrequisition->createdBy->name ?? 'N/A' }}" readonly />
                                <x-input label="Total Amount" value="{{ $paymentrequisition->currency->name ?? 'USD' }} {{ number_format($paymentrequisition->total_amount, 2) }}" readonly />
                                @php
                                    $statusColor = match ($paymentrequisition->status) {
                                        'DRAFT' => 'badge-warning',
                                        'Submitted' => 'badge-info',
                                        'HOD_RECOMMENDED' => 'badge-info',
                                        'ADMIN_REVIEWED' => 'badge-info',
                                        'ADMIN_RECOMMENDED' => 'badge-info',
                                        'AWAITING_PAYMENT_VOUCHER' => 'badge-success',
                                        'Rejected' => 'badge-error',
                                        default => 'badge-ghost',
                                    };
                                @endphp
                                <x-input label="Status" readonly>
                                    <x-slot:value>
                                        <x-badge :value="$paymentrequisition->status" class="{{ $statusColor }} badge-sm" />
                                    </x-slot:value>
                                </x-input>
                            </div>
                            
                            @if($paymentrequisition->payee_type && $paymentrequisition->payee_name)
                                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                    <h4 class="text-md font-semibold mb-2 text-blue-800 dark:text-blue-300">Payee Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <x-input label="Payee Type" value="{{ $paymentrequisition->payee_type }}" readonly />
                                        <x-input label="Payee Registration Number" value="{{ $paymentrequisition->payee_regnumber ?? 'N/A' }}" readonly />
                                        <x-input label="Payee Name" value="{{ $paymentrequisition->payee_name ?? 'N/A' }}" readonly />
                                    </div>
                                </div>
                            @endif
                            
                            <div class="mt-3">
                                <x-textarea label="Purpose" readonly rows="3">{{ $paymentrequisition->purpose }}</x-textarea>
                            </div>
                        </div>

                        <!-- Line Items Section -->
                        @if($paymentrequisition->lineItems && $paymentrequisition->lineItems->count() > 0)
                            <div class="bg-white p-4 rounded-lg border">
                                <h3 class="text-lg font-semibold mb-3 text-gray-700">Line Items</h3>
                                <x-table :headers="[['key' => 'quantity', 'label' => 'Qty'], ['key' => 'description', 'label' => 'Description'], ['key' => 'unit_amount', 'label' => 'Unit Amount'], ['key' => 'line_total', 'label' => 'Total']]" :rows="$paymentrequisition->lineItems" class="table-xs">
                                    @scope('cell_unit_amount', $item)
                                        <div>{{ $paymentrequisition->currency->name ?? 'USD' }} {{ number_format($item->unit_amount, 2) }}</div>
                                    @endscope
                                    @scope('cell_line_total', $item)
                                        <div>{{ $paymentrequisition->currency->name ?? 'USD' }} {{ number_format($item->line_total, 2) }}</div>
                                    @endscope
                                </x-table>
                                <div class="mt-4 p-3 bg-base-200 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold">Grand Total:</span>
                                        <span class="text-lg font-bold">{{ $paymentrequisition->currency->name ?? 'USD' }} {{ number_format($paymentrequisition->total_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-tab>

                <!-- Approvals Tab -->
                <x-tab name="approvals" label="Approvals" icon="o-document-check">
                    <div class="mt-4 space-y-3">
                        @if ($paymentrequisition->workflow)
                            @foreach ($paymentrequisition->workflow->workflowparameters->sortBy('order') as $wp)
                                @php
                                    $approval = $paymentrequisition->approvals?->where('workflowparameter_id', $wp->id)->first();
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

                <!-- Comments Tab -->
                @if($paymentrequisition->comments && $paymentrequisition->comments->count() > 0)
                    <x-tab name="comments" label="Comments" icon="o-chat-bubble-left-right">
                        <div class="mt-4 space-y-3">
                            @foreach($paymentrequisition->comments as $comment)
                                <div class="bg-white p-3 rounded border">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold">{{ $comment['user_id'] ?? 'Unknown' }}</span>
                                        <span class="text-xs text-gray-500">{{ isset($comment['created_at']) ? \Carbon\Carbon::parse($comment['created_at'])->format('Y-m-d H:i:s') : 'N/A' }}</span>
                                    </div>
                                    <div class="text-sm text-gray-700">{{ $comment['comment'] ?? '--' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </x-tab>
                @endif
            </x-tabs>
        </x-card>
    @else
        <x-alert class="alert-error mt-5" title="Payment Requisition not found" />
    @endif
</div>
