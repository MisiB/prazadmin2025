<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box"
        link-item-class="text-base" />
    <x-card title="Staff Welfare Loans" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." />
            @can('swl.create')
                <x-button icon="o-plus" class="btn-primary" label="New Loan" @click="$wire.modal=true" />
            @endcan
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$loans" class="table-zebra table-xs">
            @scope('cell_loan_number', $loan)
                <div>{{ $loan->loan_number }}</div>
            @endscope
            @scope('cell_full_name', $loan)
                <div>{{ $loan->full_name }}</div>
            @endscope
            @scope('cell_loan_amount_requested', $loan)
                <div>${{ number_format($loan->loan_amount_requested, 2) }}</div>
            @endscope
            @scope('cell_loan_purpose', $loan)
                <div class="max-w-xs truncate" title="{{ $loan->loan_purpose }}">{{ $loan->loan_purpose }}</div>
            @endscope
            @scope('cell_repayment_period_months', $loan)
                <div>{{ $loan->repayment_period_months }} months</div>
            @endscope
            @scope('cell_status', $loan)
                @php
                    $statusColor = match ($loan->status) {
                        'DRAFT' => 'badge-warning',
                        'SUBMITTED' => 'badge-info',
                        'HR_REVIEW' => 'badge-info',
                        'FINANCE_REVIEW' => 'badge-info',
                        'CEO_APPROVAL' => 'badge-info',
                        'APPROVED' => 'badge-success',
                        'PAYMENT_PROCESSED' => 'badge-success',
                        'AWAITING_ACKNOWLEDGEMENT' => 'badge-warning',
                        'COMPLETED' => 'badge-success',
                        'REJECTED' => 'badge-error',
                        default => 'badge-ghost',
                    };
                @endphp
                <x-badge :value="$loan->status" class="{{ $statusColor }}" />
            @endscope
            @scope('cell_action', $loan)
                <div class="flex items-center space-x-2">
                    <x-button icon="o-eye" class="btn-xs btn-success btn-outline"
                        link="{{ route('admin.workflows.staff-welfare-loan', $loan->uuid) }}" />
                    @if ($loan->status == 'DRAFT')
                        @can('swl.edit.draft')
                            <x-button icon="o-pencil" class="btn-xs btn-info btn-outline" wire:click="edit({{ $loan->id }})"
                                spinner />
                        @endcan
                        @can('swl.submit')
                            <x-button icon="o-paper-airplane" class="btn-xs btn-primary btn-outline"
                                wire:click="submit({{ $loan->id }})" wire:confirm="Are you sure you want to submit this loan?"
                                spinner />
                        @endcan
                        @can('swl.edit.draft')
                            <x-button icon="o-trash" class="btn-xs btn-outline btn-error" wire:click="delete({{ $loan->id }})"
                                wire:confirm="Are you sure?" spinner />
                        @endcan
                    @endif
                </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No Staff Welfare Loans found." />
            </x-slot:empty>
        </x-table>

        <div class="mt-4">
            {{ $loans->links() }}
        </div>
    </x-card>

    <x-modal title="{{ $id ? 'Edit Staff Welfare Loan' : 'New Staff Welfare Loan' }}" wire:model="modal"
        box-class="max-w-4xl" separator>
        <x-form wire:submit="save">
            <div class="grid grid-cols-2 gap-4">
                <x-input wire:model="employee_number" label="Employee Number" />
                <x-input wire:model="job_title" label="Job Title" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-input wire:model="date_joined" type="date" label="Date Joined" />
                <x-input wire:model="loan_amount_requested" type="number" step="0.01" label="Loan Amount Requested"
                    prefix="$" 
                    :min="$min_loan_amount ?? 0"
                    :max="$max_loan_amount ?? 999999999"
                    :hint="'Min: $' . number_format($min_loan_amount ?? 0, 2) . ($max_loan_amount ? ' | Max: $' . number_format($max_loan_amount, 2) : '')" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-input wire:model="repayment_period_months" type="number" label="Repayment Period (Months)" 
                    min="1" 
                    :max="$max_repayment_months ?? 120"
                    :hint="'Maximum: ' . ($max_repayment_months ?? 120) . ' months'" />
                <x-textarea wire:model="loan_purpose" label="Loan Purpose" rows="3" />
            </div>
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.modal = false" />
                <x-button class="btn-primary" label="Save" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
