<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
        link-item-class="text-base" />

    <x-card title="Staff Welfare Loan Approvals" separator class="mt-5 border-2 border-gray-200">

        @if ($workflow)
            <div class="space-y-4">
                @foreach ($workflow->workflowparameters->sortBy('order') as $workflowparameter)
                    @php
                        $stageLoans = $loans->where('status', $workflowparameter->status);
                        $count = $stageLoans->count();
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
                                </div>
                            </div>
                        </div>

                        <!-- Stage Content - Loans List -->
                        @if ($isExpanded)
                            <div class="p-4 space-y-3">
                                @forelse ($stageLoans as $loan)
                                    @php
                                        $isLoanExpanded = $this->isLoanExpanded($loan->uuid);
                                    @endphp
                                    <div class="border border-gray-200 rounded-lg shadow-sm">
                                        <!-- Loan Header -->
                                        <div class="p-3 bg-white rounded-t-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                            wire:click="toggleLoan('{{ $loan->uuid }}')">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4">
                                                    <x-icon name="o-chevron-{{ $isLoanExpanded ? 'down' : 'right' }}" class="w-4 h-4" />
                                                    <div>
                                                        <div class="font-semibold">{{ $loan->loan_number }}</div>
                                                        <div class="text-sm text-gray-600">{{ $loan->full_name }} - {{ $loan->department->name ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <div class="text-right">
                                                        <div class="font-semibold">${{ number_format($loan->loan_amount_requested, 2) }}</div>
                                                        <div class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($loan->loan_purpose, 30) }}</div>
                                                    </div>
                                                    @php
                                                        $statusColor = match ($loan->status) {
                                                            'SUBMITTED' => 'badge-info',
                                                            'HR_REVIEW' => 'badge-info',
                                                            'FINANCE_REVIEW' => 'badge-info',
                                                            'CEO_APPROVAL' => 'badge-info',
                                                            'APPROVED' => 'badge-success',
                                                            'REJECTED' => 'badge-error',
                                                            'AWAITING_ACKNOWLEDGEMENT' => 'badge-warning',
                                                            'COMPLETED' => 'badge-success',
                                                            default => 'badge-ghost',
                                                        };
                                                    @endphp
                                                    <x-badge :value="$loan->status" class="{{ $statusColor }}" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Loan Details (Expanded) -->
                                        @if ($isLoanExpanded)
                                            @php
                                                $loanDetails = $this->getLoanByUuid($loan->uuid);
                                            @endphp
                                            <div class="p-4 bg-gray-50 space-y-4">
                                                <x-tabs wire:model="selectedTabs.{{ $loan->uuid }}">
                                                    <!-- Details Tab -->
                                                    <x-tab name="details-{{ $loan->uuid }}" label="Details" icon="o-document-text">
                                                        <div class="space-y-4 mt-4">
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
                                                                    <x-textarea label="Loan Purpose" readonly rows="3">{{ $loanDetails->loan_purpose }}</x-textarea>
                                                                </div>
                                                            </div>

                                                            <!-- HR Section -->
                                                            @if ($loanDetails->status != 'DRAFT' && $loanDetails->status != 'SUBMITTED')
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
                                                                            <x-input label="HR Review Date" value="{{ $loanDetails->hr_review_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                                                                        </div>
                                                                        @if ($loanDetails->hr_comments)
                                                                            <div class="mt-3">
                                                                                <x-textarea label="HR Comments" readonly rows="3">{{ $loanDetails->hr_comments }}</x-textarea>
                                                                            </div>
                                                                        @endif
                                                                    @else
                                                                        <x-alert class="alert-warning" title="HR data not yet captured" />
                                                                    @endif
                                                                </div>
                                                            @endif

                                                            <!-- Payment Section -->
                                                            @if (in_array($loanDetails->status, ['PAYMENT_PROCESSED', 'AWAITING_ACKNOWLEDGEMENT', 'COMPLETED']))
                                                                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                                                    <h3 class="text-lg font-semibold mb-3 text-gray-700">Payment Information</h3>
                                                                    @if ($loanDetails->finance_officer_confirmation)
                                                                        @php
                                                                            $payment = $loanDetails->payments->first();
                                                                        @endphp
                                                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                                            @if($payment && $payment->currency_id)
                                                                                <x-input label="Currency" value="{{ $payment->currency->name ?? 'N/A' }}" readonly />
                                                                                <x-input label="Amount Paid ({{ $payment->currency->name ?? '' }})" 
                                                                                    value="{{ number_format($payment->amount_paid_original ?? $loanDetails->amount_paid, 2) }}" readonly />
                                                                                @if($payment->exchangerate_id)
                                                                                    <x-input label="Exchange Rate" 
                                                                                        value="1 USD = {{ number_format($payment->exchange_rate_used, 4) }} {{ $payment->currency->name }}" 
                                                                                        readonly />
                                                                                    <x-input label="USD Equivalent" value="${{ number_format($payment->amount_paid_usd ?? $loanDetails->amount_paid, 2) }}" readonly />
                                                                                    <x-input label="Rate Set By" value="{{ $payment->exchangerate->user->name ?? 'N/A' }}" readonly />
                                                                                @else
                                                                                    <x-input label="USD Amount" value="${{ number_format($payment->amount_paid_usd ?? $loanDetails->amount_paid, 2) }}" readonly />
                                                                                @endif
                                                                            @else
                                                                                <x-input label="Amount Paid (USD)" value="${{ number_format($loanDetails->amount_paid ?? 0, 2) }}" readonly />
                                                                            @endif
                                                                            <x-input label="Payment Method" value="{{ $loanDetails->payment_method ?? 'N/A' }}" readonly />
                                                                            <x-input label="Payment Reference" value="{{ $loanDetails->payment_reference ?? 'N/A' }}" readonly />
                                                                            <x-input label="Payment Date" value="{{ $loanDetails->payment_date?->format('Y-m-d') ?? 'N/A' }}" readonly />
                                                                            <x-input label="Capture Date" value="{{ $loanDetails->payment_capture_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                                                                            <x-input label="Finance Officer" value="{{ $loanDetails->financeOfficer->name ?? 'N/A' }}" readonly />
                                                                        </div>
                                                                        @if ($loanDetails->proof_of_payment_path)
                                                                            <div class="mt-3">
                                                                                <x-button icon="o-document" class="btn-outline btn-info" label="View Proof of Payment"
                                                                                    link="{{ asset('storage/' . $loanDetails->proof_of_payment_path) }}" target="_blank" />
                                                                            </div>
                                                                        @endif
                                                                    @else
                                                                        <x-alert class="alert-warning" title="Payment not yet processed" />
                                                                    @endif
                                                                </div>
                                                            @endif

                                                            <!-- Acknowledgement Section -->
                                                            @if ($loanDetails->status == 'COMPLETED')
                                                                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                                                                    <h3 class="text-lg font-semibold mb-3 text-gray-700">Employee Acknowledgement</h3>
                                                                    @if ($loanDetails->employee_digital_acceptance)
                                                                        <div class="space-y-3">
                                                                            <x-textarea label="Acknowledgement Statement" readonly rows="4">{{ $loanDetails->acknowledgement_of_debt_statement }}</x-textarea>
                                                                            <x-input label="Acceptance Date" value="{{ $loanDetails->acceptance_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                                                                        </div>
                                                                    @else
                                                                        <x-alert class="alert-warning" title="Debt not yet acknowledged" />
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </x-tab>

                                                    <!-- Approvals Tab -->
                                                    <x-tab name="approvals-{{ $loan->uuid }}" label="Approvals" icon="o-document-check">
                                                        <div class="mt-4 space-y-3">
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
                                                                <div class="bg-white p-3 rounded border">
                                                                    <div class="flex items-center justify-between mb-2">
                                                                        <span class="text-sm font-semibold">Step {{ $wp->order }}: {{ $wp->name }}</span>
                                                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">{{ $status }}</span>
                                                                    </div>
                                                                    @if ($approval)
                                                                        <div class="text-xs text-gray-600 space-y-1">
                                                                            <div>Approver: {{ $approval->approver->name ?? 'N/A' }}</div>
                                                                            <div>Comment: {{ $approval->comment ?? '--' }}</div>
                                                                            <div>Date: {{ $approval->created_at?->format('Y-m-d H:i:s') ?? '--' }}</div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </x-tab>
                                                </x-tabs>

                                                <!-- Action Buttons -->
                                                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t">
                                                    @if ($loanDetails->status == $workflowparameter->status && $loanDetails->status != 'APPROVED' && $loanDetails->status != 'REJECTED')
                                                        @can($workflowparameter->permission->name)
                                                            <x-button icon="o-check-circle" class="btn-primary btn-sm" label="Make Decision"
                                                                @click="$wire.openDecisionModal('{{ $loan->uuid }}')" />
                                                        @endcan
                                                    @endif

                                                    @if ($loanDetails->status == 'HR_REVIEW')
                                                        @can('swl.edit.hr.section')
                                                            <x-button icon="o-pencil" class="btn-info btn-sm" label="Capture HR Data"
                                                                @click="$wire.openHrDataModal('{{ $loan->uuid }}')" />
                                                        @endcan
                                                    @endif

                                                    @if ($loanDetails->status == 'APPROVED')
                                                        @can('swl.payment.execute')
                                                            <x-button icon="o-currency-dollar" class="btn-success btn-sm" label="Execute Payment"
                                                                @click="$wire.openPaymentModal('{{ $loan->uuid }}')" />
                                                        @endcan
                                                    @endif

                                                    @if ($loanDetails->status == 'AWAITING_ACKNOWLEDGEMENT' && $loanDetails->applicant_user_id == auth()->id())
                                                        @can('swl.acknowledge.debt')
                                                            <x-button icon="o-check-circle" class="btn-warning btn-sm" label="Acknowledge Debt"
                                                                @click="$wire.openAcknowledgementModal('{{ $loan->uuid }}')" />
                                                        @endcan
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-gray-500">
                                        No loans in this stage
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
    <x-modal wire:model="decisionmodal" title="Make Decision">
        <x-form wire:submit="savedecision">
            <x-select label="Decision" wire:model.live="decision" placeholder="Select Decision"
                :options="[['id' => 'APPROVED', 'name' => 'APPROVED'], ['id' => 'REJECT', 'name' => 'REJECT']]" />
            <x-textarea label="Comment" wire:model="comment" />
            <x-pin label="Approval Code" wire:model="approvalcode" size="6" hide />
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.decisionmodal = false" />
                <x-button icon="o-check" class="btn-primary" label="Submit" type="submit" spinner="savedecision" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <!-- HR Data Capture Modal -->
    <x-modal wire:model="hrdatamodal" title="Capture HR Data" box-class="max-w-4xl">
        <x-form wire:submit="savehrdata">
            <x-alert class="alert-info mb-4" title="Auto-Calculated Fields"
                description="Interest, monthly deduction, and existing loan balance will be automatically calculated based on loan configuration settings. Salary will be hashed for privacy after capture." />
            
            <div class="grid grid-cols-2 gap-4">
                <x-select wire:model="employment_status" label="Employment Status" 
                    :options="[['id' => 'PERMANENT', 'name' => 'PERMANENT'], ['id' => 'CONTRACT', 'name' => 'CONTRACT']]" 
                    placeholder="Select employment status" />
                <x-input wire:model="date_of_engagement" type="date" label="Date of Engagement" 
                    :hint="'Last payment date will be calculated as ' . $selectedLoanRepaymentMonths . ' months from this date'" />
                <x-input wire:model="basic_salary" type="number" step="0.01" label="Basic Salary" prefix="$" 
                    hint="This value will be hashed for privacy" />
            </div>
            <div class="mt-4">
                <x-textarea wire:model="hr_comments" label="HR Comments" rows="3" />
            </div>
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.hrdatamodal = false" />
                <x-button class="btn-primary" label="Save HR Data" type="submit" spinner="savehrdata" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <!-- Payment Execution Modal -->
    <x-modal wire:model="paymentmodal" title="Execute Payment" box-class="max-w-4xl">
        <x-form wire:submit="executepayment">
            <div class="grid grid-cols-2 gap-4">
                <x-input wire:model="amount_paid" type="number" step="0.01" label="Amount Paid" prefix="$" />
                <x-input wire:model="payment_method" label="Payment Method" />
                <x-input wire:model="payment_reference" label="Payment Reference" />
                <x-input wire:model="payment_date" type="date" label="Payment Date" />
            </div>
            <div class="mt-4">
                <x-file wire:model="proof_of_payment" label="Proof of Payment" accept=".pdf,.jpg,.jpeg,.png" hint="Max 10MB. PDF, JPG, PNG" />
            </div>
            <div class="mt-4">
                <x-textarea wire:model="payment_notes" label="Notes" rows="3" />
            </div>
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.paymentmodal = false" />
                <x-button class="btn-primary" label="Execute Payment" type="submit" spinner="executepayment" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <!-- Debt Acknowledgement Modal -->
    <x-modal wire:model="acknowledgementmodal" title="Acknowledge Debt" box-class="max-w-3xl">
        <x-form wire:submit="acknowledgedebt">
            <x-alert class="alert-info mb-4" title="Important"
                description="By acknowledging this debt, you confirm that you understand and accept your repayment obligations." />
            <x-textarea wire:model="acknowledgement_statement" label="Acknowledgement Statement"
                placeholder="I acknowledge that I have received the loan amount and understand my repayment obligations..."
                rows="6" />
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.acknowledgementmodal = false" />
                <x-button class="btn-primary" label="Acknowledge Debt" type="submit" spinner="acknowledgedebt" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
