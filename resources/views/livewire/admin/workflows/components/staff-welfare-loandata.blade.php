<div>
    @php
        $loan = $this->getloan();
    @endphp

    <x-card title="Staff Welfare Loan" subtitle="{{ $loan->status }}" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            @if ($loan->status == 'HR_REVIEW')
                @can('swl.edit.hr.section')
                    <x-button icon="o-pencil" class="btn-primary" label="Capture HR Data" @click="$wire.hrdatamodal=true" />
                @endcan
            @endif
            @if ($loan->status == 'AWAITING_ACKNOWLEDGEMENT' && $loan->applicant_user_id == auth()->id())
                @can('swl.acknowledge.debt')
                    <x-button icon="o-check-circle" class="btn-primary" label="Acknowledge Debt" @click="$wire.acknowledgementmodal=true" />
                @endcan
            @endif
        </x-slot:menu>

        <!-- Loan Details Section -->
        <div name="details-tab">
                <div class="space-y-6">
                    <!-- Applicant Section -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Applicant Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <x-input label="Loan Number" value="{{ $loan->loan_number }}" readonly />
                            <x-input label="Employee Number" value="{{ $loan->employee_number }}" readonly />
                            <x-input label="Full Name" value="{{ $loan->full_name }}" readonly />
                            <x-input label="Department" value="{{ $loan->department->name ?? 'N/A' }}" readonly />
                            <x-input label="Job Title" value="{{ $loan->job_title }}" readonly />
                            <x-input label="Date Joined" value="{{ $loan->date_joined?->format('Y-m-d') }}" readonly />
                            <x-input label="Loan Amount Requested" value="${{ number_format($loan->loan_amount_requested, 2) }}" readonly />
                            <x-input label="Repayment Period" value="{{ $loan->repayment_period_months }} months" readonly />
                            <x-input label="Submission Date" value="{{ $loan->submission_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                        </div>
                        <div class="mt-4">
                            <x-textarea label="Loan Purpose" readonly rows="3">{{ $loan->loan_purpose }}</x-textarea>
                        </div>
                    </div>

                    <!-- HR Section -->
                    @if ($loan->status != 'DRAFT' && $loan->status != 'SUBMITTED')
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">HR Information</h3>
                            @if ($loan->hr_digital_confirmation)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <x-input label="Employment Status" value="{{ $loan->employment_status ?? 'N/A' }}" readonly />
                                    <x-input label="Date of Engagement" value="{{ $loan->date_of_engagement?->format('Y-m-d') ?? 'N/A' }}" readonly />
                                    <x-input label="Basic Salary" value="{{ $loan->basic_salary_hash ? '******' : '$' . number_format($loan->basic_salary ?? 0, 2) }}" readonly />
                                    <x-input label="Existing Loan Balance" value="${{ number_format($loan->existing_loan_balance ?? 0, 2) }}" readonly />
                                    <x-input label="Last Payment Date" value="{{ $loan->last_payment_date?->format('Y-m-d') ?? 'N/A' }}" readonly />
                                    <x-input label="HR Review Date" value="{{ $loan->hr_review_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                                </div>
                                <!-- Loan Calculation Details -->
                                <div class="mt-4 p-4 bg-white rounded-lg border border-blue-200">
                                    <h4 class="text-md font-semibold mb-3 text-gray-600">Loan Calculation Details</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <div class="stat bg-base-100 rounded-lg p-3 border">
                                            <div class="stat-title text-xs">Interest Rate Applied</div>
                                            <div class="stat-value text-lg text-warning">{{ $loan->interest_rate_applied ?? 0 }}%</div>
                                        </div>
                                        <div class="stat bg-base-100 rounded-lg p-3 border">
                                            <div class="stat-title text-xs">Interest Amount</div>
                                            <div class="stat-value text-lg text-warning">${{ number_format($loan->interest_amount ?? 0, 2) }}</div>
                                        </div>
                                        <div class="stat bg-base-100 rounded-lg p-3 border">
                                            <div class="stat-title text-xs">Total Repayment</div>
                                            <div class="stat-value text-lg text-success">${{ number_format($loan->total_repayment_amount ?? 0, 2) }}</div>
                                        </div>
                                        <div class="stat bg-base-100 rounded-lg p-3 border">
                                            <div class="stat-title text-xs">Monthly Deduction</div>
                                            <div class="stat-value text-lg text-info">${{ number_format($loan->monthly_deduction_amount ?? 0, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                                @if ($loan->hr_comments)
                                    <div class="mt-4">
                                        <x-textarea label="HR Comments" readonly rows="3">{{ $loan->hr_comments }}</x-textarea>
                                    </div>
                                @endif
                            @else
                                <x-alert class="alert-warning" title="HR data not yet captured" />
                            @endif
                        </div>
                    @endif

                    <!-- Payment Section -->
                    @if ($loan->status == 'PAYMENT_PROCESSED' || $loan->status == 'AWAITING_ACKNOWLEDGEMENT' || $loan->status == 'COMPLETED')
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Payment Information</h3>
                            @if ($loan->finance_officer_confirmation)
                                @php
                                    $payment = $loan->payments->first();
                                @endphp
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @if($payment && $payment->currency_id)
                                        <x-input label="Currency" value="{{ $payment->currency->name ?? 'N/A' }}" readonly />
                                        <x-input label="Amount Paid ({{ $payment->currency->name ?? '' }})" 
                                            value="{{ number_format($payment->amount_paid_original ?? $loan->amount_paid, 2) }}" readonly />
                                        @if($payment->exchangerate_id)
                                            <x-input label="Exchange Rate" 
                                                value="1 USD = {{ number_format($payment->exchange_rate_used, 4) }} {{ $payment->currency->name }}" 
                                                readonly />
                                            <x-input label="USD Equivalent" value="${{ number_format($payment->amount_paid_usd ?? $loan->amount_paid, 2) }}" readonly />
                                            <x-input label="Rate Set By" value="{{ $payment->exchangerate->user->name ?? 'N/A' }}" readonly />
                                        @else
                                            <x-input label="USD Amount" value="${{ number_format($payment->amount_paid_usd ?? $loan->amount_paid, 2) }}" readonly />
                                        @endif
                                    @else
                                        <x-input label="Amount Paid (USD)" value="${{ number_format($loan->amount_paid ?? 0, 2) }}" readonly />
                                    @endif
                                    <x-input label="Payment Method" value="{{ $loan->payment_method ?? 'N/A' }}" readonly />
                                    <x-input label="Payment Reference" value="{{ $loan->payment_reference ?? 'N/A' }}" readonly />
                                    <x-input label="Payment Date" value="{{ $loan->payment_date?->format('Y-m-d') ?? 'N/A' }}" readonly />
                                    <x-input label="Payment Capture Date" value="{{ $loan->payment_capture_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                                    <x-input label="Finance Officer" value="{{ $loan->financeOfficer->name ?? 'N/A' }}" readonly />
                                </div>
                                @if ($loan->proof_of_payment_path)
                                    <div class="mt-4">
                                        <x-button icon="o-document" class="btn-outline btn-info" label="View Proof of Payment"
                                            link="{{ asset('storage/' . $loan->proof_of_payment_path) }}" target="_blank" />
                                    </div>
                                @endif
                            @else
                                <x-alert class="alert-warning" title="Payment not yet processed" />
                            @endif
                        </div>
                    @endif

                    <!-- Acknowledgement Section -->
                    @if ($loan->status == 'COMPLETED')
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Employee Acknowledgement</h3>
                            @if ($loan->employee_digital_acceptance)
                                <div class="space-y-4">
                                    <x-textarea label="Acknowledgement Statement" readonly rows="4">{{ $loan->acknowledgement_of_debt_statement }}</x-textarea>
                                    <x-input label="Acceptance Date" value="{{ $loan->acceptance_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                                </div>
                            @else
                                <x-alert class="alert-warning" title="Debt not yet acknowledged" />
                            @endif
                        </div>
                    @endif
                </div>
        </div>
    </x-card>

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
                    hint="Last payment date will be calculated as {{ $loan->repayment_period_months ?? 0 }} months from this date" />
                <x-input wire:model="basic_salary" type="number" step="0.01" label="Basic Salary" prefix="$" 
                    hint="This value will be hashed for privacy" />
            </div>
            <div class="mt-4">
                <x-textarea wire:model="hr_comments" label="HR Comments" rows="3" />
            </div>
            
            <!-- Preview of calculations -->
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-md font-semibold mb-3 text-gray-600">Calculation Preview</h4>
                <p class="text-sm text-gray-500 mb-2">Based on current loan configuration:</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    <div class="bg-white p-2 rounded border">
                        <span class="text-gray-500">Principal:</span><br>
                        <span class="font-semibold">${{ number_format($loan->loan_amount_requested ?? 0, 2) }}</span>
                    </div>
                    <div class="bg-white p-2 rounded border">
                        <span class="text-gray-500">Months:</span><br>
                        <span class="font-semibold">{{ $loan->repayment_period_months ?? 0 }}</span>
                    </div>
                    <div class="bg-white p-2 rounded border">
                        <span class="text-gray-500">Interest & Total:</span><br>
                        <span class="font-semibold text-xs">Calculated on save</span>
                    </div>
                    <div class="bg-white p-2 rounded border">
                        <span class="text-gray-500">Monthly Deduction:</span><br>
                        <span class="font-semibold text-xs">Calculated on save</span>
                    </div>
                </div>
            </div>
            
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.hrdatamodal = false" />
                <x-button class="btn-primary" label="Save HR Data" type="submit" spinner="savehrdata" />
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
