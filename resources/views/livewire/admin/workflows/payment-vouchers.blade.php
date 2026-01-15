<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-base" />
    <x-card title="Payment Vouchers" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..."/>
            @can('payment.voucher.create')
                <x-button icon="o-plus" class="btn-primary" label="New Voucher" @click="$wire.openModal()"/>
            @endcan
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$vouchers" class="table-zebra table-xs">
            @scope("cell_voucher_number", $voucher)
            <div>{{ $voucher->voucher_number }}</div>
            @endscope
            @scope("cell_voucher_date", $voucher)
            <div>{{ $voucher->voucher_date->format('Y-m-d') }}</div>
            @endscope
            @scope("cell_currency", $voucher)
            <div>{{ $voucher->currency }}</div>
            @endscope
            @scope("cell_total_amount", $voucher)
            <div>{{ $voucher->currency }} {{ number_format($voucher->total_amount, 2) }}</div>
            @endscope
            @scope("cell_status", $voucher)
            @php
                $statusColors = [
                    'DRAFT' => 'badge-warning',
                    'SUBMITTED' => 'badge-info',
                    'VERIFIED' => 'badge-info',
                    'CHECKED' => 'badge-info',
                    'FINANCE_RECOMMENDED' => 'badge-success',
                    'APPROVED_PAYMENT_PROCESSED' => 'badge-success',
                    'REJECTED' => 'badge-error',
                ];
                $color = $statusColors[$voucher->status] ?? 'badge-ghost';
            @endphp
            <x-badge :value="$voucher->status" class="{{ $color }} badge-sm" />
            @endscope
            @scope("cell_action", $voucher)
            <div class="flex items-center space-x-2">
                <x-button icon="o-eye" class="btn-xs btn-success btn-outline" link="{{ route('admin.paymentvoucher.show', $voucher->uuid) }}"/>
                @if($voucher->status == "DRAFT")
                    @can("payment.voucher.edit")
                    <x-button icon="o-pencil" class="btn-xs btn-info btn-outline" wire:click="edit({{ $voucher->id }})" spinner/>
                    @endcan
                    @can("payment.voucher.submit")
                    <x-button icon="o-paper-airplane" class="btn-xs btn-primary btn-outline" wire:click="submit({{ $voucher->id }})" wire:confirm="Are you sure you want to submit this voucher?" spinner/>
                    @endcan
                    @can("payment.voucher.delete")
                    <x-button icon="o-trash" class="btn-xs btn-outline btn-error" wire:click="delete({{ $voucher->id }})" wire:confirm="Are you sure?" spinner/>
                    @endcan
                @endif
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No Payment Vouchers found."/>
            </x-slot:empty>
        </x-table>
        
        <div class="mt-4">
            {{ $vouchers->links() }}
        </div>
    </x-card>

    <x-modal title="{{ $id ? 'Edit Payment Voucher' : 'New Payment Voucher' }}" wire:model="modal" box-class="max-w-6xl" separator>
        <x-form wire:submit="save">
            <div class="grid grid-cols-2 gap-4">
                <x-input wire:model="voucher_date" type="date" label="Voucher Date" />
                <x-select wire:model="bank_account_id" label="Bank Account" :options="$bankAccounts" placeholder="Select Bank Account" option-label="name" option-value="id" required />
            </div>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <x-select wire:model.live="currency" label="Currency" :options="$currencies" placeholder="Select Currency" option-label="name" option-value="name" />
                @if($currency === 'ZiG')
                    @php
                        $hasDifferentCurrency = collect($selectedItems)->contains(function ($item) {
                            return $item['original_currency'] !== 'ZiG';
                        });
                    @endphp
                    @if($hasDifferentCurrency)
                        <x-input wire:model="exchange_rate" type="number" step="0.0001" label="Exchange Rate (ZiG)" min="0" required />
                    @else
                        <x-input wire:model="exchange_rate" type="number" step="0.0001" label="Exchange Rate (ZiG) - Optional" min="0" />
                        <p class="text-xs text-gray-500 mt-1">Not required if all items are already in ZiG</p>
                    @endif
                @endif
            </div>

            <div class="divider">Select Items for Payment</div>

            @if(count($eligibleItems) > 0)
                <div class="max-h-96 overflow-y-auto border rounded-lg p-4 relative">
                    <table class="table table-xs table-zebra">
                        <thead class="sticky top-0 bg-base-100 z-10">
                            <tr>
                                <th class="bg-base-100">Select</th>
                                <th class="bg-base-100">Source</th>
                                <th class="bg-base-100">Action</th>
                                <th class="bg-base-100">Reference</th>
                                <th class="bg-base-100">Description</th>
                                <th class="bg-base-100">Currency</th>
                                <th class="bg-base-100">Original Amount</th>
                                <th class="bg-base-100">Remaining Balance</th>
                                <th class="bg-base-100">Payee Reg Number</th>
                                <th class="bg-base-100">Payee Name</th>
                                <th class="bg-base-100">Department</th>
                                {{-- <th class="bg-base-100">Action</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eligibleItems as $index => $item)
                                <tr>
                                    <td>
                                        <x-checkbox wire:click="toggleItem({{ $index }})" :checked="$this->isItemSelected($item)" />
                                    </td>
                                    <td>{{ $item['source_type'] }}</td>
                                    <td>
                                        <x-button 
                                            icon="o-eye" 
                                            class="btn-xs btn-info btn-outline" 
                                            wire:click="viewItemDetails({{ $index }})"
                                            spinner="viewItemDetails({{ $index }})"
                                        />
                                    </td>
                                    <td>{{ $item['reference'] }}</td>
                                    <td class="max-w-xs truncate" title="{{ $item['description'] }}">{{ $item['description'] }}</td>
                                    <td>{{ $item['original_currency'] }}</td>
                                    <td>
                                        <div class="text-xs">
                                            <div>{{ number_format($item['original_amount'], 2) }}</div>
                                            @if(isset($item['total_paid']) && $item['total_paid'] > 0)
                                                <div class="text-gray-500">Paid: {{ number_format($item['total_paid'], 2) }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $remainingBalance = $item['remaining_balance'] ?? $item['original_amount'] ?? 0;
                                        @endphp
                                        <span class="text-green-600 font-semibold">{{ number_format($remainingBalance, 2) }}</span>
                                    </td>
                                    <td>{{ $item['payee_regnumber'] ?? '-' }}</td>
                                    <td>{{ $item['payee_name'] ?? '-' }}</td>
                                    <td>{{ $item['department'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-alert class="alert-info" title="No eligible items available. All items may already be on vouchers or not in AWAITING_PAYMENT status."/>
            @endif

            @if(count($selectedItems) > 0)
                <div class="divider">Selected Items ({{ count($selectedItems) }})</div>
                <div class="max-h-64 overflow-y-auto border rounded-lg p-4">
                    <table class="table table-xs table-zebra">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>Payee Reg Number</th>
                                <th>Payee Name</th>
                                <th>Description</th>
                                @if($currency === 'ZiG' && $exchange_rate)
                                    <th>Apply Rate to ZiG</th>
                                @endif
                                <th>Currency</th>
                                <th>Amount Details</th>
                                <th>Amount Change</th>
                                <th>Comment (if changed)</th>
                                <th>Partial Amount</th>
                                <th>Account Type</th>
                                <th>GL Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($selectedItems as $index => $item)
                                @php
                                    $originalAmount = (float) ($item['original_amount'] ?? 0);
                                    $amountChange = $item['amount_change'] ?? null;
                                    $partialAmount = $item['partial_amount'] ?? null;
                                    $remainingBalance = (float) ($item['remaining_balance'] ?? $originalAmount);
                                    
                                    // Amount is changed if it differs from original amount
                                    $isAmountChanged = false;
                                    if ($amountChange !== null && $amountChange !== '' && is_numeric($amountChange)) {
                                        $isAmountChanged = abs((float) $amountChange - $originalAmount) > 0.01;
                                    }
                                    
                                    // Calculate the effective amount (changed amount if set, otherwise original)
                                    $effectiveAmount = $amountChange !== null && is_numeric($amountChange) ? (float) $amountChange : $remainingBalance;
                                    
                                    $isZiGItem = strtoupper($item['original_currency'] ?? '') === 'ZIG';
                                    $showApplyRateOption = $currency === 'ZiG' && $exchange_rate && $isZiGItem;
                                @endphp
                                <tr wire:key="selected-item-{{ $index }}">
                                    <td>{{ $item['source_type'] }}</td>
                                    <td>{{ $item['payee_regnumber'] ?? '-' }}</td>
                                    <td>{{ $item['payee_name'] ?? '-' }}</td>
                                    <td class="max-w-xs truncate" title="{{ $item['description'] }}">{{ $item['description'] }}</td>
                                    @if($currency === 'ZiG' && $exchange_rate)
                                        <td>
                                            @if($isZiGItem)
                                                <x-checkbox 
                                                    wire:model.live="selectedItems.{{ $index }}.apply_rate_to_zig" 
                                                    label="Apply Rate"
                                                    class="checkbox-xs"
                                                />
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>{{ $item['original_currency'] }}</td>
                                    <td>
                                        <div class="text-xs">
                                            <div>{{ number_format($item['original_amount'], 2) }}</div>
                                            @if(isset($item['total_paid']) && $item['total_paid'] > 0)
                                                <div class="text-gray-500">Paid: {{ number_format($item['total_paid'], 2) }}</div>
                                            @endif
                                            @if(isset($item['remaining_balance']))
                                                <div class="text-green-600 font-semibold">Remaining: {{ number_format($item['remaining_balance'], 2) }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <x-input 
                                            wire:model.live="selectedItems.{{ $index }}.amount_change" 
                                            type="number" 
                                            step="0.01" 
                                            min="0"
                                            class="input-xs w-24"
                                            placeholder="{{ number_format($originalAmount, 2) }}"
                                        />
                                        <div class="text-xs text-gray-500 mt-1">
                                            Optional: Change from {{ number_format($originalAmount, 2) }}
                                        </div>
                                    </td>
                                    
                                    <td>
                                        @if($isAmountChanged)
                                            <x-textarea 
                                                wire:model="selectedItems.{{ $index }}.amount_change_comment" 
                                                class="textarea-xs w-48"
                                                placeholder="Reason for amount change (required)..."
                                                rows="2"
                                                required
                                            />
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        <x-input 
                                            wire:model.live="selectedItems.{{ $index }}.partial_amount" 
                                            type="number" 
                                            step="0.01" 
                                            min="0"
                                            :max="$effectiveAmount"
                                            class="input-xs w-24"
                                            placeholder="{{ number_format($effectiveAmount, 2) }}"
                                        />
                                        <div class="text-xs text-gray-500 mt-1">
                                            Optional: Leave empty to pay {{ number_format($effectiveAmount, 2) }}
                                        </div>
                                    </td>
                                    <td>
                                        <x-input 
                                            wire:model="selectedItems.{{ $index }}.account_type" 
                                            type="text"
                                            class="input-xs w-32"
                                            placeholder="Account Type"
                                            required
                                        />
                                    </td>
                                    <td>
                                        <x-input 
                                            wire:model="selectedItems.{{ $index }}.gl_code" 
                                            type="text"
                                            class="input-xs w-32"
                                            placeholder="GL Code"
                                            required
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-4 p-4 bg-base-200 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="font-semibold">Total Amount:</span>
                    <span class="text-lg font-bold">{{ $currency ?? 'USD' }} {{ number_format($this->totalAmount, 2) }}</span>
                </div>
                @php
                    $hasDifferentCurrency = collect($selectedItems)->contains(function ($item) {
                        return $item['original_currency'] !== ($currency ?? 'USD');
                    });
                @endphp
                @if($currency === 'ZiG' && $exchange_rate && $hasDifferentCurrency)
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-sm text-gray-600">Exchange Rate:</span>
                        <span class="text-sm font-semibold">{{ number_format($exchange_rate, 4) }}</span>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.modal = false" />
                <x-button type="submit" label="{{ $id ? 'Update' : 'Create' }}" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>

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
</div>
