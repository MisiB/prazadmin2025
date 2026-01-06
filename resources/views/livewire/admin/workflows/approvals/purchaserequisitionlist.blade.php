<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
    link-item-class="text-base" />

    <x-card title="Purchase Requisition Approvals" separator class="mt-5 border-2 border-gray-200">
     
        @if ($workflow)
            <div class="space-y-4">
                {{-- Step 0: Supervisor/HOD Recommendation (AWAITING_RECOMMENDATION) --}}
                @can("purchaserequisition.recommend")
                    @php
                        $awaitingRequisitions = $awaitingrecommendation;
                        $awaitingCount = $awaitingRequisitions->count();
                        $isExpanded = $this->isStageExpanded('AWAITING_RECOMMENDATION');
                    @endphp

                    <div class="border-2 rounded-lg border-gray-200 shadow-sm">
                        <!-- Stage Header -->
                        <div class="p-4 bg-gray-50 rounded-t-lg cursor-pointer hover:bg-gray-100 transition-colors"
                            wire:click="toggleStage('AWAITING_RECOMMENDATION')">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <x-icon name="o-chevron-{{ $isExpanded ? 'down' : 'right' }}" class="w-5 h-5" />
                                        <div>
                                            <div class="text-xl font-bold">Supervisor/HOD Recommendation</div>
                                            <div class="text-sm text-gray-600">Step 0 - AWAITING_RECOMMENDATION</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <x-badge value="{{ $awaitingCount }}" class="badge-secondary badge-lg" />
                                    @if ($awaitingCount > 0)
                                        @can('purchaserequisition.recommend')
                                            @php
                                                $stageUuids = $awaitingRequisitions->pluck('uuid')->toArray();
                                                $selectedInStage = array_intersect($selectedForBulk, $stageUuids);
                                            @endphp
                                            <x-button icon="o-check-circle" class="btn-success btn-sm" 
                                                label="Bulk Recommend ({{ count($selectedInStage) }})"
                                                wire:click.stop="openBulkApprovalModal('AWAITING_RECOMMENDATION')"
                                                :disabled="count($selectedInStage) == 0" />
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Stage Content - Requisitions List -->
                        @if ($isExpanded)
                            <div class="p-4 space-y-3">
                                <!-- Select All / Clear Selection for this stage -->
                                @if ($awaitingRequisitions->count() > 0)
                                    @can('purchaserequisition.recommend')
                                        <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                                            @php
                                                $stageUuids = $awaitingRequisitions->pluck('uuid')->toArray();
                                                $selectedInStage = array_intersect($selectedForBulk, $stageUuids);
                                                $allSelectedInStage = count($selectedInStage) == count($stageUuids);
                                            @endphp
                                            <x-checkbox 
                                                :checked="$allSelectedInStage && count($stageUuids) > 0"
                                                wire:click="selectAllForBulk('AWAITING_RECOMMENDATION')"
                                                label="Select All ({{ count($stageUuids) }})" />
                                            @if (count($selectedInStage) > 0)
                                                <x-button icon="o-x-mark" class="btn-ghost btn-xs" 
                                                    label="Clear Selection ({{ count($selectedInStage) }})"
                                                    wire:click="clearBulkSelection" />
                                            @endif
                                        </div>
                                    @endcan
                                @endif
                                @forelse ($awaitingRequisitions as $requisition)
                                    @php
                                        $isRequisitionExpanded = $this->isRequisitionExpanded($requisition->uuid);
                                    @endphp
                                    <div class="border border-gray-200 rounded-lg shadow-sm">
                                        <!-- Requisition Header -->
                                        <div class="p-3 bg-white rounded-t-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                            wire:click="toggleRequisition('{{ $requisition->uuid }}')">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4">
                                                    <x-icon name="o-chevron-{{ $isRequisitionExpanded ? 'down' : 'right' }}" class="w-4 h-4" />
                                                    <div>
                                                        <div class="font-semibold">{{ $requisition->prnumber }}</div>
                                                        <div class="text-sm text-gray-600">{{ $requisition->budgetitem->activity ?? 'N/A' }} - {{ $requisition->department->name ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <div class="text-right">
                                                        <div class="font-semibold">{{ $requisition->budgetitem->currency->name ?? 'USD' }} {{ number_format($requisition->budgetitem->unitprice * $requisition->quantity, 2) }}</div>
                                                        <div class="text-xs text-gray-500">{{ Str::limit($requisition->purpose, 30) }}</div>
                                                    </div>
                                                    <x-badge value="AWAITING_RECOMMENDATION" class="badge-info" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Requisition Details (Expanded) -->
                                        @if ($isRequisitionExpanded)
                                            @php
                                                $requisitionDetails = $this->getRequisitionByUuid($requisition->uuid);
                                            @endphp
                                            <div class="p-4 bg-gray-50 space-y-4">
                                                <x-tabs wire:model="selectedTabs.{{ $requisition->uuid }}">
                                                    <!-- Details Tab -->
                                                    <x-tab name="details-{{ $requisition->uuid }}" label="Details" icon="o-document-text">
                                                        <div class="space-y-4 mt-4">
                                                            <!-- Requisition Section -->
                                                            <div class="bg-white p-4 rounded-lg border">
                                                                <h3 class="text-lg font-semibold mb-3 text-gray-700">Purchase Requisition Information</h3>
                                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                                    <x-input label="PR Number" value="{{ $requisitionDetails->prnumber }}" readonly />
                                                                    <x-input label="Year" value="{{ $requisitionDetails->year }}" readonly />
                                                                    <x-input label="Department" value="{{ $requisitionDetails->department->name ?? 'N/A' }}" readonly />
                                                                    <x-input label="Budget Item" value="{{ $requisitionDetails->budgetitem->activity ?? 'N/A' }}" readonly />
                                                                    <x-input label="Requested By" value="{{ $requisitionDetails->requestedby->name ?? 'N/A' }}" readonly />
                                                                    <x-input label="Quantity" value="{{ $requisitionDetails->quantity }}" readonly />
                                                                    <x-input label="Unit Price" value="{{ $requisitionDetails->budgetitem->currency->name ?? 'USD' }} {{ number_format($requisitionDetails->budgetitem->unitprice, 2) }}" readonly />
                                                                    <x-input label="Total" value="{{ $requisitionDetails->budgetitem->currency->name ?? 'USD' }} {{ number_format($requisitionDetails->budgetitem->unitprice * $requisitionDetails->quantity, 2) }}" readonly />
                                                                    <x-input label="Status" value="{{ $requisitionDetails->status }}" readonly />
                                                                </div>
                                                                <div class="mt-3">
                                                                    <x-textarea label="Purpose" readonly rows="3">{{ $requisitionDetails->purpose }}</x-textarea>
                                                                </div>
                                                                @if ($requisitionDetails->description)
                                                                    <div class="mt-3">
                                                                        <x-textarea label="Description" readonly rows="3">{{ $requisitionDetails->description }}</x-textarea>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </x-tab>

                                                    <!-- Approvals Tab -->
                                                    <x-tab name="approvals-{{ $requisition->uuid }}" label="Approvals" icon="o-document-check">
                                                        <div class="mt-4 space-y-3">
                                                            @if ($requisitionDetails->workflow)
                                                                @foreach ($requisitionDetails->workflow->workflowparameters->sortBy('order') as $wp)
                                                                    @php
                                                                        $approval = $requisitionDetails->approvals?->where('workflowparameter_id', $wp->id)->first();
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
                                                                                <div>Approver: {{ $approval->user->name ?? 'N/A' }}</div>
                                                                                <div>Comment: {{ $approval->comment ?? '--' }}</div>
                                                                                <div>Date: {{ $approval->created_at?->format('Y-m-d H:i:s') ?? '--' }}</div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </x-tab>
                                                </x-tabs>

                                                <!-- Action Buttons -->
                                                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t">
                                                    @if ($requisitionDetails->status == 'AWAITING_RECOMMENDATION')
                                                        @can('purchaserequisition.recommend')
                                                            <x-button icon="o-check-circle" class="btn-primary btn-sm" label="Make Decision"
                                                                @click="$wire.openDecisionModal('{{ $requisition->uuid }}')" />
                                                        @endcan
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-gray-500">
                                        No requisitions awaiting recommendation
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                @endcan

                {{-- Workflow Steps --}}
                @foreach ($workflow->workflowparameters->sortBy('order') as $workflowparameter)
                    @php
                        $stageRequisitions = $requisitions->where('status', $workflowparameter->status);
                        $count = $stageRequisitions->count();
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
                                    @if ($count > 0)
                                        @can($workflowparameter->permission->name)
                                            @php
                                                $stageUuids = $stageRequisitions->pluck('uuid')->toArray();
                                                $selectedInStage = array_intersect($selectedForBulk, $stageUuids);
                                            @endphp
                                            <x-button icon="o-check-circle" class="btn-success btn-sm" 
                                                label="Bulk Approve ({{ count($selectedInStage) }})"
                                                wire:click.stop="openBulkApprovalModal('{{ $workflowparameter->status }}')"
                                                :disabled="count($selectedInStage) == 0" />
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Stage Content - Requisitions List -->
                        @if ($isExpanded)
                            <div class="p-4 space-y-3">
                                <!-- Select All / Clear Selection for this stage -->
                                @if ($stageRequisitions->count() > 0)
                                    @can($workflowparameter->permission->name)
                                        <div class="flex items-center gap-3 pb-3 border-b border-gray-200">
                                            @php
                                                $stageUuids = $stageRequisitions->pluck('uuid')->toArray();
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
                                @forelse ($stageRequisitions as $requisition)
                                    @php
                                        $isRequisitionExpanded = $this->isRequisitionExpanded($requisition->uuid);
                                        $isSelected = in_array($requisition->uuid, $selectedForBulk);
                                    @endphp
                                    <div class="border border-gray-200 rounded-lg shadow-sm {{ $isSelected ? 'ring-2 ring-primary ring-offset-2' : '' }}">
                                        <!-- Requisition Header -->
                                        <div class="p-3 bg-white rounded-t-lg cursor-pointer hover:bg-gray-50 transition-colors"
                                            wire:click="toggleRequisition('{{ $requisition->uuid }}')">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-4">
                                                    @can($workflowparameter->permission->name)
                                                        <x-checkbox 
                                                            :checked="$isSelected"
                                                            wire:click.stop="toggleBulkSelection('{{ $requisition->uuid }}')"
                                                            class="checkbox-sm" />
                                                    @endcan
                                                    <x-icon name="o-chevron-{{ $isRequisitionExpanded ? 'down' : 'right' }}" class="w-4 h-4" />
                                                    <div>
                                                        <div class="font-semibold">{{ $requisition->prnumber }}</div>
                                                        <div class="text-sm text-gray-600">{{ $requisition->budgetitem->activity ?? 'N/A' }} - {{ $requisition->department->name ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <div class="text-right">
                                                        <div class="font-semibold">{{ $requisition->budgetitem->currency->name ?? 'USD' }} {{ number_format($requisition->budgetitem->unitprice * $requisition->quantity, 2) }}</div>
                                                        <div class="text-xs text-gray-500">{{ Str::limit($requisition->purpose, 30) }}</div>
                                                    </div>
                                                    @php
                                                        $statusColor = match ($requisition->status) {
                                                            'BUDGET_CONFIRMATION' => 'badge-info',
                                                            'FINANCE_RECOMMENDATION' => 'badge-info',
                                                            'FINANCE_APPROVAL' => 'badge-info',
                                                            'ADMIN_RECOMMENDATION' => 'badge-info',
                                                            'ADMIN_APPROVAL' => 'badge-info',
                                                            'CEO_APPROVAL' => 'badge-info',
                                                            'APPROVED' => 'badge-success',
                                                            'REJECTED' => 'badge-error',
                                                            default => 'badge-ghost',
                                                        };
                                                    @endphp
                                                    <x-badge :value="$requisition->status" class="{{ $statusColor }}" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Requisition Details (Expanded) -->
                                        @if ($isRequisitionExpanded)
                                            @php
                                                $requisitionDetails = $this->getRequisitionByUuid($requisition->uuid);
                                            @endphp
                                            <div class="p-4 bg-gray-50 space-y-4">
                                                <x-tabs wire:model="selectedTabs.{{ $requisition->uuid }}">
                                                    <!-- Details Tab -->
                                                    <x-tab name="details-{{ $requisition->uuid }}" label="Details" icon="o-document-text">
                                                        <div class="space-y-4 mt-4">
                                                            <!-- Requisition Section -->
                                                            <div class="bg-white p-4 rounded-lg border">
                                                                <h3 class="text-lg font-semibold mb-3 text-gray-700">Purchase Requisition Information</h3>
                                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                                                    <x-input label="PR Number" value="{{ $requisitionDetails->prnumber }}" readonly />
                                                                    <x-input label="Year" value="{{ $requisitionDetails->year }}" readonly />
                                                                    <x-input label="Department" value="{{ $requisitionDetails->department->name ?? 'N/A' }}" readonly />
                                                                    <x-input label="Budget Item" value="{{ $requisitionDetails->budgetitem->activity ?? 'N/A' }}" readonly />
                                                                    <x-input label="Requested By" value="{{ $requisitionDetails->requestedby->name ?? 'N/A' }}" readonly />
                                                                    <x-input label="Recommended By" value="{{ $requisitionDetails->recommendedby->name ?? 'N/A' }}" readonly />
                                                                    <x-input label="Quantity" value="{{ $requisitionDetails->quantity }}" readonly />
                                                                    <x-input label="Unit Price" value="{{ $requisitionDetails->budgetitem->currency->name ?? 'USD' }} {{ number_format($requisitionDetails->budgetitem->unitprice, 2) }}" readonly />
                                                                    <x-input label="Total" value="{{ $requisitionDetails->budgetitem->currency->name ?? 'USD' }} {{ number_format($requisitionDetails->budgetitem->unitprice * $requisitionDetails->quantity, 2) }}" readonly />
                                                                    <x-input label="Status" value="{{ $requisitionDetails->status }}" readonly />
                                                                </div>
                                                                <div class="mt-3">
                                                                    <x-textarea label="Purpose" readonly rows="3">{{ $requisitionDetails->purpose }}</x-textarea>
                                                                </div>
                                                                @if ($requisitionDetails->description)
                                                                    <div class="mt-3">
                                                                        <x-textarea label="Description" readonly rows="3">{{ $requisitionDetails->description }}</x-textarea>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </x-tab>

                                                    <!-- Approvals Tab -->
                                                    <x-tab name="approvals-{{ $requisition->uuid }}" label="Approvals" icon="o-document-check">
                                                        <div class="mt-4 space-y-3">
                                                            @if ($requisitionDetails->workflow)
                                                                @foreach ($requisitionDetails->workflow->workflowparameters->sortBy('order') as $wp)
                                                                    @php
                                                                        $approval = $requisitionDetails->approvals?->where('workflowparameter_id', $wp->id)->first();
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
                                                                                <div>Approver: {{ $approval->user->name ?? 'N/A' }}</div>
                                                                                <div>Comment: {{ $approval->comment ?? '--' }}</div>
                                                                                <div>Date: {{ $approval->created_at?->format('Y-m-d H:i:s') ?? '--' }}</div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </x-tab>
                                                </x-tabs>

                                                <!-- Action Buttons -->
                                                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t">
                                                    @if ($requisitionDetails->status == $workflowparameter->status && !in_array($requisitionDetails->status, ['APPROVED', 'REJECTED']))
                                                        @can($workflowparameter->permission->name)
                                                            <x-button icon="o-check-circle" class="btn-primary btn-sm" label="Make Decision"
                                                                @click="$wire.openDecisionModal('{{ $requisition->uuid }}')" />
                                                        @endcan
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-gray-500">
                                        No requisitions in this stage
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
            <x-textarea label="Comment" wire:model="comment" placeholder="Enter your comment (optional)" />
            <x-pin label="Approval Code" wire:model="approvalcode" size="6" hide />
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.decisionmodal = false" />
                <x-button icon="o-check" class="btn-primary" label="Submit" type="submit" spinner="savedecision" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <!-- Bulk Approval Modal -->
    <x-modal wire:model="bulkapprovalmodal" title="Bulk {{ $this->bulkDecisionLabel ?? 'Approve' }} - {{ $bulkStepName ?? '' }}">
        <x-form wire:submit="executeBulkApproval">
            <x-alert icon="o-information-circle" class="alert-info mb-4">
                You are about to {{ strtolower($this->bulkDecisionLabel ?? 'approve') }} {{ count($selectedForBulk) }} requisition(s).
            </x-alert>
            <x-textarea label="Comment (Optional)" wire:model="bulkComment" placeholder="Enter a comment for all selected requisitions" />
            <x-pin label="Approval Code" wire:model="bulkApprovalCode" size="6" hide />
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.bulkapprovalmodal = false" />
                <x-button icon="o-check" class="btn-primary" label="Bulk {{ $this->bulkDecisionLabel ?? 'Approve' }}" type="submit" spinner="executeBulkApproval" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
 