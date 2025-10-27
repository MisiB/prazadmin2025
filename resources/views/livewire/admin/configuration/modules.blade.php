<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
     <x-card title="System modules" subtitle="Grouped by Account Type" separator class="mt-5 border-2 border-gray-200">
     @forelse($accounttypes as $accounttype)
     <x-collapse class="mt-5 border-2 border-gray-200">
        <x-slot:heading>
            {{ $accounttype->name }}
        </x-slot:heading>
        <x-slot:content>
            <x-button class="btn-outline" label="New Module" icon="o-plus" @click="$wire.add({{ $accounttype->id }})" />
     
             <x-table :headers="$headers" :rows="$accounttype->modules">
                 @scope('cell_icon', $module)
                     <x-icon name="{{ $module->icon }}" class="w-5 h-5" />
                 @endscope
                 @scope('actions', $module)
                 <div class="flex space-x-2">
                     <x-button icon="o-pencil" wire:click="edit({{ $module->id }})" class="text-blue-500 btn-ghost btn-sm" />
                     <x-button icon="o-trash" wire:click="delete({{ $module->id }})" wire:confirm="Are you sure?" spinner class="text-red-500 btn-ghost btn-sm" />
                     <x-button icon="o-link"  link="{{ route('admin.configuration.submodules', $module->id) }}" class="text-green-500 btn-ghost btn-sm" />
                 </div>
                 @endscope
                 <x-slot:empty>
                     <x-alert class="alert-error" title="No modules found." />
                 </x-slot:empty>
             </x-table>
         </x-slot:content>
     </x-collapse>
     @empty
         <x-alert class="alert-error" title="No account types found." />
     @endforelse
     </x-card>
 
     <x-modal wire:model="modal" title="{{ $id ? 'Edit Module' : 'New Module' }}" separator>
        @if($this->error)
            <x-alert class="alert-error" title="{{ $this->error }}" />
        @endif
         <x-form wire:submit="save">
             <div class="grid gap-2">
                 <x-input label="Name" wire:model="name" />
                 <x-input label="Icon" wire:model="icon" />
                 <x-input label="Default Permission" wire:model="default_permission" />
             </div>
             <x-slot:actions>
                 <x-button label="Cancel" @click="$wire.modal = false" />
                 <x-button label="Save" type="submit" class="btn-primary" spinner="save" />
             </x-slot:actions>
         </x-form>
     </x-modal>
 </div>
 