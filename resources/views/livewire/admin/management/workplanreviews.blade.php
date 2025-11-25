<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"/>
    
    <x-card title="Workplan Reviews" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button label="Get Workplans" wire:click="modal=true" class="btn-primary" />
        </x-slot:menu>

        <!-- Tabs -->
        <div class="tabs tabs-boxed mb-4">
            <button 
                wire:click="switchTab('pending')" 
                class="tab {{ $activeTab === 'pending' ? 'tab-active' : '' }}">
                Pending Reviews
            </button>
            <button 
                wire:click="switchTab('approved')" 
                class="tab {{ $activeTab === 'approved' ? 'tab-active' : '' }}">
                Approved Workplans
            </button>
        </div>

        <!-- Pending Workplans Tab -->
        @if($activeTab === 'pending')
            @if(isset($workplans) && $workplans->count() > 0)
            <x-table :headers="$headers" :rows="$workplans">
                @scope('cell_user.name', $workplan)
                    {{ $workplan->user->name }} {{ $workplan->user->surname }}
                @endscope
                
                @scope('cell_status', $workplan)
                    <x-badge :value="$workplan->status" 
                        :class="$workplan->status == 'APPROVED' ? 'badge-success' : 'badge-warning'"/>
                @endscope
                
                @scope('cell_weightage', $workplan)
                    {{ $workplan->weightage }}%
                @endscope
                
                @scope('cell_actions', $workplan)
                    <div class="flex gap-2 justify-end">
                        <x-button icon="o-eye" 
                            class="btn-info btn-ghost btn-sm" 
                            wire:click="reviewworkplan({{ $workplan->id }})" 
                            tooltip="Review Workplan" />
                        <x-button icon="o-check-circle" 
                            class="btn-success btn-ghost btn-sm" 
                            wire:click="approveworkplan({{ $workplan->id }})" 
                            wire:confirm="Are you sure you want to approve this workplan?"
                            tooltip="Approve Workplan" />
                    </div>
                @endscope
            </x-table>
            @else
                <x-alert class="alert-info" icon="o-information-circle" 
                    title="No pending workplans found. Click 'Get Workplans' to search." />
            @endif
        @endif

        <!-- Approved Workplans Tab -->
        @if($activeTab === 'approved')
            @if(isset($approvedWorkplans) && $approvedWorkplans->count() > 0)
            <x-table :headers="$headers" :rows="$approvedWorkplans">
                @scope('cell_user.name', $workplan)
                    {{ $workplan->user->name }} {{ $workplan->user->surname }}
                @endscope
                
                @scope('cell_status', $workplan)
                    <x-badge :value="$workplan->status" 
                        :class="$workplan->status == 'APPROVED' ? 'badge-success' : 'badge-warning'"/>
                @endscope
                
                @scope('cell_weightage', $workplan)
                    {{ $workplan->weightage }}%
                @endscope
                
                @scope('cell_actions', $workplan)
                    <div class="flex gap-2 justify-end">
                        <x-button icon="o-eye" 
                            class="btn-info btn-ghost btn-sm" 
                            wire:click="reviewworkplan({{ $workplan->id }})" 
                            tooltip="View Workplan" />
                    </div>
                @endscope
            </x-table>
            @else
                <x-alert class="alert-info" icon="o-information-circle" 
                    title="No approved workplans found. Click 'Get Workplans' to search." />
            @endif
        @endif
    </x-card>

    <!-- Search Modal -->
    <x-modal title="Search Parameters" wire:model="modal">
        <x-form wire:submit.prevent="getworkplans">
            <div class="grid gap-2">
                <x-select label="Strategy" 
                    wire:model="strategy_id" 
                    placeholder="Select Strategy" 
                    :options="$strategies" 
                    option-label="name" 
                    option-value="id" />
                <x-input label="Year" 
                    wire:model="year" 
                    type="number" />
            </div>
            <x-slot:actions>
                <x-button label="Close" 
                    wire:click="$wire.closeModal()" 
                    class="btn-outline" />
                <x-button label="Search" 
                    type="submit" 
                    class="btn-primary" 
                    spinner="getworkplans" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <!-- Review Modal -->
    @if($selectedWorkplan)
    <x-modal title="Review Workplan - {{ $selectedWorkplan->user->name }} {{ $selectedWorkplan->user->surname }}" 
        wire:model="reviewModal" 
        box-class="max-w-4xl">
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Employee</span>
                    </label>
                    <p class="text-sm">{{ $selectedWorkplan->user->name }} {{ $selectedWorkplan->user->surname }}</p>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Status</span>
                    </label>
                    <x-badge :value="$selectedWorkplan->status" 
                        :class="$selectedWorkplan->status == 'APPROVED' ? 'badge-success' : 'badge-warning'"/>
                </div>
            </div>
            
            <x-hr />
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Programme</span>
                    </label>
                    <p class="text-sm">{{ $selectedWorkplan->targetmatrix->target->indicator->departmentoutput->output->outcome->programme->title ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Outcome</span>
                    </label>
                    <p class="text-sm">{{ $selectedWorkplan->targetmatrix->target->indicator->departmentoutput->output->outcome->title ?? 'N/A' }}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Output</span>
                    </label>
                    <p class="text-sm">{{ $selectedWorkplan->targetmatrix->target->indicator->departmentoutput->output->title ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Indicator</span>
                    </label>
                    <p class="text-sm">{{ $selectedWorkplan->targetmatrix->target->indicator->title ?? 'N/A' }}</p>
                </div>
            </div>
            
            <x-hr />
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Quarter</span>
                    </label>
                    <p class="text-sm">{{ $selectedWorkplan->targetmatrix->month ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Month</span>
                    </label>
                    <p class="text-sm">{{ $selectedWorkplan->month }}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Target</span>
                    </label>
                    <p class="text-sm">{{ $selectedWorkplan->target }}</p>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Weightage</span>
                    </label>
                    <p class="text-sm">{{ $selectedWorkplan->weightage }}%</p>
                </div>
            </div>
            
            <x-hr />
            
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Output Description</span>
                </label>
                <p class="text-sm p-3 bg-base-200 rounded">{{ $selectedWorkplan->output }}</p>
            </div>
            
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Indicator Description</span>
                </label>
                <p class="text-sm p-3 bg-base-200 rounded">{{ $selectedWorkplan->indicator }}</p>
            </div>
            
            <x-hr />
            
            <div>
                <label class="label">
                    <span class="label-text font-semibold">Supervisor Remarks</span>
                </label>
                <x-textarea wire:model="remarks" 
                    placeholder="Enter your remarks or comments about this workplan..." 
                    rows="4" />
                @if($selectedWorkplan->remarks)
                    <div class="mt-2">
                        <p class="text-xs text-gray-500">Previous remarks:</p>
                        <p class="text-sm p-3 bg-base-200 rounded">{{ $selectedWorkplan->remarks }}</p>
                    </div>
                @endif
            </div>
        </div>
        
        <x-slot:actions>
            <x-button label="Close" 
                wire:click="$wire.closeReviewModal()" 
                class="btn-outline" />
            @if($selectedWorkplan->status !== 'APPROVED')
            <x-button icon="o-check-circle" 
                label="Approve Workplan" 
                wire:click="approveworkplan({{ $selectedWorkplan->id }})"
                wire:confirm="Are you sure you want to approve this workplan?"
                class="btn-success"
                spinner="approveworkplan" />
            @endif
        </x-slot:actions>
    </x-modal>
    @endif
</div>
