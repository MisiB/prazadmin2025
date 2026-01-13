@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp
<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
    link-item-class="text-base" />

    <x-card title="Payment Voucher Approvals" separator class="mt-5 border-2 border-gray-200">
        @if ($workflow)
            <div class="space-y-4">
                {{-- Workflow Parameter Stages --}}
                @foreach ($workflow->workflowparameters->sortBy('order') as $workflowparameter)
                    @php
                        $stageVouchers = $vouchers->where('status', $workflowparameter->status);
                        $voucherCount = $stageVouchers->count();
                        $isExpanded = isset($expandedStages[$workflowparameter->status]);
                    @endphp

                    @can($workflowparameter->permission->name)
                        <div class="border-2 rounded-lg border-gray-200 shadow-sm">
                            <!-- Stage Header -->
                            <div class="p-4 bg-gray-50 rounded-t-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                wire:click="toggleStage('{{ $workflowparameter->status }}')">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <x-icon name="o-chevron-{{ $isExpanded ? 'down' : 'right' }}" class="w-5 h-5" />
                                        <div>
                                            <div class="text-xl font-bold">{{ $workflowparameter->name ?? $workflowparameter->status }}</div>
                                            <div class="text-sm text-gray-600">{{ $workflowparameter->status }}</div>
                                        </div>
                                    </div>
                                    <x-badge value="{{ $voucherCount }}" class="badge-secondary badge-lg" />
                                </div>
                            </div>

                            <!-- Stage Content - Vouchers List -->
                            @if ($isExpanded)
                                <div class="p-4 space-y-3">
                                    @forelse ($stageVouchers as $voucher)
                                        @php
                                            $isVoucherExpanded = $this->isVoucherExpanded($voucher->uuid);
                                        @endphp
                                        <div class="border border-gray-200 rounded-lg shadow-sm">
                                            <!-- Voucher Header -->
                                            <div class="p-3 bg-white rounded-t-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                                wire:click="toggleVoucher('{{ $voucher->uuid }}')">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-4">
                                                        <x-icon name="o-chevron-{{ $isVoucherExpanded ? 'down' : 'right' }}" class="w-4 h-4" />
                                                        <div>
                                                            <div class="font-semibold">{{ $voucher->voucher_number }}</div>
                                                            <div class="text-sm text-gray-600">{{ $voucher->voucher_date->format('Y-m-d') }} - {{ $voucher->currency }} {{ number_format($voucher->total_amount, 2) }}</div>
                                                            <div class="text-xs text-gray-500">{{ $voucher->items->count() }} item(s)</div>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        @if($voucher->status !== 'CEO_APPROVED' && $voucher->status !== 'REJECTED')
                                                            <x-button icon="o-check-circle" class="btn-xs btn-primary" 
                                                                wire:click.stop="openDecisionModal('{{ $voucher->uuid }}', {{ $voucher->id }})" 
                                                                label="Make Decision" />
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Voucher Details (Expanded) -->
                                            @if ($isVoucherExpanded)
                                                @php
                                                    $voucherDetails = $this->getVoucherByUuid($voucher->uuid);
                                                @endphp
                                                <div class="p-4 bg-gray-50 space-y-4">
                                                    <x-tabs wire:model="selectedTabs.{{ $voucher->uuid }}">
                                                        <!-- Details Tab -->
                                                        <x-tab name="details-{{ $voucher->uuid }}" label="Details" icon="o-document-text">
                                                            <div class="space-y-4 mt-4">
                                                                <!-- Voucher Information -->
                                                                <div class="bg-white p-4 rounded-lg border">
                                                                    <h3 class="text-lg font-semibold mb-3 text-gray-700">Voucher Information</h3>
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                                        <x-input label="Voucher Number" value="{{ $voucherDetails->voucher_number }}" readonly />
                                                                        <x-input label="Voucher Date" value="{{ $voucherDetails->voucher_date->format('Y-m-d') }}" readonly />
                                                                        <x-input label="Currency" value="{{ $voucherDetails->currency }}" readonly />
                                                                        @if($voucherDetails->bankAccount)
                                                                            <x-input label="Bank Account" value="{{ $voucherDetails->bankAccount->account_number }} - {{ $voucherDetails->bankAccount->currency->name ?? '' }} ({{ $voucherDetails->bankAccount->account_type }})" readonly />
                                                                        @endif
                                                                        @if($voucherDetails->exchange_rate)
                                                                            <x-input label="Exchange Rate" value="{{ number_format($voucherDetails->exchange_rate, 4) }}" readonly />
                                                                        @endif
                                                                        <x-input label="Prepared By" value="{{ $voucherDetails->preparedBy->name ?? 'N/A' }}" readonly />
                                                                        @if($voucherDetails->verified_by)
                                                                            <x-input label="Verified By" value="{{ $voucherDetails->verifiedBy->name ?? 'N/A' }}" readonly />
                                                                        @endif
                                                                        @if($voucherDetails->checked_by)
                                                                            <x-input label="Checked By" value="{{ $voucherDetails->checkedBy->name ?? 'N/A' }}" readonly />
                                                                        @endif
                                                                        @if($voucherDetails->finance_approved_by)
                                                                            <x-input label="Finance Approved By" value="{{ $voucherDetails->financeApprovedBy->name ?? 'N/A' }}" readonly />
                                                                        @endif
                                                                        @if($voucherDetails->ceo_approved_by)
                                                                            <x-input label="CEO Approved By" value="{{ $voucherDetails->ceoApprovedBy->name ?? 'N/A' }}" readonly />
                                                                        @endif
                                                                        @php
                                                                            $statusColor = match ($voucherDetails->status) {
                                                                                'DRAFT' => 'badge-warning',
                                                                                'SUBMITTED' => 'badge-info',
                                                                                'VERIFIED' => 'badge-info',
                                                                                'CHECKED' => 'badge-info',
                                                                                'FINANCE_RECOMMENDED' => 'badge-success',
                                                                                'CEO_APPROVED' => 'badge-success',
                                                                                'REJECTED' => 'badge-error',
                                                                                default => 'badge-ghost',
                                                                            };
                                                                        @endphp
                                                                        <x-input label="Status" readonly>
                                                                            <x-slot:value>
                                                                                <x-badge :value="$voucherDetails->status" class="{{ $statusColor }} badge-sm" />
                                                                            </x-slot:value>
                                                                        </x-input>
                                                                    </div>
                                                                </div>

                                                                <!-- Voucher Items Section -->
                                                                @if($voucherDetails->items && $voucherDetails->items->count() > 0)
                                                                    <div class="bg-white p-4 rounded-lg border">
                                                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Voucher Items ({{ $voucherDetails->items->count() }})</h3>
                                                                        <div class="space-y-2">
                                                                            @foreach($voucherDetails->items as $item)
                                                                                @php
                                                                                    $itemId = $item->id;
                                                                                    $isItemExpanded = $this->isItemExpanded($itemId);
                                                                                    $sourceRecord = $isItemExpanded ? $this->getSourceRecord($item->source_type, $item->source_id) : null;
                                                                                @endphp
                                                                                <div class="border border-gray-200 rounded-lg">
                                                                                    <!-- Item Header -->
                                                                                    <div class="p-3 bg-white rounded-t-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                                                                        wire:click="toggleItem({{ $itemId }})">
                                                                                        <div class="flex items-start gap-3">
                                                                                            <x-icon 
                                                                                                name="o-chevron-{{ $isItemExpanded ? 'down' : 'right' }}" 
                                                                                                class="w-4 h-4 mt-1 flex-shrink-0" 
                                                                                            />
                                                                                        
                                                                                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 text-sm w-full">
                                                                                                
                                                                                                <!-- Source -->
                                                                                                <div>
                                                                                                    <div class="text-xs text-gray-500">Source</div>
                                                                                                    <div class="font-semibold break-words">
                                                                                                        {{ $item->source_type }}
                                                                                                    </div>
                                                                                                </div>
                                                                                        
                                                                                                <!-- Description -->
                                                                                                <div class="col-span-2">
                                                                                                    <div class="text-xs text-gray-500">Description</div>
                                                                                                    <div
                                                                                                        class="font-semibold text-gray-900
                                                                                                               {{ $isItemExpanded ? 'whitespace-normal break-words' : 'truncate' }}"
                                                                                                        title="{{ $item->description }}"
                                                                                                    >
                                                                                                        {{ $item->description }}
                                                                                                    </div>
                                                                                                </div>
                                                                                        
                                                                                                <!-- Currency -->
                                                                                                <div>
                                                                                                    <div class="text-xs text-gray-500">Currency</div>
                                                                                                    <div class="font-semibold">
                                                                                                        {{ $item->original_currency }}
                                                                                                    </div>
                                                                                                </div>
                                                                                        
                                                                                                <!-- Original -->
                                                                                                <div>
                                                                                                    <div class="text-xs text-gray-500">Original</div>
                                                                                                    <div class="font-semibold whitespace-nowrap">
                                                                                                        {{ $item->original_currency }} {{ number_format($item->original_amount, 2) }}
                                                                                                    </div>
                                                                                                </div>
                                                                                        
                                                                                                <!-- Payable -->
                                                                                                <div>
                                                                                                    <div class="text-xs text-gray-500">Payable</div>
                                                                                                    <div class="font-semibold whitespace-nowrap">
                                                                                                        {{ $item->voucher_currency ?? 'USD' }} {{ number_format($item->payable_amount, 2) }}
                                                                                                    </div>
                                                                                                </div>
                                                                                        
                                                                                                <!-- Account Type -->
                                                                                                <div>
                                                                                                    <div class="text-xs text-gray-500">Account Type</div>
                                                                                                    <div class="font-semibold break-words">
                                                                                                        {{ $item->account_type ?? 'N/A' }}
                                                                                                    </div>
                                                                                                </div>
                                                                                        
                                                                                                <!-- GL Code -->
                                                                                                <div>
                                                                                                    <div class="text-xs text-gray-500">GL Code</div>
                                                                                                    <div class="font-semibold break-words">
                                                                                                        {{ $item->gl_code ?? 'N/A' }}
                                                                                                    </div>
                                                                                                </div>
                                                                                        
                                                                                            </div>
                                                                                        </div>
                                                                                        
                                                                                    </div>

                                                                                    <!-- Item Details (Expanded) -->
                                                                                    @if ($isItemExpanded)
                                                                                        <div class="p-4 bg-gray-50 border-t">
                                                                                            <x-tabs wire:model="selectedTabs.item-{{ $itemId }}">
                                                                                                <!-- Voucher Item Details Tab -->
                                                                                                <x-tab name="voucher-item-{{ $itemId }}" label="Voucher Item Details" icon="o-document-text">
                                                                                                    <div class="mt-4">
                                                                                                        <div class="bg-white p-4 rounded-lg border">
                                                                                                            <h4 class="text-md font-semibold mb-3">Voucher Item Information</h4>
                                                                                                            <div class="overflow-x-auto">
                                                                                                                <div class="flex flex-wrap gap-4 items-end min-w-max">
                                                                                                                    <x-input label="Source Type" value="{{ $item->source_type }}" readonly class="min-w-[120px]" />
                                                                                                                    <x-input label="Description" value="{{ $item->description }}" readonly class="min-w-[200px]" />
                                                                                                                    <x-input label="Original Currency" value="{{ $item->original_currency }}" readonly class="min-w-[120px]" />
                                                                                                                    <x-input label="Original Amount" value="{{ $item->original_currency }} {{ number_format($item->original_amount, 2) }}" readonly class="min-w-[150px]" />
                                                                                                                    @if($item->edited_amount)
                                                                                                                        <x-input label="Edited Amount" value="{{ $item->original_currency }} {{ number_format($item->edited_amount, 2) }}" readonly class="min-w-[150px]" />
                                                                                                                        <x-input label="Amount Change Comment" value="{{ $item->amount_change_comment ?? 'N/A' }}" readonly class="min-w-[200px]" />
                                                                                                                    @endif
                                                                                                                    <x-input label="Payable Amount" value="{{ $item->voucher_currency ?? 'USD' }} {{ number_format($item->payable_amount, 2) }}" readonly class="min-w-[150px]" />
                                                                                                                    <x-input label="Account Type" value="{{ $item->account_type ?? 'N/A' }}" readonly class="min-w-[120px]" />
                                                                                                                    <x-input label="GL Code" value="{{ $item->gl_code ?? 'N/A' }}" readonly class="min-w-[120px]" />
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </x-tab>
                                                                                                <!-- Source Details Tab -->
                                                                                                <x-tab name="details-item-{{ $itemId }}" label="Source Details" icon="o-document-text">
                                                                                                    <div class="mt-4 space-y-4">
                                                                                                        @if($sourceRecord)
                                                                                                            @if($item->source_type === 'PAYMENT_REQUISITION')
                                                                                                            <div class="bg-white p-4 rounded-lg border">
                                                                                                                <h4 class="text-md font-semibold mb-3">Payment Requisition Information</h4>
                                                                                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                                                                                    <x-input label="Reference Number" value="{{ $sourceRecord->reference_number }}" readonly />
                                                                                                                    <x-input label="Purpose" value="{{ $sourceRecord->purpose }}" readonly />
                                                                                                                    <x-input label="Department" value="{{ $sourceRecord->department->name ?? 'N/A' }}" readonly />
                                                                                                                    <x-input label="Budget Line Item" value="{{ $sourceRecord->budgetLineItem->activity ?? 'N/A' }}" readonly />
                                                                                                                    <x-input label="Currency" value="{{ $sourceRecord->currency->name ?? 'USD' }}" readonly />
                                                                                                                    <x-input label="Total Amount" value="{{ $sourceRecord->currency->name ?? 'USD' }} {{ number_format($sourceRecord->total_amount, 2) }}" readonly />
                                                                                                                    <x-input label="Status" value="{{ $sourceRecord->status }}" readonly />
                                                                                                                    <x-input label="Created By" value="{{ $sourceRecord->createdBy->name ?? 'N/A' }}" readonly />
                                                                                                                </div>
                                                                                                                @if($sourceRecord->lineItems && $sourceRecord->lineItems->count() > 0)
                                                                                                                    <div class="mt-4">
                                                                                                                        <h5 class="text-sm font-semibold mb-2">Line Items</h5>
                                                                                                                        <x-table :headers="[['key' => 'quantity', 'label' => 'Qty'], ['key' => 'description', 'label' => 'Description'], ['key' => 'unit_amount', 'label' => 'Unit Amount'], ['key' => 'line_total', 'label' => 'Total']]" :rows="$sourceRecord->lineItems" class="table-xs">
                                                                                                                            @scope('cell_unit_amount', $lineItem)
                                                                                                                                <div>{{ $sourceRecord->currency->name ?? 'USD' }} {{ number_format($lineItem->unit_amount, 2) }}</div>
                                                                                                                            @endscope
                                                                                                                            @scope('cell_line_total', $lineItem)
                                                                                                                                <div>{{ $sourceRecord->currency->name ?? 'USD' }} {{ number_format($lineItem->line_total, 2) }}</div>
                                                                                                                            @endscope
                                                                                                                        </x-table>
                                                                                                                    </div>
                                                                                                                @endif
                                                                                                            </div>
                                                                                                        @elseif($item->source_type === 'TNS')
                                                                                                            <div class="bg-white p-4 rounded-lg border">
                                                                                                                <h4 class="text-md font-semibold mb-3">Travel & Subsistence Allowance Information</h4>
                                                                                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                                                                                    <x-input label="Application Number" value="{{ $sourceRecord->application_number }}" readonly />
                                                                                                                    <x-input label="Applicant" value="{{ $sourceRecord->full_name }}" readonly />
                                                                                                                    <x-input label="Department" value="{{ $sourceRecord->department->name ?? 'N/A' }}" readonly />
                                                                                                                    <x-input label="Trip Start Date" value="{{ $sourceRecord->trip_start_date->format('Y-m-d') }}" readonly />
                                                                                                                    <x-input label="Trip End Date" value="{{ $sourceRecord->trip_end_date->format('Y-m-d') }}" readonly />
                                                                                                                    <x-input label="Balance Due" value="{{ $sourceRecord->currency->name ?? 'USD' }} {{ number_format($sourceRecord->balance_due, 2) }}" readonly />
                                                                                                                    <x-input label="Status" value="{{ $sourceRecord->status }}" readonly />
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        @elseif($item->source_type === 'STAFF_WELFARE')
                                                                                                            <div class="bg-white p-4 rounded-lg border">
                                                                                                                <h4 class="text-md font-semibold mb-3">Staff Welfare Loan Information</h4>
                                                                                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                                                                                    <x-input label="Loan Number" value="{{ $sourceRecord->loan_number }}" readonly />
                                                                                                                    <x-input label="Applicant" value="{{ $sourceRecord->applicant->name ?? 'N/A' }}" readonly />
                                                                                                                    <x-input label="Department" value="{{ $sourceRecord->department->name ?? 'N/A' }}" readonly />
                                                                                                                    <x-input label="Loan Purpose" value="{{ $sourceRecord->loan_purpose }}" readonly />
                                                                                                                    <x-input label="Loan Amount" value="{{ number_format($sourceRecord->loan_amount_requested, 2) }}" readonly />
                                                                                                                    <x-input label="Status" value="{{ $sourceRecord->status }}" readonly />
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        @endif
                                                                                                    @else
                                                                                                        <x-alert class="alert-info" title="Source record not found."/>
                                                                                                    @endif
                                                                                                    </div>
                                                                                                </x-tab>

                                                                                                <!-- History Tab -->
                                                                                                <x-tab name="history-item-{{ $itemId }}" label="History" icon="o-clock">
                                                                                                    <div class="mt-4 space-y-3">
                                                                                                        @if($sourceRecord && $item->source_type === 'PAYMENT_REQUISITION' && $sourceRecord->approvals && $sourceRecord->approvals->count() > 0)
                                                                                                            @foreach ($sourceRecord->approvals->sortBy('created_at') as $approval)
                                                                                                                <div class="border rounded-lg p-3 bg-white">
                                                                                                                    <div class="flex items-center justify-between mb-2">
                                                                                                                        <div class="flex items-center gap-2">
                                                                                                                            <x-badge :value="$approval->status" class="badge-info badge-sm" />
                                                                                                                            <span class="text-sm text-gray-600">{{ $approval->created_at->format('Y-m-d H:i:s') }}</span>
                                                                                                                        </div>
                                                                                                                        <div class="text-sm">
                                                                                                                            <span class="font-semibold">{{ $approval->user->name ?? 'N/A' }}</span>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                    @if($approval->comment)
                                                                                                                        <div class="text-sm text-gray-700 mt-2">
                                                                                                                            <strong>Comment:</strong> {{ $approval->comment }}
                                                                                                                        </div>
                                                                                                                    @endif
                                                                                                                </div>
                                                                                                            @endforeach
                                                                                                        @elseif($sourceRecord && $item->source_type === 'TNS' && $sourceRecord->comments && $sourceRecord->comments->count() > 0)
                                                                                                            @foreach ($sourceRecord->comments as $comment)
                                                                                                                <div class="border rounded-lg p-3 bg-white">
                                                                                                                    <div class="text-sm text-gray-600 mb-1">
                                                                                                                        {{ isset($comment['created_at']) ? \Carbon\Carbon::parse($comment['created_at'])->format('Y-m-d H:i:s') : 'N/A' }}
                                                                                                                    </div>
                                                                                                                    <div class="text-sm text-gray-700">{{ $comment['comment'] ?? '--' }}</div>
                                                                                                                </div>
                                                                                                            @endforeach
                                                                                                        @elseif($sourceRecord && $item->source_type === 'STAFF_WELFARE' && $sourceRecord->comments && $sourceRecord->comments->count() > 0)
                                                                                                            @foreach ($sourceRecord->comments as $comment)
                                                                                                                <div class="border rounded-lg p-3 bg-white">
                                                                                                                    <div class="text-sm text-gray-600 mb-1">
                                                                                                                        {{ isset($comment['created_at']) ? \Carbon\Carbon::parse($comment['created_at'])->format('Y-m-d H:i:s') : 'N/A' }}
                                                                                                                    </div>
                                                                                                                    <div class="text-sm text-gray-700">{{ $comment['comment'] ?? '--' }}</div>
                                                                                                                </div>
                                                                                                            @endforeach
                                                                                                        @else
                                                                                                            <x-alert class="alert-info" title="No history available."/>
                                                                                                        @endif
                                                                                                    </div>
                                                                                                </x-tab>

                                                                                                <!-- Attachments Tab -->
                                                                                                <x-tab name="attachments-item-{{ $itemId }}" label="Attachments" icon="o-paper-clip">
                                                                                                    <div class="mt-4 space-y-3">
                                                                                                        @if($sourceRecord && $item->source_type === 'PAYMENT_REQUISITION' && $sourceRecord->documents && $sourceRecord->documents->count() > 0)
                                                                                                            @foreach ($sourceRecord->documents as $document)
                                                                                                                <div class="border rounded-lg p-3 bg-white flex items-center justify-between">
                                                                                                                    <div>
                                                                                                                        <div class="font-semibold">{{ $document->document }}</div>
                                                                                                                        <div class="text-sm text-gray-500">{{ $document->document_type }}</div>
                                                                                                                    </div>
                                                                                                                    <x-button icon="o-arrow-down-tray" class="btn-xs btn-outline" 
                                                                                                                        link="{{ Storage::url($document->filepath) }}" 
                                                                                                                        target="_blank" 
                                                                                                                        label="Download" />
                                                                                                                </div>
                                                                                                            @endforeach
                                                                                                        @elseif($sourceRecord && $item->source_type === 'TNS')
                                                                                                            <div class="space-y-3">
                                                                                                                @if($sourceRecord->trip_attachment_path)
                                                                                                                    <div class="border rounded-lg p-3 bg-white flex items-center justify-between">
                                                                                                                        <div>
                                                                                                                            <div class="font-semibold">Trip Attachment</div>
                                                                                                                            <div class="text-sm text-gray-500">Travel Document</div>
                                                                                                                        </div>
                                                                                                                        <x-button icon="o-arrow-down-tray" class="btn-xs btn-outline" 
                                                                                                                            link="{{ Storage::url($sourceRecord->trip_attachment_path) }}" 
                                                                                                                            target="_blank" 
                                                                                                                            label="Download" />
                                                                                                                    </div>
                                                                                                                @endif
                                                                                                                @if($sourceRecord->proof_of_payment_path)
                                                                                                                    <div class="border rounded-lg p-3 bg-white flex items-center justify-between">
                                                                                                                        <div>
                                                                                                                            <div class="font-semibold">Proof of Payment</div>
                                                                                                                            <div class="text-sm text-gray-500">Payment Document</div>
                                                                                                                        </div>
                                                                                                                        <x-button icon="o-arrow-down-tray" class="btn-xs btn-outline" 
                                                                                                                            link="{{ Storage::url($sourceRecord->proof_of_payment_path) }}" 
                                                                                                                            target="_blank" 
                                                                                                                            label="Download" />
                                                                                                                    </div>
                                                                                                                @endif
                                                                                                                @if(!$sourceRecord->trip_attachment_path && !$sourceRecord->proof_of_payment_path)
                                                                                                                    <x-alert class="alert-info" title="No attachments available."/>
                                                                                                                @endif
                                                                                                            </div>
                                                                                                        @elseif($sourceRecord && $item->source_type === 'STAFF_WELFARE')
                                                                                                            @if($sourceRecord->proof_of_payment_path)
                                                                                                                <div class="border rounded-lg p-3 bg-white flex items-center justify-between">
                                                                                                                    <div>
                                                                                                                        <div class="font-semibold">Proof of Payment</div>
                                                                                                                        <div class="text-sm text-gray-500">Payment Document</div>
                                                                                                                    </div>
                                                                                                                    <x-button icon="o-arrow-down-tray" class="btn-xs btn-outline" 
                                                                                                                        link="{{ Storage::url($sourceRecord->proof_of_payment_path) }}" 
                                                                                                                        target="_blank" 
                                                                                                                        label="Download" />
                                                                                                                </div>
                                                                                                            @else
                                                                                                                <x-alert class="alert-info" title="No attachments available."/>
                                                                                                            @endif
                                                                                                        @else
                                                                                                            <x-alert class="alert-info" title="No attachments available."/>
                                                                                                        @endif
                                                                                                    </div>
                                                                                                </x-tab>
                                                                                            </x-tabs>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                        <div class="mt-4 p-3 bg-base-200 rounded-lg">
                                                                            <div class="flex justify-between items-center">
                                                                                <span class="font-semibold">Grand Total:</span>
                                                                                <span class="text-lg font-bold">{{ $voucherDetails->currency }} {{ number_format($voucherDetails->total_amount, 2) }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </x-tab>

                                                        <!-- Audit Log Tab -->
                                                        <x-tab name="audit-{{ $voucher->uuid }}" label="Audit Trail" icon="o-document-check">
                                                            <div class="mt-4 space-y-3">
                                                                @if ($voucherDetails->auditLogs && $voucherDetails->auditLogs->count() > 0)
                                                                    @foreach ($voucherDetails->auditLogs->sortBy('timestamp') as $log)
                                                                        <div class="border rounded-lg p-4 bg-white">
                                                                            <div class="flex items-center justify-between mb-2">
                                                                                <div class="flex items-center gap-2">
                                                                                    <x-badge :value="$log->action" class="badge-info badge-sm" />
                                                                                    <span class="text-sm text-gray-600">{{ $log->timestamp->format('Y-m-d H:i:s') }}</span>
                                                                                </div>
                                                                                <div class="text-sm">
                                                                                    <span class="font-semibold">{{ $log->user->name ?? 'N/A' }}</span>
                                                                                    @if($log->role)
                                                                                        <span class="text-gray-500">({{ $log->role }})</span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                            @if($log->old_status || $log->new_status)
                                                                                <div class="text-sm text-gray-600 mb-2">
                                                                                    Status: 
                                                                                    @if($log->old_status)
                                                                                        <span class="badge badge-warning badge-xs">{{ $log->old_status }}</span>
                                                                                    @endif
                                                                                    @if($log->old_status && $log->new_status)
                                                                                        <span class="mx-2">→</span>
                                                                                    @endif
                                                                                    @if($log->new_status)
                                                                                        <span class="badge badge-success badge-xs">{{ $log->new_status }}</span>
                                                                                    @endif
                                                                                </div>
                                                                            @endif
                                                                            @if($log->comments)
                                                                                <div class="text-sm text-gray-700 mt-2">
                                                                                    <strong>Comment:</strong> {{ $log->comments }}
                                                                                </div>
                                                                            @endif
                                                                            @if($log->exchange_rate)
                                                                                <div class="text-sm text-gray-600 mt-1">
                                                                                    Exchange Rate: {{ number_format($log->exchange_rate, 4) }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <x-alert class="alert-info" title="No audit logs available."/>
                                                                @endif
                                                            </div>
                                                        </x-tab>
                                                    </x-tabs>
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <x-alert class="alert-info" title="No vouchers in this stage."/>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    @endcan
                @endforeach
            </div>
        @else
            <x-alert class="alert-warning" title="Payment Voucher Workflow Not Configured" />
        @endif
    </x-card>

    <!-- Decision Modal -->
    <x-modal wire:model="decisionmodal" title="Make Decision">
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
</div>
