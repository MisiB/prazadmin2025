<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
    link-item-class="text-base" />
    
    @if($voucher)
        <x-card title="Payment Voucher Details" separator class="mt-5 border-2 border-gray-200">
            <x-tabs wire:model="selectedTab">
                <!-- Details Tab -->
                <x-tab name="details" label="Details" icon="o-document-text">
                    <div class="space-y-4 mt-4">
                        <!-- Voucher Information -->
                        <div class="bg-white p-4 rounded-lg border">
                            <h3 class="text-lg font-semibold mb-3 text-gray-700">Voucher Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <x-input label="Voucher Number" value="{{ $voucher->voucher_number }}" readonly />
                                <x-input label="Voucher Date" value="{{ $voucher->voucher_date->format('Y-m-d') }}" readonly />
                                <x-input label="Currency" value="{{ $voucher->currency }}" readonly />
                                @if($voucher->bankAccount)
                                    <x-input label="Bank Account" value="{{ $voucher->bankAccount->account_number ?? 'N/A' }} - {{ $voucher->bankAccount->currency->name ?? 'N/A' }} ({{ $voucher->bankAccount->account_type ?? 'N/A' }})" readonly />
                                @endif
                                @if($voucher->exchange_rate)
                                    <x-input label="Exchange Rate" value="{{ number_format($voucher->exchange_rate, 4) }}" readonly />
                                @endif
                                <x-input label="Prepared By" value="{{ $voucher->preparedBy->name ?? 'N/A' }}" readonly />
                                @if($voucher->verified_by)
                                    <x-input label="Verified By" value="{{ $voucher->verifiedBy->name ?? 'N/A' }}" readonly />
                                @endif
                                @if($voucher->checked_by)
                                    <x-input label="Checked By" value="{{ $voucher->checkedBy->name ?? 'N/A' }}" readonly />
                                @endif
                                @if($voucher->finance_approved_by)
                                    <x-input label="Finance Approved By" value="{{ $voucher->financeApprovedBy->name ?? 'N/A' }}" readonly />
                                @endif
                                @if($voucher->ceo_approved_by)
                                    <x-input label="CEO Approved By" value="{{ $voucher->ceoApprovedBy->name ?? 'N/A' }}" readonly />
                                @endif
                                @php
                                    $statusColor = match ($voucher->status) {
                                        'DRAFT' => 'badge-warning',
                                        'VERIFIED' => 'badge-info',
                                        'CHECKED' => 'badge-info',
                                        'FINANCE_RECOMMENDED' => 'badge-success',
                                        'APPROVED_PAYMENT_PROCESSED' => 'badge-success',
                                        'REJECTED' => 'badge-error',
                                        default => 'badge-ghost',
                                    };
                                @endphp
                                <x-input label="Status" readonly>
                                    <x-slot:value>
                                        <x-badge :value="$voucher->status" class="{{ $statusColor }} badge-sm" />
                                    </x-slot:value>
                                </x-input>
                            </div>
                        </div>

                        <!-- Voucher Items Section -->
                        @if($voucher->items && $voucher->items->count() > 0)
                            <div class="bg-white p-4 rounded-lg border">
                                <h3 class="text-lg font-semibold mb-3 text-gray-700">Voucher Items ({{ $voucher->items->count() }})</h3>
                                <x-table :headers="[['key' => 'source_type', 'label' => 'Source'], ['key' => 'description', 'label' => 'Description'], ['key' => 'gl_code', 'label' => 'GL Code'], ['key' => 'view_history', 'label' => 'View Line History'], ['key' => 'original_currency', 'label' => 'Currency'], ['key' => 'original_amount', 'label' => 'Original Amount'], ['key' => 'payable_amount', 'label' => 'Payable Amount']]" :rows="$voucher->items" class="table-xs">
                                    @scope('cell_view_history', $item)
                                        <x-button 
                                            icon="o-eye" 
                                            class="btn-info btn-xs"
                                            wire:click="viewItemDetails({{ $item->id }})"
                                            label="View"
                                        />
                                    @endscope
                                    @scope('cell_original_amount', $item)
                                        <div>{{ $item->original_currency }} {{ number_format($item->original_amount, 2) }}</div>
                                    @endscope
                                    @scope('cell_payable_amount', $item)
                                        <div>{{ $item->voucher_currency ?? 'USD' }} {{ number_format($item->payable_amount, 2) }}</div>
                                    @endscope
                                </x-table>
                                <div class="mt-4 p-3 bg-base-200 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="font-semibold">Grand Total:</span>
                                        <span class="text-lg font-bold">{{ $voucher->currency }} {{ number_format($voucher->total_amount, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-tab>

                <!-- Audit Log Tab -->
                <x-tab name="audit" label="Audit Trail" icon="o-document-check">
                    <div class="mt-4 space-y-3">
                        @if ($voucher->auditLogs && $voucher->auditLogs->count() > 0)
                            @foreach ($voucher->auditLogs->sortBy('timestamp') as $log)
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
        </x-card>

        <!-- View Item Details Modal -->
        <x-modal title="Item Details" wire:model="viewItemModal" box-class="max-w-6xl" separator>
            @if($viewedItemDetails)
                @if($viewedItemSourceType === 'PAYMENT_REQUISITION')
                    @include('livewire.admin.workflows.partials.payment-requisition-details', [
                        'requisitionDetails' => $viewedItemDetails,
                        'viewedLineItemId' => $viewedItemLineId
                    ])
                @elseif($viewedItemSourceType === 'TNS')
                    @include('livewire.admin.workflows.partials.ts-allowance-details', ['allowanceDetails' => $viewedItemDetails])
                @elseif($viewedItemSourceType === 'STAFF_WELFARE')
                    @include('livewire.admin.workflows.partials.staff-welfare-details', ['loanDetails' => $viewedItemDetails])
                @endif
            @endif

            <x-slot:actions>
                <x-button label="Close" @click="$wire.closeViewItemModal()" />
            </x-slot:actions>
        </x-modal>
    @endif
</div>
