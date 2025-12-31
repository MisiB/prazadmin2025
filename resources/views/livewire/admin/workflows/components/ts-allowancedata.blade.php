<div>
    @php
        $allowance = $this->getallowance();
    @endphp

    <x-card title="Travel & Subsistence Allowance" subtitle="{{ $allowance->status }}" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            @if (in_array($allowance->status, ['SUBMITTED', 'UNDER_REVIEW', 'RECOMMENDED']))
                @php
                    $workflowParameter = $allowance->workflow->workflowparameters
                        ->where('status', $allowance->status)
                        ->first();
                @endphp
                @if($workflowParameter)
                    @can($workflowParameter->permission->name)
                        <x-button icon="o-check-circle" class="btn-primary" label="Make Decision" @click="$wire.decisionmodal=true" />
                    @endcan
                @endif
            @endif
            @if ($allowance->status == 'APPROVED')
                @can('tsa.verify.rates')
                    <x-button icon="o-document-check" class="btn-info" label="Verify Finance" @click="$wire.financeverificationmodal=true" />
                @endcan
            @endif
            @if ($allowance->status == 'FINANCE_VERIFIED')
                @can('tsa.payment.execute')
                    <x-button icon="o-currency-dollar" class="btn-success" label="Process Payment" @click="$wire.paymentmodal=true" />
                @endcan
            @endif
        </x-slot:menu>

        <!-- Allowance Details Section -->
        <div name="details-tab">
            <div class="space-y-6">
                <!-- Applicant Section -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Applicant Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <x-input label="Application Number" value="{{ $allowance->application_number }}" readonly />
                        <x-input label="Full Name" value="{{ $allowance->full_name }}" readonly />
                        <x-input label="Job Title" value="{{ $allowance->job_title }}" readonly />
                        <x-input label="Department" value="{{ $allowance->department->name ?? 'N/A' }}" readonly />
                        <x-input label="Grade" value="{{ $allowance->grade }}" readonly />
                        <x-input label="Trip Start Date" value="{{ $allowance->trip_start_date?->format('Y-m-d') }}" readonly />
                        <x-input label="Trip End Date" value="{{ $allowance->trip_end_date?->format('Y-m-d') }}" readonly />
                        <x-input label="Number of Days" value="{{ $allowance->number_of_days }}" readonly />
                        <x-input label="Submission Date" value="{{ $allowance->submission_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                    </div>
                    <div class="mt-4">
                        <x-textarea label="Reason for Allowances" readonly rows="3">{{ $allowance->reason_for_allowances }}</x-textarea>
                    </div>
                    @if ($allowance->trip_attachment_path)
                        <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-center gap-2">
                                <x-icon name="o-paper-clip" class="w-5 h-5 text-blue-600" />
                                <span class="text-sm font-medium text-blue-800">Trip Supporting Document</span>
                                <a href="{{ asset('storage/' . $allowance->trip_attachment_path) }}" 
                                    target="_blank" 
                                    class="ml-auto btn btn-sm btn-outline btn-info">
                                    <x-icon name="o-document-arrow-down" class="w-4 h-4" />
                                    View Attachment
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Allowance Breakdown Section -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700">Allowance Breakdown</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <x-input label="Out of Station/Subsistence" value="${{ number_format($allowance->out_of_station_subsistence, 2) }}" readonly />
                        <x-input label="Overnight Allowance" value="${{ number_format($allowance->overnight_allowance, 2) }}" readonly />
                        <x-input label="Bed Allowance" value="${{ number_format($allowance->bed_allowance, 2) }}" readonly />
                        <x-input label="Breakfast" value="${{ number_format($allowance->breakfast, 2) }}" readonly />
                        <x-input label="Lunch" value="${{ number_format($allowance->lunch, 2) }}" readonly />
                        <x-input label="Dinner" value="${{ number_format($allowance->dinner, 2) }}" readonly />
                        <x-input label="Fuel" value="${{ number_format($allowance->fuel, 2) }}" readonly />
                        <x-input label="Toll Gates" value="${{ number_format($allowance->toll_gates, 2) }}" readonly />
                        <x-input label="Mileage/Distance (km)" value="{{ number_format($allowance->mileage_estimated_distance, 2) }}" readonly />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-blue-200">
                        <x-input label="Calculated Subtotal" value="${{ number_format($allowance->calculated_subtotal, 2) }}" readonly class="font-bold" />
                        <x-input label="Balance Due" value="${{ number_format($allowance->balance_due, 2) }}" readonly class="font-bold" />
                    </div>
                </div>

                <!-- Recommendation Section (HOD) -->
                @if ($allowance->status != 'DRAFT' && $allowance->hod_digital_signature)
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">HOD Recommendation</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <x-input label="Decision" value="{{ $allowance->recommendation_decision ?? 'N/A' }}" readonly />
                            <x-input label="HOD Name" value="{{ $allowance->hod_name ?? 'N/A' }}" readonly />
                            <x-input label="HOD Designation" value="{{ $allowance->hod_designation ?? 'N/A' }}" readonly />
                            <x-input label="Recommendation Date" value="{{ $allowance->recommendation_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                        </div>
                        @if ($allowance->hod_comment)
                            <div class="mt-4">
                                <x-textarea label="HOD Comment" readonly rows="3">{{ $allowance->hod_comment }}</x-textarea>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Approval Section (CEO) -->
                @if (in_array($allowance->status, ['APPROVED', 'FINANCE_VERIFIED', 'PAYMENT_PROCESSED']) && $allowance->ceo_digital_signature)
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">CEO Approval</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input label="Decision" value="{{ $allowance->approval_decision ?? 'N/A' }}" readonly />
                            <x-input label="Approval Date" value="{{ $allowance->approval_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                        </div>
                        @if ($allowance->ceo_comment)
                            <div class="mt-4">
                                <x-textarea label="CEO Comment" readonly rows="3">{{ $allowance->ceo_comment }}</x-textarea>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Finance Verification Section -->
                @if (in_array($allowance->status, ['FINANCE_VERIFIED', 'PAYMENT_PROCESSED']) && $allowance->finance_digital_signature)
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Finance Verification</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <x-input label="Verified Total Amount" value="${{ number_format($allowance->verified_total_amount ?? 0, 2) }}" readonly />
                            @if($allowance->exchange_rate_applied)
                                <x-input label="Exchange Rate Applied" value="{{ number_format($allowance->exchange_rate_applied, 4) }}" readonly />
                            @endif
                            <x-input label="Finance Officer" value="{{ $allowance->finance_officer_name ?? 'N/A' }}" readonly />
                            <x-input label="Verification Date" value="{{ $allowance->verification_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                        </div>
                        @if ($allowance->finance_comment)
                            <div class="mt-4">
                                <x-textarea label="Finance Comment" readonly rows="3">{{ $allowance->finance_comment }}</x-textarea>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Payment Section -->
                @if ($allowance->status == 'PAYMENT_PROCESSED')
                    <div class="bg-green-100 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Payment Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @if($allowance->currency_id)
                                <x-input label="Currency" value="{{ $allowance->currency->name ?? 'N/A' }}" readonly />
                                <x-input label="Amount Paid ({{ $allowance->currency->name ?? '' }})" 
                                    value="{{ number_format($allowance->amount_paid_original ?? $allowance->amount_paid_usd, 2) }}" readonly />
                            @endif
                            <x-input label="Amount Paid (USD)" value="${{ number_format($allowance->amount_paid_usd ?? 0, 2) }}" readonly />
                            <x-input label="Payment Method" value="{{ $allowance->payment_method ?? 'N/A' }}" readonly />
                            <x-input label="Payment Reference" value="{{ $allowance->payment_reference ?? 'N/A' }}" readonly />
                            <x-input label="Payment Date" value="{{ $allowance->payment_date?->format('Y-m-d') ?? 'N/A' }}" readonly />
                            <x-input label="Payment Capture Date" value="{{ $allowance->payment_capture_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                        </div>
                        @if ($allowance->proof_of_payment_path)
                            <div class="mt-4">
                                <x-button icon="o-document" class="btn-outline btn-info" label="View Proof of Payment"
                                    link="{{ asset('storage/' . $allowance->proof_of_payment_path) }}" target="_blank" />
                            </div>
                        @endif
                        @if ($allowance->payment_notes)
                            <div class="mt-4">
                                <x-textarea label="Payment Notes" readonly rows="3">{{ $allowance->payment_notes }}</x-textarea>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Approval History -->
                @if ($allowance->approvals->count() > 0)
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4 text-gray-700">Approval History</h3>
                        <div class="space-y-3">
                            @foreach ($allowance->workflow->workflowparameters->sortBy('order') as $wp)
                                @php
                                    $approval = $allowance->approvals?->where('workflowparameter_id', $wp->id)->first();
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
                    </div>
                @endif
            </div>
        </div>
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

    <!-- Finance Verification Modal -->
    <x-modal wire:model="financeverificationmodal" title="Finance Verification" box-class="max-w-4xl">
        <x-form wire:submit="savefinanceverification">
            <div class="grid grid-cols-2 gap-4">
                <x-input wire:model="verified_total_amount" type="number" step="0.01" label="Verified Total Amount" prefix="$" />
                <x-input wire:model="exchange_rate_applied" type="number" step="0.0001" label="Exchange Rate Applied (Optional)" />
            </div>
            <div class="mt-4">
                <x-textarea wire:model="finance_comment" label="Finance Comment" rows="3" />
            </div>
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.financeverificationmodal = false" />
                <x-button class="btn-primary" label="Verify Finance" type="submit" spinner="savefinanceverification" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <!-- Payment Execution Modal -->
    <x-modal wire:model="paymentmodal" title="Execute Payment" box-class="max-w-4xl">
        <x-form wire:submit="executepayment">
            <div class="grid grid-cols-2 gap-4">
                <x-input wire:model="amount_paid_usd" type="number" step="0.01" label="Amount Paid (USD)" prefix="$" />
                <x-input wire:model="amount_paid_original" type="number" step="0.01" label="Amount Paid (Original Currency)" />
                <x-input wire:model="payment_method" label="Payment Method" />
                <x-input wire:model="payment_reference" label="Payment Reference" />
                <x-input wire:model="payment_date" type="date" label="Payment Date" />
            </div>
            <div class="mt-4">
                <x-file wire:model="proof_of_payment" label="Proof of Payment (Optional)" accept=".pdf,.jpg,.jpeg,.png" hint="Max 10MB. PDF, JPG, PNG" />
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
</div>

