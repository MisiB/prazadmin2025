<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />

    <x-card title="Strategies" class="mt-5 border-2 border-gray-200" separator>
        <x-slot:menu>
            <x-button class="btn-outline" label="New Strategy" icon="o-plus" @click="$wire.openModal"  responsive/>
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$strategies" class="table-zebra table-compact"  pagination>
            @scope('actions', $strategy)
            <div class="flex justify-end space-x-2">
               <x-button icon="o-eye" label="View" link="{{ route('admin.management.strategydetail', ['uuid' => $strategy->uuid]) }}" class="btn-success  btn-sm" />
               <x-button icon="o-pencil" label="Edit" wire:click="getstrategy({{ $strategy->id }})" class="btn-info  btn-sm" />
               <x-button icon="o-trash" label="Delete" wire:click="delete({{ $strategy->id }})" wire:confirm="Are you sure?" spinner class="btn-error btn-sm" />
            </div>
            @endscope
            @scope('cell_created_by', $strategy)
                {{ $strategy?->creator?->name }} {{ $strategy?->creator?->surname }}
            @endscope
            @scope('cell_name', $strategy)
              {{ $strategy?->name }}
            @endscope
         
          
            @scope('cell_status', $strategy)
              <x-badge :value="$strategy->status" :class="$strategy->status == 'Draft' ? 'badge-warning' : ($strategy->status == 'Approved' ? 'badge-success' : 'badge-error')"/>
            @endscope
         
            <x-slot:empty>
                <x-alert class="alert-error" title="No strategies found." />
            </x-slot:empty>
        </x-table>
    </x-card>
    <x-modal title="{{ $id ? 'Edit' : 'New' }}Strategy" wire:model="modal">
       <x-form wire:submit="save">
           <div class="grid gap-2">
               <x-input wire:model="name" label="Name" placeholder="Enter name" />
               <x-input wire:model="startyear" label="Start Year" placeholder="Enter start year" />
               <x-input wire:model="endyear" label="End Year" placeholder="Enter end year" />
           </div>
           <x-slot:actions>
               <x-button label="Cancel" @click="$wire.closeModal()" />
               <x-button label="Save" type="submit" class="btn-primary" spinner="save" />
           </x-slot:actions>
       </x-form>
    </x-modal>
</div>
