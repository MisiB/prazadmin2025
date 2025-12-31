<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box"
        link-item-class="text-sm font-bold" />

    <x-card title="T&S Allowance Configuration" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." />
            @can('tsa.allowance.config.create')
                <x-button label="New Configuration" responsive icon="o-plus" class="btn-primary" @click="$wire.modal = true" />
            @endcan
        </x-slot:menu>

        <x-table :headers="$headers" :rows="$configs" class="table-zebra table-xs">
            @scope('cell_grade_band', $config)
                <div class="font-semibold">{{ $config->gradeBand->code }}</div>
                <div class="text-xs text-gray-500">{{ $config->gradeBand->description }}</div>
            @endscope

            @scope('cell_currency', $config)
                <div>{{ $config->currency->name }}</div>
            @endscope

            @scope('cell_subsistence', $config)
                <div>${{ number_format($config->out_of_station_subsistence_rate, 2) }}</div>
            @endscope

            @scope('cell_overnight', $config)
                <div>${{ number_format($config->overnight_allowance_rate, 2) }}</div>
            @endscope

            @scope('cell_effective_from', $config)
                <div>{{ $config->effective_from?->format('d M Y') }}</div>
            @endscope

            @scope('cell_status', $config)
                @php
                    $statusColor = match ($config->status) {
                        'DRAFT' => 'badge-warning',
                        'INACTIVE' => 'badge-warning',
                        'ACTIVE' => 'badge-success',
                        'ARCHIVED' => 'badge-ghost',
                        default => 'badge-ghost',
                    };
                @endphp
                <x-badge :value="$config->status" class="{{ $statusColor }}" />
            @endscope

            @scope('cell_action', $config)
                <div class="flex items-center space-x-2">
                    @if (in_array($config->status, ['DRAFT', 'INACTIVE']))
                        @can('tsa.allowance.config.update')
                            <x-button icon="o-pencil" class="btn-xs btn-info btn-outline"
                                wire:click="edit({{ $config->id }})" spinner tooltip="Edit" />
                        @endcan
                        @can('tsa.allowance.config.approve')
                            <x-button icon="o-check" class="btn-xs btn-success btn-outline"
                                wire:click="approve({{ $config->id }})"
                                wire:confirm="Are you sure you want to approve this configuration?"
                                spinner tooltip="Approve" />
                        @endcan
                        @can('tsa.allowance.config.deactivate')
                            <x-button icon="o-trash" class="btn-xs btn-outline btn-error"
                                wire:click="delete({{ $config->id }})"
                                wire:confirm="Are you sure you want to delete this configuration?"
                                spinner tooltip="Delete" />
                        @endcan
                    @elseif ($config->status == 'ACTIVE')
                        @can('tsa.allowance.config.view')
                            <x-button icon="o-eye" class="btn-xs btn-info btn-outline"
                                wire:click="edit({{ $config->id }})" spinner tooltip="View" />
                        @endcan
                        @can('tsa.allowance.config.deactivate')
                            <x-button icon="o-archive-box" class="btn-xs btn-warning btn-outline"
                                wire:click="archive({{ $config->id }})"
                                wire:confirm="Are you sure you want to archive this configuration?"
                                spinner tooltip="Archive" />
                        @endcan
                    @else
                        @can('tsa.allowance.config.view')
                            <x-button icon="o-eye" class="btn-xs btn-ghost btn-outline"
                                wire:click="edit({{ $config->id }})" spinner tooltip="View" />
                        @endcan
                    @endif
                </div>
            @endscope

            <x-slot:empty>
                <x-alert class="alert-info" title="No T&S allowance configurations found." />
            </x-slot:empty>
        </x-table>

        <div class="mt-4">
            {{ $configs->links() }}
        </div>
    </x-card>

    <x-modal wire:model="modal" title="{{ $id ? 'Edit T&S Allowance Configuration' : 'New T&S Allowance Configuration' }}"
        box-class="max-w-4xl" separator>
        <x-form wire:submit="save">
            <div class="space-y-4">
                {{-- Basic Information --}}
                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold mb-3">Basic Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <x-select wire:model="grade_band_id" label="Grade Band" :options="$gradeBands"
                            option-label="code" placeholder="Select Grade Band" :disabled="$id && !in_array($status, ['DRAFT', 'INACTIVE'])" />
                        <x-select wire:model="currency_id" label="Currency" :options="$currencies"
                            placeholder="Select Currency" :disabled="$id && !in_array($status, ['DRAFT', 'INACTIVE'])" />
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <x-input wire:model="effective_from" type="date" label="Effective From"
                            :disabled="$id && !in_array($status, ['DRAFT', 'INACTIVE'])" />
                        @if ($id)
                            <x-select wire:model="status" label="Status"
                                :options="[['id' => 'DRAFT', 'name' => 'Draft'], ['id' => 'ACTIVE', 'name' => 'Active'], ['id' => 'ARCHIVED', 'name' => 'Archived']]"
                                disabled />
                        @endif
                    </div>
                </div>

                {{-- Allowance Rates --}}
                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold mb-3">Allowance Rates</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <x-input wire:model="out_of_station_subsistence_rate" type="number" step="0.01"
                            label="Out of Station/Subsistence" prefix="$" :disabled="$id && !in_array($status, ['DRAFT', 'INACTIVE'])" />
                        <x-input wire:model="overnight_allowance_rate" type="number" step="0.01"
                            label="Overnight Allowance" prefix="$" :disabled="$id && !in_array($status, ['DRAFT', 'INACTIVE'])" />
                        <x-input wire:model="bed_allowance_rate" type="number" step="0.01"
                            label="Bed Allowance" prefix="$" :disabled="$id && !in_array($status, ['DRAFT', 'INACTIVE'])" />
                    </div>
                </div>

                {{-- Meal Rates --}}
                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold mb-3">Meal Rates</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <x-input wire:model="breakfast_rate" type="number" step="0.01"
                            label="Breakfast" prefix="$" :disabled="$id && !in_array($status, ['DRAFT', 'INACTIVE'])" />
                        <x-input wire:model="lunch_rate" type="number" step="0.01"
                            label="Lunch" prefix="$" :disabled="$id && !in_array($status, ['DRAFT', 'INACTIVE'])" />
                        <x-input wire:model="dinner_rate" type="number" step="0.01"
                            label="Dinner" prefix="$" :disabled="$id && !in_array($status, ['DRAFT', 'INACTIVE'])" />
                    </div>
                </div>

                {{-- Transport Rates --}}
                <div>
                    <h3 class="text-lg font-semibold mb-3">Transport Rates</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <x-input wire:model="fuel_rate" type="number" step="0.01"
                            label="Fuel" prefix="$" :disabled="$id && !in_array($status, ['DRAFT', 'INACTIVE'])" />
                        <x-input wire:model="toll_gate_rate" type="number" step="0.01"
                            label="Toll Gates" prefix="$" :disabled="$id && !in_array($status, ['DRAFT', 'INACTIVE'])" />
                        <x-input wire:model="mileage_rate_per_km" type="number" step="0.01"
                            label="Mileage Rate (per km)" prefix="$" :disabled="$id && !in_array($status, ['DRAFT', 'INACTIVE'])" />
                    </div>
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.modal = false" class="btn-outline" />
                @if (!$id || in_array($status, ['DRAFT', 'INACTIVE']))
                    <x-button label="{{ $id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="save" />
                @endif
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>

