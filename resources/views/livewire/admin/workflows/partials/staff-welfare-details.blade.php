<div class="space-y-4">
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
            <x-input label="Loan Amount Requested" value="${{ number_format($loanDetails->loan_amount_requested, 2) }}" readonly />
            <x-input label="Repayment Period" value="{{ $loanDetails->repayment_period_months }} months" readonly />
            <x-input label="Submission Date" value="{{ $loanDetails->submission_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
        </div>
        <div class="mt-3">
            <x-textarea label="Loan Purpose" readonly rows="3">{{ $loanDetails->loan_purpose }}</x-textarea>
        </div>
    </div>

    <!-- HR Section -->
    @if ($loanDetails->hr_digital_confirmation)
        <div class="bg-white p-4 rounded-lg border">
            <h3 class="text-lg font-semibold mb-3 text-gray-700">HR Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <x-input label="Employment Status" value="{{ $loanDetails->employment_status ?? 'N/A' }}" readonly />
                <x-input label="Date of Engagement" value="{{ $loanDetails->date_of_engagement?->format('Y-m-d') ?? 'N/A' }}" readonly />
                <x-input label="Basic Salary" value="{{ $loanDetails->basic_salary_hash ? '******' : '$' . number_format($loanDetails->basic_salary ?? 0, 2) }}" readonly />
                <x-input label="Existing Loan Balance" value="${{ number_format($loanDetails->existing_loan_balance ?? 0, 2) }}" readonly />
                <x-input label="Monthly Deduction" value="${{ number_format($loanDetails->monthly_deduction_amount ?? 0, 2) }}" readonly />
                <x-input label="Monthly Repayment" value="${{ number_format($loanDetails->monthly_repayment ?? 0, 2) }}" readonly />
                <x-input label="Last Payment Date" value="{{ $loanDetails->last_payment_date?->format('Y-m-d') ?? 'N/A' }}" readonly />
                <x-input label="HR Review Date" value="{{ $loanDetails->hr_review_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
            </div>
            
            <!-- Loan Calculation Details -->
            <div class="mt-4 p-4 bg-gray-50 rounded-lg border">
                <h4 class="text-md font-semibold mb-3 text-gray-600">Loan Calculation Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="stat bg-base-100 rounded-lg p-3 border">
                        <div class="stat-title text-xs">Interest Rate Applied</div>
                        <div class="stat-value text-lg text-warning">{{ $loanDetails->interest_rate_applied ?? 0 }}%</div>
                    </div>
                    <div class="stat bg-base-100 rounded-lg p-3 border">
                        <div class="stat-title text-xs">Interest Amount</div>
                        <div class="stat-value text-lg text-warning">${{ number_format($loanDetails->interest_amount ?? 0, 2) }}</div>
                    </div>
                    <div class="stat bg-base-100 rounded-lg p-3 border">
                        <div class="stat-title text-xs">Total Repayment</div>
                        <div class="stat-value text-lg text-success">${{ number_format($loanDetails->total_repayment_amount ?? 0, 2) }}</div>
                    </div>
                    <div class="stat bg-base-100 rounded-lg p-3 border">
                        <div class="stat-title text-xs">Monthly Deduction</div>
                        <div class="stat-value text-lg text-info">${{ number_format($loanDetails->monthly_deduction_amount ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
            
            @if ($loanDetails->hr_comments)
                <div class="mt-3">
                    <x-textarea label="HR Comments" readonly rows="3">{{ $loanDetails->hr_comments }}</x-textarea>
                </div>
            @endif
        </div>
    @endif

    <!-- Payment Section -->
    @if ($loanDetails->finance_officer_confirmation)
        <div class="bg-white p-4 rounded-lg border">
            <h3 class="text-lg font-semibold mb-3 text-gray-700">Payment Information</h3>
            @php
                $payment = $loanDetails->payments->first();
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @if($payment && $payment->currency_id)
                    <x-input label="Currency" value="{{ $payment->currency->name ?? 'N/A' }}" readonly />
                    <x-input label="Amount Paid ({{ $payment->currency->name ?? '' }})" 
                        value="{{ number_format($payment->amount_paid_original ?? $loanDetails->amount_paid, 2) }}" readonly />
                @else
                    <x-input label="Amount Paid (USD)" value="${{ number_format($loanDetails->amount_paid ?? 0, 2) }}" readonly />
                @endif
                <x-input label="Payment Method" value="{{ $loanDetails->payment_method ?? 'N/A' }}" readonly />
                <x-input label="Payment Reference" value="{{ $loanDetails->payment_reference ?? 'N/A' }}" readonly />
                <x-input label="Payment Date" value="{{ $loanDetails->payment_date?->format('Y-m-d') ?? 'N/A' }}" readonly />
            </div>
            @if ($loanDetails->proof_of_payment_path)
                <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2">
                        <x-icon name="o-paper-clip" class="w-5 h-5 text-green-600" />
                        <span class="text-sm font-medium text-green-800">Proof of Payment</span>
                        <a href="{{ asset('storage/' . $loanDetails->proof_of_payment_path) }}" 
                            target="_blank" 
                            class="ml-auto btn btn-sm btn-outline btn-success">
                            <x-icon name="o-document-arrow-down" class="w-4 h-4" />
                            View Attachment
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Approval History -->
    @if($loanDetails->approvals && $loanDetails->approvals->count() > 0)
        <div class="bg-white p-4 rounded-lg border">
            <h3 class="text-lg font-semibold mb-3 text-gray-700">Approval History</h3>
            <div class="space-y-2">
                @foreach($loanDetails->approvals as $approval)
                    <div class="border rounded p-3 text-sm">
                        <div class="grid grid-cols-2 gap-2">
                            <div><span class="font-medium">Approver:</span> {{ $approval->approver->name ?? 'N/A' }}</div>
                            <div><span class="font-medium">Date:</span> {{ $approval->created_at->format('Y-m-d H:i:s') }}</div>
                            <div><span class="font-medium">Decision:</span> {{ $approval->decision ?? 'N/A' }}</div>
                            <div><span class="font-medium">Status:</span> {{ $approval->workflowParameter->status ?? 'N/A' }}</div>
                            @if($approval->comment)
                                <div class="col-span-2"><span class="font-medium">Comment:</span> {{ $approval->comment }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

