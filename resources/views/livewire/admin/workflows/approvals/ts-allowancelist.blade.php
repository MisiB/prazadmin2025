<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
        link-item-class="text-base" />

    <x-card title="T&S Allowances Approvals" separator class="mt-5 border-2 border-gray-200">

        @if ($workflow)
            <div class="space-y-4">
                @foreach ($workflow->workflowparameters->sortBy('order') as $workflowparameter)
                    @php
                        $stageAllowances = $allowances->where('status', $workflowparameter->status);
                        $count = $stageAllowances->count();
                        $isExpanded = $this->isStageExpanded($workflowparameter->status);
                    @endphp

                    <div class="border-2 rounded-lg border-gray-200 shadow-sm">
                        <!-- Stage Header -->
                        <div class="p-4 bg-gray-50 rounded-t-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4 cursor-pointer hover:bg-gray-100 transition-colors rounded-lg p-2 -m-2"
                                    wire:click="toggleStage('{{ $workflowparameter->status }}')">
                                    <div class="flex items-center gap-2">
                                        <x-icon name="o-chevron-{{ $isExpanded ? 'down' : 'right' }}" class="w-5 h-5" />
                                        <div>
                                            <div class="text-xl font-bold">{{ $workflowparameter->name }}</div>
                                            <div class="text-sm text-gray-600">Step {{ $workflowparameter->order }} - {{ $workflowparameter->status }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <x-badge value="{{ $count }}" class="badge-secondary badge-lg" />
                                    @if ($count > 0)
                                        @can($workflowparameter->permission->name)
                                            <x-button icon="o-check-circle" class="btn-success btn-sm" 
                                                label="Bulk {{ $workflowparameter->order == 1 ? 'Recommend' : ($workflowparameter->order == 2 ? 'Verify' : 'Approve') }} ({{ count(array_intersect($selectedForBulk, $stageAllowances->pluck('uuid')->toArray())) }})"
                                                wire:click.stop="openBulkApprovalModal('{{ $workflowparameter->status }}')"
                                                :disabled="count(array_intersect($selectedForBulk, $stageAllowances->pluck('uuid')->toArray())) == 0" />
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Stage Content - Allowances List -->
                        @if ($isExpanded)
                            <div class="p-4 space-y-3">
                                <!-- Select All / Clear Selection for this stage -->
                                @if ($stageAllowances->count() > 0)
                                    @can($workflowparameter->permission->name)
                                        <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                                            @php
                                                $stageUuids = $stageAllowances->pluck('uuid')->toArray();
                                                $selectedInStage = array_intersect($selectedForBulk, $stageUuids);
                                                $allSelectedInStage = count($selectedInStage) == count($stageUuids);
                                            @endphp
                                            <x-checkbox 
                                                :checked="$allSelectedInStage && count($stageUuids) > 0"
                                                wire:click="selectAllForBulk('{{ $workflowparameter->status }}')"
                                                label="Select All ({{ count($stageUuids) }})" />
                                            @if (count($selectedInStage) > 0)
                                                <x-button icon="o-x-mark" class="btn-ghost btn-xs" 
                                                    label="Clear Selection ({{ count($selectedInStage) }})"
                                                    wire:click="clearBulkSelection" />
                                            @endif
                                        </div>
                                    @endcan
                                @endif

                                @forelse ($stageAllowances as $allowance)
                                    @php
                                        $isAllowanceExpanded = $this->isAllowanceExpanded($allowance->uuid);
                                        $isSelected = in_array($allowance->uuid, $selectedForBulk);
                                    @endphp
                                    <div class="border border-gray-200 rounded-lg shadow-sm {{ $isSelected ? 'ring-2 ring-primary ring-offset-2' : '' }}">
                                        <!-- Allowance Header -->
                                        <div class="p-3 bg-white rounded-t-lg hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4">
                                                    @can($workflowparameter->permission->name)
                                                        <x-checkbox 
                                                            :checked="$isSelected"
                                                            wire:click.stop="toggleBulkSelection('{{ $allowance->uuid }}')" />
                                                    @endcan
                                                    <div class="cursor-pointer" wire:click="toggleAllowance('{{ $allowance->uuid }}')">
                                                        <x-icon name="o-chevron-{{ $isAllowanceExpanded ? 'down' : 'right' }}" class="w-4 h-4" />
                                                    </div>
                                                    <div class="cursor-pointer" wire:click="toggleAllowance('{{ $allowance->uuid }}')">
                                                        <div class="font-semibold">{{ $allowance->application_number }}</div>
                                                        <div class="text-sm text-gray-600">{{ $allowance->full_name }} - {{ $allowance->department->name ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3 cursor-pointer" wire:click="toggleAllowance('{{ $allowance->uuid }}')">
                                                    <div class="text-right">
                                                        <div class="font-semibold">${{ number_format($allowance->balance_due, 2) }}</div>
                                                        <div class="text-xs text-gray-500">{{ $allowance->trip_start_date->format('Y-m-d') }} - {{ $allowance->trip_end_date->format('Y-m-d') }}</div>
                                                    </div>
                                                    @php
                                                        $statusColor = match ($allowance->status) {
                                                            'SUBMITTED' => 'badge-info',
                                                            'UNDER_REVIEW' => 'badge-info',
                                                            'RECOMMENDED' => 'badge-info',
                                                            'APPROVED' => 'badge-success',
                                                            'FINANCE_VERIFIED' => 'badge-success',
                                                            'PAYMENT_PROCESSED' => 'badge-success',
                                                            'REJECTED' => 'badge-error',
                                                            default => 'badge-ghost',
                                                        };
                                                    @endphp
                                                    <x-badge :value="$allowance->status" class="{{ $statusColor }}" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Allowance Details (Expanded) -->
                                        @if ($isAllowanceExpanded)
                                            @php
                                                $allowanceDetails = $this->getAllowanceByUuid($allowance->uuid);
                                            @endphp
                                            <div class="p-4 bg-gray-50 space-y-4">
                                                <x-tabs wire:model="selectedTabs.{{ $allowance->uuid }}">
                                                    <!-- Details Tab -->
                                                    <x-tab name="details-{{ $allowance->uuid }}" label="Details" icon="o-document-text">
                                                        <div class="space-y-4 mt-4">
                                                            <!-- Applicant Section -->
                                                            <div class="bg-white p-4 rounded-lg border">
                                                                <h3 class="text-lg font-semibold mb-3 text-gray-700">Applicant Information</h3>
                                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                                    <x-input label="Application Number" value="{{ $allowanceDetails->application_number }}" readonly />
                                                                    <x-input label="Full Name" value="{{ $allowanceDetails->full_name }}" readonly />
                                                                    <x-input label="Department" value="{{ $allowanceDetails->department->name ?? 'N/A' }}" readonly />
                                                                    <x-input label="Job Title" value="{{ $allowanceDetails->job_title }}" readonly />
                                                                    <x-input label="Grade" value="{{ $allowanceDetails->grade }}" readonly />
                                                                    <x-input label="Trip Start Date" value="{{ $allowanceDetails->trip_start_date?->format('Y-m-d') }}" readonly />
                                                                    <x-input label="Trip End Date" value="{{ $allowanceDetails->trip_end_date?->format('Y-m-d') }}" readonly />
                                                                    <x-input label="Balance Due" value="${{ number_format($allowanceDetails->balance_due, 2) }}" readonly />
                                                                    <x-input label="Submission Date" value="{{ $allowanceDetails->submission_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                                                                </div>
                                                                <div class="mt-3">
                                                                    <x-textarea label="Reason for Allowances" readonly rows="3">{{ $allowanceDetails->reason_for_allowances }}</x-textarea>
                                                                </div>
                                                                @if ($allowanceDetails->trip_attachment_path)
                                                                    <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                                                        <div class="flex items-center gap-2">
                                                                            <x-icon name="o-paper-clip" class="w-5 h-5 text-blue-600" />
                                                                            <span class="text-sm font-medium text-blue-800">Trip Supporting Document</span>
                                                                            <a href="{{ asset('storage/' . $allowanceDetails->trip_attachment_path) }}" 
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
                                                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                                                <h3 class="text-lg font-semibold mb-3 text-gray-700">Allowance Breakdown</h3>
                                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                                    <x-input label="Out of Station/Subsistence" value="${{ number_format($allowanceDetails->out_of_station_subsistence, 2) }}" readonly />
                                                                    <x-input label="Overnight Allowance" value="${{ number_format($allowanceDetails->overnight_allowance, 2) }}" readonly />
                                                                    <x-input label="Bed Allowance" value="${{ number_format($allowanceDetails->bed_allowance, 2) }}" readonly />
                                                                    <x-input label="Breakfast" value="${{ number_format($allowanceDetails->breakfast, 2) }}" readonly />
                                                                    <x-input label="Lunch" value="${{ number_format($allowanceDetails->lunch, 2) }}" readonly />
                                                                    <x-input label="Dinner" value="${{ number_format($allowanceDetails->dinner, 2) }}" readonly />
                                                                    <x-input label="Fuel" value="${{ number_format($allowanceDetails->fuel, 2) }}" readonly />
                                                                    <x-input label="Toll Gates" value="${{ number_format($allowanceDetails->toll_gates, 2) }}" readonly />
                                                                    <x-input label="Mileage/Distance (km)" value="{{ number_format($allowanceDetails->mileage_estimated_distance, 2) }}" readonly />
                                                                </div>
                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-blue-200">
                                                                    <x-input label="Calculated Subtotal" value="${{ number_format($allowanceDetails->calculated_subtotal, 2) }}" readonly class="font-bold" />
                                                                    <x-input label="Balance Due" value="${{ number_format($allowanceDetails->balance_due, 2) }}" readonly class="font-bold" />
                                                                </div>
                                                            </div>

                                                            <!-- HOD Recommendation Section -->
                                                            @if ($allowanceDetails->status != 'DRAFT' && $allowanceDetails->status != 'SUBMITTED' && $allowanceDetails->hod_digital_signature)
                                                                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                                                                    <h3 class="text-lg font-semibold mb-3 text-gray-700">HOD Recommendation</h3>
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                                        <x-input label="Decision" value="{{ $allowanceDetails->recommendation_decision ?? 'N/A' }}" readonly />
                                                                        <x-input label="HOD Name" value="{{ $allowanceDetails->hod_name ?? 'N/A' }}" readonly />
                                                                        <x-input label="Recommendation Date" value="{{ $allowanceDetails->recommendation_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                                                                    </div>
                                                                    @if ($allowanceDetails->hod_comment)
                                                                        <div class="mt-3">
                                                                            <x-textarea label="HOD Comment" readonly rows="3">{{ $allowanceDetails->hod_comment }}</x-textarea>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif

                                                            <!-- Finance Verification Section - Only show for PAYMENT_PROCESSED status -->
                                                            @if ($allowanceDetails->status == 'PAYMENT_PROCESSED' && $allowanceDetails->verified_total_amount)
                                                                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                                                    <h3 class="text-lg font-semibold mb-3 text-gray-700">Finance Verification</h3>
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                                        <x-input label="Verified Total Amount" value="${{ number_format($allowanceDetails->verified_total_amount ?? 0, 2) }}" readonly />
                                                                        @if($allowanceDetails->exchange_rate_applied)
                                                                            <x-input label="Exchange Rate Applied" value="{{ number_format($allowanceDetails->exchange_rate_applied, 4) }}" readonly />
                                                                        @endif
                                                                        <x-input label="Finance Officer" value="{{ $allowanceDetails->finance_officer_name ?? 'N/A' }}" readonly />
                                                                        <x-input label="Verification Date" value="{{ $allowanceDetails->verification_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                                                                    </div>
                                                                    @if ($allowanceDetails->finance_comment)
                                                                        <div class="mt-3">
                                                                            <x-textarea label="Finance Comment" readonly rows="3">{{ $allowanceDetails->finance_comment }}</x-textarea>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif

                                                            <!-- Payment Section -->
                                                            @if ($allowanceDetails->status == 'PAYMENT_PROCESSED')
                                                                <div class="bg-green-100 p-4 rounded-lg border border-green-200">
                                                                    <h3 class="text-lg font-semibold mb-3 text-gray-700">Payment Information</h3>
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                                        @if($allowanceDetails->currency_id)
                                                                            <x-input label="Currency" value="{{ $allowanceDetails->currency->name ?? 'N/A' }}" readonly />
                                                                            <x-input label="Amount Paid ({{ $allowanceDetails->currency->name ?? '' }})" 
                                                                                value="{{ number_format($allowanceDetails->amount_paid_original ?? $allowanceDetails->amount_paid_usd, 2) }}" readonly />
                                                                        @endif
                                                                        <x-input label="Amount Paid (USD)" value="${{ number_format($allowanceDetails->amount_paid_usd ?? 0, 2) }}" readonly />
                                                                        <x-input label="Payment Method" value="{{ $allowanceDetails->payment_method ?? 'N/A' }}" readonly />
                                                                        <x-input label="Payment Reference" value="{{ $allowanceDetails->payment_reference ?? 'N/A' }}" readonly />
                                                                        <x-input label="Payment Date" value="{{ $allowanceDetails->payment_date?->format('Y-m-d') ?? 'N/A' }}" readonly />
                                                                        <x-input label="Payment Capture Date" value="{{ $allowanceDetails->payment_capture_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
                                                                    </div>
                                                                    @if ($allowanceDetails->proof_of_payment_path)
                                                                        <div class="mt-3">
                                                                            <x-button icon="o-document" class="btn-outline btn-info" label="View Proof of Payment"
                                                                                link="{{ asset('storage/' . $allowanceDetails->proof_of_payment_path) }}" target="_blank" />
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </x-tab>

                                                    <!-- Approvals Tab -->
                                                    <x-tab name="approvals-{{ $allowance->uuid }}" label="Approvals" icon="o-document-check">
                                                        <div class="mt-4 space-y-3">
                                                            @foreach ($allowanceDetails->workflow->workflowparameters->sortBy('order') as $wp)
                                                                @php
                                                                    // Get ALL approvals for this step, sorted by date
                                                                    $stepApprovals = $allowanceDetails->approvals?->where('workflowparameter_id', $wp->id)->sortBy('created_at') ?? collect();
                                                                    $latestApproval = $stepApprovals->last();
                                                                    $currentStatus = $latestApproval?->status ?? 'PENDING';
                                                                    $headerColor = match ($currentStatus) {
                                                                        'APPROVED' => 'bg-green-50 border-green-200',
                                                                        'REJECTED' => 'bg-red-50 border-red-200',
                                                                        'SEND_BACK' => 'bg-orange-50 border-orange-200',
                                                                        'PENDING' => 'bg-yellow-50 border-yellow-200',
                                                                        default => 'bg-gray-50 border-gray-200',
                                                                    };
                                                                @endphp
                                                                <div class="rounded border-2 {{ $headerColor }}">
                                                                    <div class="p-3 font-semibold text-sm border-b {{ $headerColor }}">
                                                                        Step {{ $wp->order }}: {{ $wp->name }}
                                                                        @if($stepApprovals->count() > 1)
                                                                            <span class="text-xs text-gray-500 ml-2">({{ $stepApprovals->count() }} actions)</span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="p-3 space-y-2">
                                                                        @forelse ($stepApprovals as $approval)
                                                                            @php
                                                                                $statusColor = match ($approval->status) {
                                                                                    'APPROVED' => 'bg-green-100 text-green-800 border-green-300',
                                                                                    'REJECTED' => 'bg-red-100 text-red-800 border-red-300',
                                                                                    'SEND_BACK' => 'bg-orange-100 text-orange-800 border-orange-300',
                                                                                    default => 'bg-gray-100 text-gray-800 border-gray-300',
                                                                                };
                                                                            @endphp
                                                                            <div class="p-2 rounded border {{ $statusColor }} text-xs">
                                                                                <div class="flex items-center justify-between mb-1">
                                                                                    <span class="font-medium">{{ $approval->approver->name ?? 'N/A' }}</span>
                                                                                    <span class="px-2 py-0.5 rounded-full font-medium {{ $statusColor }}">{{ $approval->status }}</span>
                                                                                </div>
                                                                                <div class="text-gray-600">
                                                                                    <div>Comment: {{ $approval->comment ?? '--' }}</div>
                                                                                    <div>Date: {{ $approval->created_at?->format('Y-m-d H:i:s') ?? '--' }}</div>
                                                                                </div>
                                                                            </div>
                                                                        @empty
                                                                            <div class="text-xs text-gray-500 italic p-2 bg-yellow-50 rounded">
                                                                                Awaiting action
                                                                            </div>
                                                                        @endforelse
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </x-tab>
                                                </x-tabs>

                                                <!-- Action Buttons -->
                                                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t">
                                                    @if ($allowanceDetails->status == $workflowparameter->status && !in_array($allowanceDetails->status, ['APPROVED', 'REJECTED', 'PAYMENT_PROCESSED']))
                                                        @can($workflowparameter->permission->name)
                                                            <x-button icon="o-check-circle" class="btn-primary btn-sm" label="Make Decision"
                                                                @click="$wire.openDecisionModal('{{ $allowance->uuid }}')" />
                                                        @endcan
                                                    @endif

                                                    @if ($allowanceDetails->status == 'APPROVED')
                                                        <x-alert icon="o-information-circle" class="alert-info text-sm">
                                                            Approved - Awaiting payment on <a href="{{ route('admin.workflows.approvals.ts-allowance-finance') }}" class="link link-primary">Finance Payments page</a>
                                                        </x-alert>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-gray-500">
                                        No allowances in this stage
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
    <x-modal wire:model="decisionmodal" title="{{ $currentStepName ?? 'Make Decision' }}">
        <x-form wire:submit="savedecision">
            <x-select label="Decision" wire:model.live="decision" placeholder="Select Decision"
                :options="$this->decisionOptions" />
            <x-textarea label="Comment" wire:model="comment" placeholder="Enter your comment (required for rejection)" />
            <x-pin label="Approval Code" wire:model="approvalcode" size="6" hide />
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.decisionmodal = false" />
                <x-button icon="o-check" class="btn-primary" label="Submit" type="submit" spinner="savedecision" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <!-- Bulk Approval Modal -->
    <x-modal wire:model="bulkapprovalmodal" title="Bulk {{ $this->bulkDecisionLabel ?? 'Approve' }} - {{ $bulkStepName ?? 'Selected Allowances' }}">
        <x-form wire:submit="executeBulkApproval">
            <div class="alert alert-info mb-4">
                <x-icon name="o-information-circle" class="w-5 h-5" />
                <span>You are about to <strong>{{ $this->bulkDecisionLabel ?? 'approve' }}</strong> <strong>{{ count($selectedForBulk) }}</strong> allowance(s).</span>
            </div>

            <!-- Summary of selected allowances -->
            @if (count($selectedForBulk) > 0)
                <div class="bg-gray-50 rounded-lg p-3 mb-4 max-h-48 overflow-y-auto">
                    <div class="text-sm font-medium text-gray-700 mb-2">Selected Allowances:</div>
                    <div class="space-y-1">
                        @foreach ($selectedForBulk as $uuid)
                            @php
                                $selectedAllowance = $this->getAllowanceByUuid($uuid);
                            @endphp
                            @if ($selectedAllowance)
                                <div class="flex items-center justify-between text-sm bg-white p-2 rounded border">
                                    <div>
                                        <span class="font-medium">{{ $selectedAllowance->application_number }}</span>
                                        <span class="text-gray-500">- {{ $selectedAllowance->full_name }}</span>
                                    </div>
                                    <span class="font-semibold text-primary">${{ number_format($selectedAllowance->balance_due, 2) }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @php
                        $totalAmount = collect($selectedForBulk)->sum(function($uuid) {
                            $allowance = $this->getAllowanceByUuid($uuid);
                            return $allowance ? $allowance->balance_due : 0;
                        });
                    @endphp
                    <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between font-bold">
                        <span>Total Amount:</span>
                        <span class="text-primary">${{ number_format($totalAmount, 2) }}</span>
                    </div>
                </div>
            @endif

            <x-textarea label="Comment (applies to all)" wire:model="bulkComment" 
                placeholder="Enter a comment for bulk approval (optional)" rows="2" />
            
            <x-pin label="Approval Code" wire:model="bulkApprovalCode" size="6" hide />

            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Cancel" 
                    wire:click="$set('bulkapprovalmodal', false)" />
                <x-button icon="o-check-circle" class="btn-success" 
                    label="Confirm Bulk {{ $this->bulkDecisionLabel ?? 'Approve' }}" 
                    type="submit" spinner="executeBulkApproval" />
            </x-slot:actions>
        </x-form>
    </x-modal>

</div>

