<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
        link-item-class="text-base" />

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5">
        <x-card class="border-2 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Total Applications</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $summaryStats['total_applications'] }}</div>
                </div>
                <x-icon name="o-document-text" class="w-12 h-12 text-blue-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Total Paid (Processed)</div>
                    <div class="text-2xl font-bold text-green-600">${{ number_format($summaryStats['amount_processed'], 2) }}</div>
                    <div class="text-xs text-gray-500">{{ $summaryStats['count_processed'] }} payment(s)</div>
                </div>
                <x-icon name="o-currency-dollar" class="w-12 h-12 text-green-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Awaiting Payment</div>
                    <div class="text-2xl font-bold text-purple-600">${{ number_format($summaryStats['amount_approved'], 2) }}</div>
                    <div class="text-xs text-gray-500">{{ $summaryStats['count_approved'] }} approved</div>
                </div>
                <x-icon name="o-check-badge" class="w-12 h-12 text-purple-400" />
            </div>
        </x-card>
    </div>

    <!-- Amount by Stage -->
    <x-card title="Amounts by Stage" separator class="mt-5 border-2 border-gray-200">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <!-- Draft -->
            <div class="p-4 rounded-lg bg-gray-50 border-2 border-gray-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-pencil-square" class="w-5 h-5 text-gray-500" />
                    <span class="text-sm font-semibold text-gray-600">Draft</span>
                </div>
                <div class="text-xl font-bold text-gray-700">${{ number_format($summaryStats['amount_draft'], 2) }}</div>
                <div class="text-xs text-gray-500">{{ $summaryStats['count_draft'] }} application(s)</div>
            </div>

            <!-- Pending Approval -->
            <div class="p-4 rounded-lg bg-yellow-50 border-2 border-yellow-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-clock" class="w-5 h-5 text-yellow-600" />
                    <span class="text-sm font-semibold text-yellow-700">In Approval</span>
                </div>
                <div class="text-xl font-bold text-yellow-700">${{ number_format($summaryStats['amount_pending_approval'], 2) }}</div>
                <div class="text-xs text-yellow-600">{{ $summaryStats['count_pending_approval'] }} application(s)</div>
            </div>

            <!-- Approved (Awaiting Payment) -->
            <div class="p-4 rounded-lg bg-blue-50 border-2 border-blue-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-check-circle" class="w-5 h-5 text-blue-600" />
                    <span class="text-sm font-semibold text-blue-700">Approved</span>
                </div>
                <div class="text-xl font-bold text-blue-700">${{ number_format($summaryStats['amount_approved'], 2) }}</div>
                <div class="text-xs text-blue-600">{{ $summaryStats['count_approved'] }} awaiting payment</div>
            </div>

            <!-- Processed/Paid -->
            <div class="p-4 rounded-lg bg-green-50 border-2 border-green-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-banknotes" class="w-5 h-5 text-green-600" />
                    <span class="text-sm font-semibold text-green-700">Paid</span>
                </div>
                <div class="text-xl font-bold text-green-700">${{ number_format($summaryStats['amount_processed'], 2) }}</div>
                <div class="text-xs text-green-600">{{ $summaryStats['count_processed'] }} payment(s)</div>
            </div>

            <!-- Rejected -->
            <div class="p-4 rounded-lg bg-red-50 border-2 border-red-300">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="o-x-circle" class="w-5 h-5 text-red-600" />
                    <span class="text-sm font-semibold text-red-700">Rejected</span>
                </div>
                <div class="text-xl font-bold text-red-700">${{ number_format($summaryStats['amount_rejected'], 2) }}</div>
                <div class="text-xs text-red-600">{{ $summaryStats['count_rejected'] }} application(s)</div>
            </div>
        </div>
    </x-card>

    <!-- Payments by Currency Breakdown -->
    @if($paymentsByCurrency->count() > 0)
        <x-card title="Payments by Currency (Processed)" separator class="mt-5 border-2 border-indigo-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($paymentsByCurrency as $currencyData)
                    <div class="p-4 rounded-lg border-2 {{ $currencyData['currency_name'] === 'USD' ? 'bg-green-50 border-green-300' : 'bg-orange-50 border-orange-300' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-lg {{ $currencyData['currency_name'] === 'USD' ? 'text-green-700' : 'text-orange-700' }}">
                                {{ $currencyData['currency_name'] }}
                            </span>
                            <span class="text-xs bg-gray-200 px-2 py-1 rounded-full">
                                {{ $currencyData['count'] }} payment(s)
                            </span>
                        </div>
                        <div class="text-2xl font-bold {{ $currencyData['currency_name'] === 'USD' ? 'text-green-800' : 'text-orange-800' }}">
                            @if($currencyData['currency_name'] === 'USD')
                                ${{ number_format($currencyData['total_original'], 2) }}
                            @else
                                {{ number_format($currencyData['total_original'], 2) }}
                            @endif
                        </div>
                        @if($currencyData['currency_name'] !== 'USD')
                            <div class="text-xs text-gray-500 mt-1">
                                USD Equivalent: ${{ number_format($currencyData['total_usd'], 2) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            
            <!-- Total Summary -->
            <div class="mt-4 p-4 bg-indigo-100 rounded-lg border-2 border-indigo-300">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-semibold text-indigo-800">Total Paid (All Currencies in USD)</span>
                        <p class="text-xs text-indigo-600">Combined value of all processed payments in selected period</p>
                    </div>
                    <span class="text-3xl font-bold text-indigo-800">${{ number_format($summaryStats['total_paid_usd'] ?? 0, 2) }}</span>
                </div>
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
            <x-select wire:model.live="department_filter" label="Department" :options="$departmentOptions" 
                placeholder="Select Department" />
            <x-input wire:model.live.debounce.300ms="search" label="Search"
                placeholder="Search by application #, name, or employee #" />
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2 mt-4">
            <x-button label="Apply Filters" wire:click="applyFilters" class="btn-primary" icon="o-funnel" spinner />
            <x-button label="Reset" wire:click="resetFilters" class="btn-outline" icon="o-arrow-path" spinner />
            <div class="flex-1"></div>
            <x-button label="Export Excel" wire:click="exportExcel" class="btn-success" icon="o-document-arrow-down" spinner />
            <x-button label="Export PDF" wire:click="exportPdf" class="btn-error" icon="o-document-text" spinner />
        </div>
    </x-card>

    <!-- Report Table -->
    <x-card title="T&S Allowance Applications" separator class="mt-5 border-2 border-gray-200">
        <x-table :headers="$headers" :rows="$allowances" class="table-zebra table-xs">
            @scope('cell_application_number', $allowance)
                <div class="font-semibold">{{ $allowance->application_number }}</div>
            @endscope

            @scope('cell_full_name', $allowance)
                <div>{{ $allowance->full_name }}</div>
                <div class="text-xs text-gray-500">{{ $allowance->employee_number }}</div>
            @endscope

            @scope('cell_department.name', $allowance)
                <div>{{ $allowance->department?->name ?? 'N/A' }}</div>
            @endscope

            @scope('cell_balance_due', $allowance)
                <div class="font-semibold">${{ number_format($allowance->balance_due, 2) }}</div>
            @endscope

            @scope('cell_status', $allowance)
                @php
                    $statusColor = match ($allowance->status) {
                        'DRAFT' => 'badge-warning',
                        'SUBMITTED' => 'badge-info',
                        'UNDER_REVIEW' => 'badge-info',
                        'RECOMMENDED' => 'badge-info',
                        'APPROVED' => 'badge-success',
                        'FINANCE_VERIFIED' => 'badge-success',
                        'PAYMENT_PROCESSED' => 'badge-success',
                        'REJECTED' => 'badge-error',
                        'ARCHIVED' => 'badge-ghost',
                        default => 'badge-ghost',
                    };
                @endphp
                <x-badge :value="$allowance->status" class="{{ $statusColor }}" />
            @endscope

            @scope('cell_submission_date', $allowance)
                <div>{{ $allowance->submission_date?->format('d M Y') ?? 'N/A' }}</div>
            @endscope

            @scope('cell_action', $allowance)
                <x-button icon="o-eye" class="btn-xs btn-success btn-outline"
                    link="{{ route('admin.workflows.ts-allowance', $allowance->uuid) }}" />
            @endscope

            <x-slot:empty>
                <x-alert class="alert-info" title="No T&S Allowance applications found for the selected filters." />
            </x-slot:empty>
        </x-table>

        <div class="mt-4">
            {{ $allowances->links() }}
        </div>
    </x-card>
</div>

