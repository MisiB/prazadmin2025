<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box"
        link-item-class="text-sm font-bold" />

    <x-card title="Payment Voucher Configuration" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." />
            @can('payment.voucher.config.manage')
                <x-button label="New Configuration" responsive icon="o-plus" class="btn-primary" @click="$wire.openModal()" />
            @endcan
        </x-slot:menu>

        <x-table :headers="$headers" :rows="$configs" class="table-zebra table-xs">
            @scope('cell_config_key', $config)
                <div class="font-semibold">{{ $config->config_key }}</div>
            @endscope

            @scope('cell_config_value', $config)
                <div class="max-w-xs truncate" title="{{ $config->config_value }}">{{ $config->config_value }}</div>
            @endscope

            @scope('cell_description', $config)
                <div class="text-sm text-gray-600">{{ $config->description ?? 'N/A' }}</div>
            @endscope

            @scope('cell_updated_by', $config)
                <div>{{ $config->updatedBy->name ?? 'N/A' }}</div>
            @endscope

            @scope('cell_updated_at', $config)
                <div>{{ $config->updated_at?->format('Y-m-d H:i:s') }}</div>
            @endscope

            @scope('cell_action', $config)
                <div class="flex items-center space-x-2">
                    @can('payment.voucher.config.manage')
                        <x-button icon="o-pencil" class="btn-sm btn-info btn-outline" 
                            wire:click="openModal({{ $config->id }})" spinner />
                    @endcan
                </div>
            @endscope

            <x-slot:empty>
                <x-alert class="alert-info" title="No configurations found." />
            </x-slot:empty>
        </x-table>
    </x-card>

    <!-- Configuration Modal -->
    <x-modal wire:model="modal" title="{{ $id ? 'Edit Configuration' : 'New Configuration' }}" separator persistent>
        <x-form wire:submit="save">
            <x-input wire:model="config_key" label="Config Key" :readonly="!!$id" hint="Unique identifier for this configuration" />
            <x-textarea wire:model="config_value" label="Config Value" rows="3" hint="The value for this configuration" />
            <x-textarea wire:model="description" label="Description" rows="2" hint="Optional description of what this configuration does" />
            
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.closeModal()" />
                <x-button type="submit" label="{{ $id ? 'Update' : 'Save' }}" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
