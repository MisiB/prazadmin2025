<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
        link-item-class="text-base" />

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5">
        <x-card class="border-2 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Awaiting Payment</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $approvedLoans->count() }}</div>
                </div>
                <x-icon name="o-clock" class="w-12 h-12 text-blue-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Paid Today</div>
                    <div class="text-3xl font-bold text-green-600">{{ $paymentsToday }}</div>
                </div>
                <x-icon name="o-check-circle" class="w-12 h-12 text-green-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Paid This Month</div>
                    <div class="text-3xl font-bold text-purple-600">{{ $paymentsThisMonth }}</div>
                </div>
                <x-icon name="o-currency-dollar" class="w-12 h-12 text-purple-400" />
            </div>
        </x-card>
    </div>

    <!-- Loans Awaiting Payment -->
    <x-card title="Loans Awaiting Payment" separator class="mt-5 border-2 border-gray-200">
        @if ($approvedLoans->count() > 0)
            <div class="space-y-3">
                @foreach ($approvedLoans as $loan)
                    @php
                        $isExpanded = $this->isLoanExpanded($loan->uuid);
                        $daysWaiting = $loan->updated_at->diffInDays(now());
                    @endphp
                    <div class="border border-gray-200 rounded-lg shadow-sm">
                        <!-- Loan Header -->
                        <div class="p-3 bg-white rounded-t-lg cursor-pointer hover:bg-gray-50 transition-colors"
                            wire:click="toggleLoan('{{ $loan->uuid }}')">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <x-icon name="o-chevron-{{ $isExpanded ? 'down' : 'right' }}" class="w-4 h-4" />
                                    <div>
                                        <div class="font-semibold">{{ $loan->loan_number }}</div>
                                        <div class="text-sm text-gray-600">{{ $loan->full_name }} - {{ $loan->department->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <div class="font-semibold text-lg">${{ number_format($loan->loan_amount_requested, 2) }}</div>
                                        <div class="text-xs text-gray-500">{{ $daysWaiting }} days waiting</div>
                                    </div>
                                    @can('swl.payment.execute')
                                        <x-button icon="o-currency-dollar" class="btn-success btn-sm" label="Execute Payment"
                                            @click.stop="$wire.openPaymentModal('{{ $loan->uuid }}')" />
                                    @endcan
                                </div>
                            </div>
                        </div>

                        <!-- Loan Details (Expanded) -->
                        @if ($isExpanded)
                            @php
                                $loanDetails = $this->getLoanByUuid($loan->uuid);
                            @endphp
                            <div class="p-4 bg-gray-50 space-y-4">
                                <!-- Applicant Section -->
                                <div class="bg-white p-4 rounded-lg border">
                                    <h3 class="text-lg font-semibold mb-3 text-gray-700">Applicant Information</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        <x-input label="Loan Number" value="{{ $loanDetails->loan_number }}" readonly />
                                        <x-input label="Employee Number" value="{{ $loanDetails->employee_number }}" readonly />
                                        <x-input label="Full Name" value="{{ $loanDetails->full_name }}" readonly />
                                        <x-input label="Department" value="{{ $loanDetails->department->name ?? 'N/A' }}" readonly />
                                        <x-input label="Job Title" value="{{ $loanDetails->job_title }}" readonly />
                                        <x-input label="Date Joined" value="{{ $loanDetails->date_joined?->format('Y-m-d') }}" readonly />
                                        <x-input label="Loan Amount" value="${{ number_format($loanDetails->loan_amount_requested, 2) }}" readonly />
                                        <x-input label="Repayment Period" value="{{ $loanDetails->repayment_period_months }} months" readonly />
                                        <x-input label="Submission Date" value="{{ $loanDetails->submission_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                                    </div>
                                    <div class="mt-3">
                                        <x-textarea label="Loan Purpose" value="{{ $loanDetails->loan_purpose }}" readonly rows="2" />
                                    </div>
                                </div>

                                <!-- HR Section -->
                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                    <h3 class="text-lg font-semibold mb-3 text-gray-700">HR Information</h3>
                                    @if ($loanDetails->hr_digital_confirmation)
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            <x-input label="Employment Status" value="{{ $loanDetails->employment_status ?? 'N/A' }}" readonly />
                                            <x-input label="Date of Engagement" value="{{ $loanDetails->date_of_engagement?->format('Y-m-d') ?? 'N/A' }}" readonly />
                                            <x-input label="Basic Salary" value="${{ number_format($loanDetails->basic_salary ?? 0, 2) }}" readonly />
                                            <x-input label="Monthly Deduction" value="${{ number_format($loanDetails->monthly_deduction_amount ?? 0, 2) }}" readonly />
                                            <x-input label="Existing Loan Balance" value="${{ number_format($loanDetails->existing_loan_balance ?? 0, 2) }}" readonly />
                                            <x-input label="Monthly Repayment" value="${{ number_format($loanDetails->monthly_repayment ?? 0, 2) }}" readonly />
                                            <x-input label="Last Payment Date" value="{{ $loanDetails->last_payment_date?->format('Y-m-d') ?? 'N/A' }}" readonly />
                                        </div>
                                        @if ($loanDetails->hr_comments)
                                            <div class="mt-3">
                                                <x-textarea label="HR Comments" value="{{ $loanDetails->hr_comments }}" readonly rows="2" />
                                            </div>
                                        @endif
                                    @else
                                        <x-alert class="alert-warning" title="HR data not captured" />
                                    @endif
                                </div>

                                <!-- Approval History -->
                                <div class="bg-white p-4 rounded-lg border">
                                    <h3 class="text-lg font-semibold mb-3 text-gray-700">Approval History</h3>
                                    <div class="space-y-2">
                                        @foreach ($loanDetails->workflow->workflowparameters->sortBy('order') as $wp)
                                            @php
                                                $approval = $loanDetails->approvals?->where('workflowparameter_id', $wp->id)->first();
                                                $status = $approval?->status ?? 'PENDING';
                                                $statusColor = match ($status) {
                                                    'APPROVED' => 'bg-green-100 text-green-800',
                                                    'REJECTED' => 'bg-red-100 text-red-800',
                                                    'PENDING' => 'bg-yellow-100 text-yellow-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                            @endphp
                                            <div class="bg-gray-50 p-3 rounded border">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="text-sm font-semibold">Step {{ $wp->order }}: {{ $wp->name }}</span>
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">{{ $status }}</span>
                                                </div>
                                                @if ($approval)
                                                    <div class="text-xs text-gray-600 space-y-1">
                                                        <div>Approver: {{ $approval->approver->name ?? 'N/A' }}</div>
                                                        <div>Date: {{ $approval->created_at?->format('Y-m-d H:i:s') ?? '--' }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Execute Payment Button -->
                                <div class="flex justify-end pt-4 border-t">
                                    @can('swl.payment.execute')
                                        <x-button icon="o-currency-dollar" class="btn-success" label="Execute Payment"
                                            @click="$wire.openPaymentModal('{{ $loan->uuid }}')" />
                                    @endcan
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex items-center justify-center h-64">
                <div class="text-center">
                    <x-icon name="o-check-circle" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <div class="text-lg text-gray-500">No loans awaiting payment</div>
                    <div class="text-sm text-gray-400 mt-2">All approved loans have been processed</div>
                </div>
            </div>
        @endif
    </x-card>

    <!-- Payment Execution Modal -->
    <x-modal wire:model="paymentmodal" title="Execute Payment" box-class="max-w-4xl">
        <x-form wire:submit="executepayment">
            <div class="grid grid-cols-2 gap-4">
                <!-- Currency Selection -->
                <x-select wire:model.live="currency_id" label="Currency" 
                    :options="$this->currencies" 
                    option-value="id" 
                    option-label="name"
                    placeholder="Select currency" required />
                
                <!-- Amount Paid (in selected currency) -->
                <x-input wire:model.live="amount_paid" type="number" step="0.01" 
                    label="Amount Paid{{ $this->selectedCurrency ? ' (' . $this->selectedCurrency->name . ')' : '' }}" 
                    prefix="{{ $this->selectedCurrency && ($this->selectedCurrency->name === 'USD' || $this->selectedCurrency->name === 'US Dollar') ? '$' : '' }}" 
                    required />
            </div>
            
            <!-- Exchange Rate Selection (only for non-USD currencies) -->
            @if($this->selectedCurrency && $this->selectedCurrency->name !== 'USD' && $this->selectedCurrency->name !== 'US Dollar')
                <div class="mt-4">
                    <div class="mb-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Select Exchange Rate <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="exchangerate_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                            <option value="">Select the exchange rate used</option>
                            @foreach($this->availableExchangeRates as $rate)
                                <option value="{{ $rate->id }}">
                                    Rate: {{ number_format($rate->value, 4) }} - by {{ $rate->user->name }} ({{ $rate->created_at->format('M d, Y H:i') }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Select the exchange rate that was used for this payment</p>
                    </div>
                    
                    @if($exchangerate_id)
                        <div class="mt-2 p-3 bg-blue-50 rounded-lg">
                            <div class="text-sm">
                                <strong>Exchange Rate:</strong> 1 USD = {{ number_format($exchange_rate_used, 4) }} {{ $this->selectedCurrency->name }}
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Display USD Equivalent -->
                @if($amount_paid_usd)
                    <div class="mt-4">
                        <x-input label="USD Equivalent" 
                            value="${{ number_format($amount_paid_usd, 2) }}" 
                            readonly 
                            hint="This is the USD value that will be recorded" />
                    </div>
                @endif
            @endif
            
            <div class="grid grid-cols-2 gap-4 mt-4">
                <x-input wire:model="payment_method" label="Payment Method" 
                    placeholder="e.g., Bank Transfer, Cheque" required />
                <x-input wire:model="payment_reference" label="Payment Reference" 
                    placeholder="e.g., TXN123456" required />
                <x-input wire:model="payment_date" type="date" label="Payment Date" required />
            </div>
            
            <div class="mt-4">
                <x-file wire:model="proof_of_payment" label="Proof of Payment" accept=".pdf,.jpg,.jpeg,.png" 
                    hint="Max 10MB. PDF, JPG, PNG" required />
            </div>
            <div class="mt-4">
                <x-textarea wire:model="payment_notes" label="Notes (Optional)" rows="3" 
                    placeholder="Any additional notes about this payment..." />
            </div>
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.paymentmodal = false" />
                <x-button class="btn-primary" label="Execute Payment" type="submit" spinner="executepayment" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
