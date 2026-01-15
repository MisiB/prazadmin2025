<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
        link-item-class="text-base" />

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5">
        <x-card class="border-2 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Total Vouchers</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $summaryStats['total_vouchers'] }}</div>
                </div>
                <x-icon name="o-document-text" class="w-12 h-12 text-blue-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Total Amount</div>
                    @if(isset($summaryStats['total_amounts_by_currency']) && count($summaryStats['total_amounts_by_currency']) > 0)
                        <div class="space-y-1">
                            @foreach($summaryStats['total_amounts_by_currency'] as $currency => $amount)
                                <div class="text-lg font-bold {{ $currency === 'USD' ? 'text-green-600' : ($currency === 'ZiG' ? 'text-orange-600' : 'text-gray-600') }}">
                                    {{ $currency }} {{ number_format($amount, 2) }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-2xl font-bold text-green-600">${{ number_format($summaryStats['total_amount'], 2) }}</div>
                    @endif
                </div>
                <x-icon name="o-currency-dollar" class="w-12 h-12 text-green-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Approved - Payment Processed</div>
                    @if(isset($summaryStats['approved_amounts_by_currency']) && count($summaryStats['approved_amounts_by_currency']) > 0)
                        <div class="space-y-1">
                            @foreach($summaryStats['approved_amounts_by_currency'] as $currency => $amount)
                                <div class="text-lg font-bold {{ $currency === 'USD' ? 'text-purple-600' : ($currency === 'ZiG' ? 'text-orange-600' : 'text-gray-600') }}">
                                    {{ $currency }} {{ number_format($amount, 2) }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-2xl font-bold text-purple-600">${{ number_format($summaryStats['amount_approved_payment_processed'], 2) }}</div>
                    @endif
                    <div class="text-xs text-gray-500 mt-1">{{ $summaryStats['approved_payment_processed'] }} voucher(s)</div>
                </div>
                <x-icon name="o-banknotes" class="w-12 h-12 text-purple-400" />
            </div>
        </x-card>
    </div>

    <!-- Amount by Stage -->
    <x-card title="Amounts by Stage" separator class="mt-5 border-2 border-gray-200">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <!-- Draft -->
            <div class="p-4 rounded-lg bg-gray-50 border-2 border-gray-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-pencil-square" class="w-5 h-5 text-gray-500" />
                    <span class="text-sm font-semibold text-gray-600">Draft</span>
                </div>
                @if(isset($summaryStats['amounts_by_stage_and_currency']['DRAFT']) && count($summaryStats['amounts_by_stage_and_currency']['DRAFT']) > 0)
                    <div class="space-y-1">
                        @foreach($summaryStats['amounts_by_stage_and_currency']['DRAFT'] as $currency => $amount)
                            <div class="text-sm font-bold text-gray-700">{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="text-xl font-bold text-gray-700">${{ number_format($summaryStats['amount_draft'], 2) }}</div>
                @endif
                <div class="text-xs text-gray-500 mt-1">{{ $summaryStats['draft_vouchers'] }} voucher(s)</div>
            </div>

            <!-- Prepared -->
            <div class="p-4 rounded-lg bg-yellow-50 border-2 border-yellow-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-clock" class="w-5 h-5 text-yellow-600" />
                    <span class="text-sm font-semibold text-yellow-700">Prepared</span>
                </div>
                @if(isset($summaryStats['amounts_by_stage_and_currency']['PREPARED']) && count($summaryStats['amounts_by_stage_and_currency']['PREPARED']) > 0)
                    <div class="space-y-1">
                        @foreach($summaryStats['amounts_by_stage_and_currency']['PREPARED'] as $currency => $amount)
                            <div class="text-sm font-bold text-yellow-700">{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="text-xl font-bold text-yellow-700">${{ number_format($summaryStats['amount_prepared'], 2) }}</div>
                @endif
                <div class="text-xs text-yellow-600 mt-1">{{ $summaryStats['prepared_vouchers'] }} voucher(s)</div>
            </div>

            <!-- Verified -->
            <div class="p-4 rounded-lg bg-blue-50 border-2 border-blue-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-check-circle" class="w-5 h-5 text-blue-600" />
                    <span class="text-sm font-semibold text-blue-700">Verified</span>
                </div>
                @if(isset($summaryStats['amounts_by_stage_and_currency']['VERIFIED']) && count($summaryStats['amounts_by_stage_and_currency']['VERIFIED']) > 0)
                    <div class="space-y-1">
                        @foreach($summaryStats['amounts_by_stage_and_currency']['VERIFIED'] as $currency => $amount)
                            <div class="text-sm font-bold text-blue-700">{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="text-xl font-bold text-blue-700">${{ number_format($summaryStats['amount_verified'], 2) }}</div>
                @endif
                <div class="text-xs text-blue-600 mt-1">{{ $summaryStats['verified_vouchers'] }} voucher(s)</div>
            </div>

            <!-- Checked -->
            <div class="p-4 rounded-lg bg-indigo-50 border-2 border-indigo-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-check-circle" class="w-5 h-5 text-indigo-600" />
                    <span class="text-sm font-semibold text-indigo-700">Checked</span>
                </div>
                @if(isset($summaryStats['amounts_by_stage_and_currency']['CHECKED']) && count($summaryStats['amounts_by_stage_and_currency']['CHECKED']) > 0)
                    <div class="space-y-1">
                        @foreach($summaryStats['amounts_by_stage_and_currency']['CHECKED'] as $currency => $amount)
                            <div class="text-sm font-bold text-indigo-700">{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="text-xl font-bold text-indigo-700">${{ number_format($summaryStats['amount_checked'], 2) }}</div>
                @endif
                <div class="text-xs text-indigo-600 mt-1">{{ $summaryStats['checked_vouchers'] }} voucher(s)</div>
            </div>

            <!-- Finance Approved -->
            <div class="p-4 rounded-lg bg-teal-50 border-2 border-teal-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-check-badge" class="w-5 h-5 text-teal-600" />
                    <span class="text-sm font-semibold text-teal-700">Finance Approved</span>
                </div>
                @if(isset($summaryStats['amounts_by_stage_and_currency']['FINANCE_APPROVED']) && count($summaryStats['amounts_by_stage_and_currency']['FINANCE_APPROVED']) > 0)
                    <div class="space-y-1">
                        @foreach($summaryStats['amounts_by_stage_and_currency']['FINANCE_APPROVED'] as $currency => $amount)
                            <div class="text-sm font-bold text-teal-700">{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="text-xl font-bold text-teal-700">${{ number_format($summaryStats['amount_finance_approved'], 2) }}</div>
                @endif
                <div class="text-xs text-teal-600 mt-1">{{ $summaryStats['finance_approved'] }} voucher(s)</div>
            </div>

            <!-- Approved - Payment Processed -->
            <div class="p-4 rounded-lg bg-green-50 border-2 border-green-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-check-badge" class="w-5 h-5 text-green-600" />
                    <span class="text-sm font-semibold text-green-700">Approved - Payment Processed</span>
                </div>
                @if(isset($summaryStats['amounts_by_stage_and_currency']['APPROVED_PAYMENT_PROCESSED']) && count($summaryStats['amounts_by_stage_and_currency']['APPROVED_PAYMENT_PROCESSED']) > 0)
                    <div class="space-y-1">
                        @foreach($summaryStats['amounts_by_stage_and_currency']['APPROVED_PAYMENT_PROCESSED'] as $currency => $amount)
                            <div class="text-sm font-bold text-green-700">{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="text-xl font-bold text-green-700">${{ number_format($summaryStats['amount_approved_payment_processed'], 2) }}</div>
                @endif
                <div class="text-xs text-green-600 mt-1">{{ $summaryStats['approved_payment_processed'] }} voucher(s)</div>
            </div>

            <!-- Rejected -->
            <div class="p-4 rounded-lg bg-red-50 border-2 border-red-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-x-circle" class="w-5 h-5 text-red-600" />
                    <span class="text-sm font-semibold text-red-700">Rejected</span>
                </div>
                @if(isset($summaryStats['amounts_by_stage_and_currency']['REJECTED']) && count($summaryStats['amounts_by_stage_and_currency']['REJECTED']) > 0)
                    <div class="space-y-1">
                        @foreach($summaryStats['amounts_by_stage_and_currency']['REJECTED'] as $currency => $amount)
                            <div class="text-sm font-bold text-red-700">{{ $currency }} {{ number_format($amount, 2) }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="text-xl font-bold text-red-700">${{ number_format($summaryStats['amount_rejected'], 2) }}</div>
                @endif
                <div class="text-xs text-red-600 mt-1">{{ $summaryStats['rejected_vouchers'] }} voucher(s)</div>
            </div>
        </div>
    </x-card>

    <!-- Vouchers by Currency Breakdown -->
    @if($vouchersByCurrency->count() > 0)
        <x-card title="Vouchers by Currency (Payment Processed)" separator class="mt-5 border-2 border-indigo-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($vouchersByCurrency as $currencyData)
                    <div class="p-4 rounded-lg border-2 {{ $currencyData['currency_name'] === 'USD' ? 'bg-green-50 border-green-300' : 'bg-orange-50 border-orange-300' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-lg {{ $currencyData['currency_name'] === 'USD' ? 'text-green-700' : 'text-orange-700' }}">
                                {{ $currencyData['currency_name'] }}
                            </span>
                            <span class="text-xs bg-gray-200 px-2 py-1 rounded-full">
                                {{ $currencyData['count'] }} voucher(s)
                            </span>
                        </div>
                        <div class="text-2xl font-bold {{ $currencyData['currency_name'] === 'USD' ? 'text-green-800' : 'text-orange-800' }}">
                            {{ number_format($currencyData['total_amount'], 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif

    <!-- Filters -->
    <x-card title="Filters & Search" separator class="mt-5 border-2 border-gray-200">
        <!-- First Row: Date Range and Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-input wire:model.live="start_date" type="date" label="Start Date" />
            <x-input wire:model.live="end_date" type="date" label="End Date" />
            <x-select wire:model.live="status_filter" label="Status" :options="$statusOptions" />
        </div>

        <!-- Second Row: Search -->
        <div class="grid grid-cols-1 gap-4 mt-4">
            <x-input wire:model.live.debounce.300ms="search" label="Search"
                placeholder="Search by voucher number" />
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2 mt-4">
            <x-button label="Apply Filters" wire:click="applyFilters" class="btn-primary" icon="o-funnel" spinner />
            <x-button label="Reset" wire:click="resetFilters" class="btn-outline" icon="o-arrow-path" spinner />
            <div class="flex-1"></div>
            <x-button label="Export Excel" wire:click="exportToExcel" class="btn-success" icon="o-document-arrow-down" spinner />
            <x-button label="Export PDF" wire:click="exportToPdf" class="btn-error" icon="o-document-text" spinner />
        </div>
    </x-card>

    <!-- Report Table -->
    <x-card title="Payment Vouchers" separator class="mt-5 border-2 border-gray-200">
        @if ($vouchers->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th class="w-8"></th>
                            <th class="w-32">Voucher #</th>
                            <th class="w-32">Date</th>
                            <th class="w-32">Amount</th>
                            <th class="w-24">Currency</th>
                            <th class="w-32">Status</th>
                            <th class="w-40">Prepared By</th>
                            <th class="w-32">Created</th>
                            <th class="w-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vouchers as $voucher)
                            <tr class="hover:bg-base-200">
                                <td>
                                    @if ($voucher->items->count() > 0)
                                        <button 
                                            wire:click="$toggle('expandedVouchers.{{ $voucher->id }}')"
                                            class="btn btn-ghost btn-xs">
                                            <x-icon name="o-chevron-{{ isset($expandedVouchers[$voucher->id]) ? 'down' : 'right' }}" class="w-4 h-4" />
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-semibold">{{ $voucher->voucher_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $voucher->items->count() }} item(s)</div>
                                </td>
                                <td>
                                    <div>{{ $voucher->voucher_date?->format('d M Y') ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="font-semibold">${{ number_format($voucher->total_amount, 2) }}</div>
                                </td>
                                <td>
                                    <div>{{ $voucher->currency ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    @php
                                        $statusColor = match ($voucher->status) {
                                            'DRAFT' => 'badge-warning',
                                            'PREPARED' => 'badge-info',
                                            'VERIFIED' => 'badge-info',
                                            'CHECKED' => 'badge-info',
                                            'FINANCE_APPROVED' => 'badge-success',
                                            'APPROVED_PAYMENT_PROCESSED' => 'badge-success',
                                            'REJECTED' => 'badge-error',
                                            default => 'badge-ghost',
                                        };
                                    @endphp
                                    <x-badge :value="$voucher->status" class="{{ $statusColor }} badge-sm" />
                                </td>
                                <td>
                                    <div>{{ $voucher->preparedBy?->name ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div>{{ $voucher->created_at->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <x-button icon="o-eye" class="btn-ghost btn-xs"
                                        link="{{ route('admin.paymentvoucher.show', $voucher->uuid) }}"
                                        tooltip="View Details" />
                                </td>
                            </tr>
                            @if (isset($expandedVouchers[$voucher->id]) && $voucher->items->count() > 0)
                                <tr>
                                    <td colspan="9" class="bg-base-100 p-0">
                                        <div class="p-4 bg-gray-50 border-t border-gray-200">
                                            <div class="font-semibold mb-3 text-sm">Voucher Items ({{ $voucher->items->count() }})</div>
                                            <div class="overflow-x-auto">
                                                <table class="table table-compact table-xs">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Source Type</th>
                                                            <th>Payee Reg Number</th>
                                                            <th>Payee Name</th>
                                                            <th>Description</th>
                                                            <th>Original Currency</th>
                                                            <th>Original Amount</th>
                                                            <th>Edited Amount</th>
                                                            <th>Partial Payment</th>
                                                            <th>Partial Amount</th>
                                                            <th>Exchange Rate</th>
                                                            <th>Payable Amount</th>
                                                            <th>Account Type</th>
                                                            <th>GL Code</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($voucher->items as $index => $item)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>
                                                                    <x-badge :value="$item->source_type" class="badge-info badge-sm" />
                                                                </td>
                                                                <td>{{ $item->payee_regnumber ?? 'N/A' }}</td>
                                                                <td>{{ $item->payee_name ?? 'N/A' }}</td>
                                                                <td>
                                                                    <div class="max-w-xs">{{ \Illuminate\Support\Str::limit($item->description, 50) }}</div>
                                                                    @if ($item->amount_change_comment)
                                                                        <div class="text-xs text-gray-500 mt-1">
                                                                            <strong>Note:</strong> {{ \Illuminate\Support\Str::limit($item->amount_change_comment, 40) }}
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $item->original_currency ?? 'N/A' }}</td>
                                                                <td class="text-right">
                                                                    @if ($item->original_amount)
                                                                        {{ number_format($item->original_amount, 2) }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                                <td class="text-right">
                                                                    @if ($item->edited_amount)
                                                                        {{ number_format($item->edited_amount, 2) }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @php
                                                                        // Determine if this is a partial payment
                                                                        $baseAmount = $item->edited_amount ?? $item->original_amount ?? 0;
                                                                        $isPartial = false;
                                                                        
                                                                        if ($baseAmount > 0 && $item->payable_amount > 0) {
                                                                            if ($item->exchange_rate && $item->exchange_rate > 0) {
                                                                                // Convert payable_amount back to original currency for comparison
                                                                                $convertedPayable = $item->payable_amount / $item->exchange_rate;
                                                                                // Allow small floating point differences (0.01)
                                                                                $isPartial = $convertedPayable < ($baseAmount - 0.01);
                                                                            } else {
                                                                                // Same currency, direct comparison
                                                                                $isPartial = $item->payable_amount < ($baseAmount - 0.01);
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    @if($isPartial)
                                                                        <x-badge value="Yes" class="badge-warning badge-xs" />
                                                                    @else
                                                                        <x-badge value="No" class="badge-ghost badge-xs" />
                                                                    @endif
                                                                </td>
                                                                <td class="text-right">
                                                                    @php
                                                                        // Calculate partial amount in original currency
                                                                        if ($isPartial && $item->payable_amount > 0) {
                                                                            if ($item->exchange_rate && $item->exchange_rate > 0) {
                                                                                $partialAmount = $item->payable_amount / $item->exchange_rate;
                                                                            } else {
                                                                                $partialAmount = $item->payable_amount;
                                                                            }
                                                                        } else {
                                                                            $partialAmount = null;
                                                                        }
                                                                    @endphp
                                                                    @if($partialAmount !== null)
                                                                        {{ $item->original_currency ?? 'N/A' }} {{ number_format($partialAmount, 2) }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                                <td class="text-right">
                                                                    @if ($item->exchange_rate)
                                                                        {{ number_format($item->exchange_rate, 4) }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                                <td class="text-right font-semibold">
                                                                    {{ $voucher->currency ?? 'USD' }} {{ number_format($item->payable_amount, 2) }}
                                                                </td>
                                                                <td>{{ $item->account_type ?? 'N/A' }}</td>
                                                                <td>{{ $item->gl_code ?? 'N/A' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="font-bold bg-gray-100">
                                                            <td colspan="12" class="text-right">Total:</td>
                                                            <td class="text-right">{{ $voucher->currency ?? 'USD' }} {{ number_format($voucher->items->sum('payable_amount'), 2) }}</td>
                                                            <td colspan="1"></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $vouchers->links() }}
            </div>
        @else
            <x-alert class="alert-info" title="No payment vouchers found for the selected filters." />
        @endif
    </x-card>
</div>

