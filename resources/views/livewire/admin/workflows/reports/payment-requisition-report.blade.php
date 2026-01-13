<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
        link-item-class="text-base" />

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5">
        <x-card class="border-2 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Total Requisitions</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $summaryStats['total_requisitions'] }}</div>
                </div>
                <x-icon name="o-document-text" class="w-12 h-12 text-blue-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Total Amount</div>
                    <div class="text-2xl font-bold text-green-600">${{ number_format($summaryStats['total_amount'], 2) }}</div>
                </div>
                <x-icon name="o-currency-dollar" class="w-12 h-12 text-green-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Awaiting Voucher</div>
                    <div class="text-2xl font-bold text-purple-600">${{ number_format($summaryStats['amount_awaiting_voucher'], 2) }}</div>
                    <div class="text-xs text-gray-500">{{ $summaryStats['awaiting_voucher'] }} requisition(s)</div>
                </div>
                <x-icon name="o-check-badge" class="w-12 h-12 text-purple-400" />
            </div>
        </x-card>
    </div>

    <!-- Amount by Stage -->
    <x-card title="Amounts by Stage" separator class="mt-5 border-2 border-gray-200">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Draft -->
            <div class="p-4 rounded-lg bg-gray-50 border-2 border-gray-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-pencil-square" class="w-5 h-5 text-gray-500" />
                    <span class="text-sm font-semibold text-gray-600">Draft</span>
                </div>
                <div class="text-xl font-bold text-gray-700">${{ number_format($summaryStats['amount_draft'], 2) }}</div>
                <div class="text-xs text-gray-500">{{ $summaryStats['draft_requisitions'] }} requisition(s)</div>
            </div>

            <!-- Submitted -->
            <div class="p-4 rounded-lg bg-yellow-50 border-2 border-yellow-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-clock" class="w-5 h-5 text-yellow-600" />
                    <span class="text-sm font-semibold text-yellow-700">Submitted</span>
                </div>
                <div class="text-xl font-bold text-yellow-700">${{ number_format($summaryStats['amount_submitted'], 2) }}</div>
                <div class="text-xs text-yellow-600">{{ $summaryStats['submitted_requisitions'] }} requisition(s)</div>
            </div>

            <!-- Awaiting Voucher -->
            <div class="p-4 rounded-lg bg-blue-50 border-2 border-blue-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-check-circle" class="w-5 h-5 text-blue-600" />
                    <span class="text-sm font-semibold text-blue-700">Awaiting Voucher</span>
                </div>
                <div class="text-xl font-bold text-blue-700">${{ number_format($summaryStats['amount_awaiting_voucher'], 2) }}</div>
                <div class="text-xs text-blue-600">{{ $summaryStats['awaiting_voucher'] }} requisition(s)</div>
            </div>

            <!-- Rejected -->
            <div class="p-4 rounded-lg bg-red-50 border-2 border-red-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-x-circle" class="w-5 h-5 text-red-600" />
                    <span class="text-sm font-semibold text-red-700">Rejected</span>
                </div>
                <div class="text-xl font-bold text-red-700">${{ number_format($summaryStats['amount_rejected'], 2) }}</div>
                <div class="text-xs text-red-600">{{ $summaryStats['rejected_requisitions'] }} requisition(s)</div>
            </div>
        </div>
    </x-card>

    <!-- Payments by Currency Breakdown -->
    @if($paymentsByCurrency->count() > 0)
        <x-card title="Payments by Currency (Awaiting Voucher)" separator class="mt-5 border-2 border-indigo-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($paymentsByCurrency as $currencyData)
                    <div class="p-4 rounded-lg border-2 {{ $currencyData['currency_name'] === 'USD' ? 'bg-green-50 border-green-300' : 'bg-orange-50 border-orange-300' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-lg {{ $currencyData['currency_name'] === 'USD' ? 'text-green-700' : 'text-orange-700' }}">
                                {{ $currencyData['currency_name'] }}
                            </span>
                            <span class="text-xs bg-gray-200 px-2 py-1 rounded-full">
                                {{ $currencyData['count'] }} requisition(s)
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

        <!-- Second Row: Department and Search -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <x-select wire:model.live="department_filter" label="Department" :options="$departments" 
                option-value="id" option-label="name" placeholder="Select Department" />
            <x-input wire:model.live.debounce.300ms="search" label="Search"
                placeholder="Search by reference # or purpose" />
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
    <x-card title="Payment Requisitions" separator class="mt-5 border-2 border-gray-200">
        <x-table :headers="$headers" :rows="$requisitions" class="table-zebra table-xs">
            @scope('cell_reference_number', $requisition)
                <div class="font-semibold">{{ $requisition->reference_number }}</div>
            @endscope

            @scope('cell_purpose', $requisition)
                <div>{{ \Illuminate\Support\Str::limit($requisition->purpose, 50) }}</div>
            @endscope

            @scope('cell_department.name', $requisition)
                <div>{{ $requisition->department?->name ?? 'N/A' }}</div>
            @endscope

            @scope('cell_total_amount', $requisition)
                <div class="font-semibold">${{ number_format($requisition->total_amount, 2) }}</div>
                <div class="text-xs text-gray-500">{{ $requisition->currency?->name ?? 'N/A' }}</div>
            @endscope

            @scope('cell_status', $requisition)
                @php
                    $statusColor = match ($requisition->status) {
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
                <x-badge :value="$requisition->status" class="{{ $statusColor }}" />
            @endscope

            @scope('cell_created_at', $requisition)
                <div>{{ $requisition->created_at->format('d M Y') }}</div>
            @endscope

            @scope('cell_action', $requisition)
                <x-button icon="o-eye" class="btn-xs btn-success btn-outline"
                    link="{{ route('admin.paymentrequisition', $requisition->uuid) }}" />
            @endscope

            <x-slot:empty>
                <x-alert class="alert-info" title="No payment requisitions found for the selected filters." />
            </x-slot:empty>
        </x-table>

        <div class="mt-4">
            {{ $requisitions->links() }}
        </div>
    </x-card>
</div>

