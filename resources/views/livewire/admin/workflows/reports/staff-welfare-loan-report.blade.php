<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
        link-item-class="text-base" />

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
        <x-card class="border-2 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Total Loans</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $summaryStats['total_loans'] }}</div>
                </div>
                <x-icon name="o-document-text" class="w-12 h-12 text-blue-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Total Requested</div>
                    <div class="text-2xl font-bold text-green-600">${{ number_format($summaryStats['total_amount_requested'], 2) }}</div>
                </div>
                <x-icon name="o-currency-dollar" class="w-12 h-12 text-green-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Total Paid (ZIG)</div>
                    <div class="text-xl font-bold text-purple-600">{{ number_format($summaryStats['total_paid_zig'], 2) }}</div>
                    <div class="text-xs text-gray-500 mt-1">ZIG Currency</div>
                </div>
                <x-icon name="o-banknotes" class="w-12 h-12 text-purple-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-indigo-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Total Paid (USD)</div>
                    <div class="text-xl font-bold text-indigo-600">${{ number_format($summaryStats['total_paid_usd'], 2) }}</div>
                    <div class="text-xs text-gray-500 mt-1">US Dollar</div>
                </div>
                <x-icon name="o-currency-dollar" class="w-12 h-12 text-indigo-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-orange-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Completed</div>
                    <div class="text-3xl font-bold text-orange-600">{{ $summaryStats['completed_loans'] }}</div>
                </div>
                <x-icon name="o-check-badge" class="w-12 h-12 text-orange-400" />
            </div>
        </x-card>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <x-card class="border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Approved</div>
                    <div class="text-2xl font-bold text-green-600">{{ $summaryStats['approved_loans'] }}</div>
                </div>
                <x-icon name="o-hand-thumb-up" class="w-10 h-10 text-green-400" />
            </div>
        </x-card>

        <x-card class="border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Rejected</div>
                    <div class="text-2xl font-bold text-red-600">{{ $summaryStats['rejected_loans'] }}</div>
                </div>
                <x-icon name="o-hand-thumb-down" class="w-10 h-10 text-red-400" />
            </div>
        </x-card>

        <x-card class="border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Pending</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $summaryStats['pending_loans'] }}</div>
                </div>
                <x-icon name="o-clock" class="w-10 h-10 text-yellow-400" />
            </div>
        </x-card>
    </div>

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
                option-value="id" option-label="name" placeholder="Search by department">
            </x-select>
            <x-input wire:model.live.debounce.300ms="search" label="Search" 
                placeholder="Loan #, Name, Employee #" icon="o-magnifying-glass" />
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2 mt-4">
            <x-button icon="o-funnel" class="btn-primary btn-sm" label="Apply Filters" wire:click="applyFilters" />
            <x-button icon="o-arrow-path" class="btn-outline btn-sm" label="Reset" wire:click="resetFilters" />
            <x-button icon="o-document-arrow-down" class="btn-success btn-sm" label="Export Excel" wire:click="exportToExcel" />
            <x-button icon="o-document-text" class="btn-error btn-sm" label="Export PDF" wire:click="exportToPdf" />
        </div>
    </x-card>

    <!-- Loans Report Table -->
    <x-card title="Staff Welfare Loans Report" separator class="mt-5 border-2 border-gray-200">
        @if ($loans->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th class="w-32">Loan Number</th>
                            <th class="w-48">Applicant Details</th>
                            <th class="w-40">Department</th>
                            <th class="w-32">Loan Details</th>
                            <th class="w-32">Status</th>
                            <th class="w-40">Timeline</th>
                            <th class="w-32">Payment Info</th>
                            <th class="w-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loans as $loan)
                            <tr>
                                <!-- Loan Number -->
                                <td>
                                    <div class="font-semibold">{{ $loan->loan_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $loan->employee_number }}</div>
                                </td>

                                <!-- Applicant Details -->
                                <td>
                                    <div class="font-semibold">{{ $loan->full_name }}</div>
                                    <div class="text-xs text-gray-600">{{ $loan->job_title }}</div>
                                    <div class="text-xs text-gray-500">Joined: {{ $loan->date_joined?->format('Y-m-d') ?? 'N/A' }}</div>
                                </td>

                                <!-- Department -->
                                <td>
                                    <div class="text-sm">{{ $loan->department->name ?? 'N/A' }}</div>
                                </td>

                                <!-- Loan Details -->
                                <td>
                                    <div class="font-semibold text-green-600">${{ number_format($loan->loan_amount_requested, 2) }}</div>
                                    <div class="text-xs text-gray-600">{{ $loan->repayment_period_months }} months</div>
                                    <div class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($loan->loan_purpose, 30) }}</div>
                                </td>

                                <!-- Status -->
                                <td>
                                    @php
                                        $statusColor = match ($loan->status) {
                                            'DRAFT' => 'badge-ghost',
                                            'SUBMITTED' => 'badge-info',
                                            'HR_REVIEW' => 'badge-info',
                                            'FINANCE_REVIEW' => 'badge-info',
                                            'CEO_APPROVAL' => 'badge-info',
                                            'APPROVED' => 'badge-success',
                                            'REJECTED' => 'badge-error',
                                            'PAYMENT_PROCESSED' => 'badge-success',
                                            'AWAITING_ACKNOWLEDGEMENT' => 'badge-warning',
                                            'COMPLETED' => 'badge-success',
                                            default => 'badge-ghost',
                                        };
                                    @endphp
                                    <x-badge :value="$loan->status" class="{{ $statusColor }} badge-sm" />
                                </td>

                                <!-- Timeline -->
                                <td>
                                    <div class="text-xs space-y-1">
                                        <div><span class="font-semibold">Created:</span> {{ $loan->created_at->format('Y-m-d') }}</div>
                                        @if ($loan->submission_date)
                                            <div><span class="font-semibold">Submitted:</span> {{ $loan->submission_date->format('Y-m-d') }}</div>
                                        @endif
                                        @if ($loan->hr_review_date)
                                            <div><span class="font-semibold">HR Review:</span> {{ $loan->hr_review_date->format('Y-m-d') }}</div>
                                        @endif
                                        @if ($loan->payment_capture_date)
                                            <div><span class="font-semibold">Payment:</span> {{ $loan->payment_capture_date->format('Y-m-d') }}</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Payment Info -->
                                <td>
                                    @if ($loan->amount_paid)
                                        @php
                                            $payment = $loan->payments->first();
                                        @endphp
                                        <div class="text-xs space-y-1">
                                            @if($payment && $payment->currency_id)
                                                <div class="font-semibold text-blue-600">{{ $payment->currency->name ?? 'N/A' }}</div>
                                                <div class="text-gray-700">{{ number_format($payment->amount_paid_original, 2) }} {{ $payment->currency->name }}</div>
                                                @if($payment->exchangerate_id)
                                                    <div class="text-xs text-gray-500">Rate: {{ number_format($payment->exchange_rate_used, 4) }}</div>
                                                @endif
                                                <div class="font-semibold text-green-600">${{ number_format($payment->amount_paid_usd, 2) }} USD</div>
                                            @else
                                                <div class="font-semibold text-green-600">${{ number_format($loan->amount_paid, 2) }}</div>
                                            @endif
                                            <div>{{ $loan->payment_method ?? 'N/A' }}</div>
                                            <div class="text-gray-500">{{ $loan->payment_reference ?? 'N/A' }}</div>
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-400">Not paid</div>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div class="flex gap-1">
                                        <x-button icon="o-eye" class="btn-ghost btn-xs" 
                                            link="{{ route('admin.workflows.staff-welfare-loan', $loan->uuid) }}" 
                                            tooltip="View Details" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $loans->links() }}
            </div>
        @else
            <div class="flex items-center justify-center h-64">
                <div class="text-center">
                    <x-icon name="o-document-text" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <div class="text-lg text-gray-500">No loans found</div>
                    <div class="text-sm text-gray-400 mt-2">Try adjusting your filters</div>
                </div>
            </div>
        @endif
    </x-card>

    <!-- Detailed Breakdown by Status -->
    <x-card title="Status Breakdown" separator class="mt-5 border-2 border-gray-200">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach ($statusOptions as $statusOption)
                @if ($statusOption['id'] !== 'ALL')
                    @php
                        $count = $loans->where('status', $statusOption['id'])->count();
                    @endphp
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-700">{{ $count }}</div>
                        <div class="text-xs text-gray-600 mt-1">{{ $statusOption['name'] }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </x-card>
</div>
