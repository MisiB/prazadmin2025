<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
  
    <x-card title="{{ $this->module->name }}" class="mt-5 border-2 border-gray-200" separator>
        <x-slot:menu>
            <x-button class="btn-outline" label="New Submodule" icon="o-plus" @click="$wire.submodal = true" />
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$module->submodules">
            @scope('actions', $submodule)
            <div class="flex space-x-2">
                <x-button icon="o-pencil" wire:click="edit({{ $submodule->id }})" class="text-blue-500 btn-ghost btn-sm" />
                <x-button icon="o-trash" wire:click="delete({{ $submodule->id }})" wire:confirm="Are you sure?" spinner class="text-red-500 btn-ghost btn-sm" />
            <x-button icon="o-lock-closed" wire:click="getsubmodule({{ $submodule->id }})" spinner class="text-green-500 btn-ghost btn-sm" />
      
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No submodules found." />
            </x-slot:empty>
        </x-table>
    </x-card>
    <x-modal wire:model="submodal" title="{{ $subid ? 'Edit Submodule' : 'New Submodule' }}" separator>
        @if($error)
            <x-alert class="alert-error" title="{{ $error }}" />
        @endif
        <x-form wire:submit="savesubmodule">
            <div class="grid grid-cols-2 gap-2">
                <x-input label="Name" wire:model="subname" />
                <x-input label="Icon" wire:model="subicon" />
            </div>
            <div class="grid grid-cols-2 gap-2">
                <x-input Label="Url" wire:model="suburl" />
                <x-input Label="Default Permission" wire:model="subdefault_permission" />
            </div>
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.submodal = false" />
                <x-button label="Save" class="btn-primary" type="submit" spinner="savesubmodule" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal wire:model="permissionmodal" title="Permissions">
        <x-input label="Permission name" wire:model="permission">
            <x-slot:append>
                {{-- Add `rounded-s-none` class (RTL support) --}}
                <x-button label="Submit" icon="o-check" class="btn-primary rounded-s-none" wire:click="savepermission"  spinner="savepermission" />
            </x-slot:append>
        </x-input>
        @if($error)
        <x-alert class="alert-error" title="{{ $error }}" />
    @endif
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Name</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
             
                @forelse ($permissions as $permission)
                    <tr>
                        <td>{{ $permission->name }}</td>
                        <td class="flex justify-end space-x-2">
                          <x-button icon="o-pencil" wire:click="editpermission({{ $permission->id }})" class="text-blue-500 btn-ghost btn-sm"  spinner="editpermission" />  
                          <x-button icon="o-trash" wire:click="deletepermission({{ $permission->id }})" wire:confirm="Are you sure?" spinner="deletepermission" class="text-red-500 btn-ghost btn-sm" />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center text-red-600">No permissions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-modal>
</div>
