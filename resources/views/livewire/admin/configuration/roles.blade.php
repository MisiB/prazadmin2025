<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    <x-card title="Roles by Account Type" separator class="mt-5 border-2 border-gray-200">
    @forelse($accounttypes as $accounttype)
    <x-collapse class="mt-5 border-2 border-gray-200">
        <x-slot:heading>
            {{ $accounttype->name }}
        </x-slot:heading>
        <x-slot:content>
            <x-button class="btn-outline" label="New Role" icon="o-plus" @click="$wire.add({{ $accounttype->id }})" />
                <x-table :headers="$headers" :rows="$accounttype->roles">
                    @scope('actions', $role)
                    <div class="flex space-x-2">
                        <x-button icon="o-pencil" wire:click="edit({{ $role->id }})" spinner class="text-blue-500 btn-outline btn-sm" />
                        <x-button icon="o-trash" wire:click="delete({{ $role->id }})" wire:confirm="Are you sure?" spinner class="text-red-500 btn-outline btn-sm" />
                        <x-button icon="o-lock-closed" wire:click="getpermissions({{ $role->id }})" spinner class="text-green-500 btn-outline btn-sm" />
                    </div>
                    @endscope
                    <x-slot:empty>
                        <x-alert class="alert-error" title="No roles found." />
                    </x-slot:empty>
                </x-table>
        </x-slot:content>
    </x-collapse>
    
      
    @empty
        <x-alert class="alert-error" title="No account types found." />
    @endforelse
</x-card>

    <x-modal wire:model="modal" title="{{ $id ? 'Edit Role' : 'New Role' }}" separator>
        <x-form wire:submit="save">
            <div class="grid gap-2">
                <x-input label="Name" wire:model="name" />
            </div>
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.modal = false" />
                <x-button label="Save" class="btn-primary"  type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-drawer wire:model="permissionmodal" title="Permissions" class="w-11/12 lg:w-2/3" right>
        @forelse($modules as $module)
        <x-collapse class="mt-5 border-2 border-gray-200">
            <x-slot:heading>
                {{ $module->name }}
            </x-slot:heading>
            <x-slot:content>
            @if(count($this->selectedpermissions)>0)
            <x-alert title="Selected Permissions" description="You have selected {{ count($this->selectedpermissions) }} permissions. please save by clicking the save button." icon="o-envelope" class="alert-info">
                <x-slot:actions>
                    <x-button label="Save" wire:click="savepermissions" spinner="savepermissions" />
                </x-slot:actions>
            </x-alert>
            @endif

            <x-table :headers="$submodulesheaders" :rows="$module->submodules" wire:model="expanded" expandable>
 
                {{-- Special `expansion` slot --}}
                @scope('expansion', $user)

                 @forelse($user->permissions as $permission)
                    <div class="flex justify-between pt-2">
                       <div> {{ $permission->name }}</div>
                       <div> 
                             @if(in_array($permission->id, $this->selectedpermissions))
                        <x-button icon="o-trash" wire:click="removepermission({{ $permission->id }})" spinner class="text-red-500 btn-outline btn-sm" /> 
                            @else
                                <x-button icon="o-plus" wire:click="addpermission({{ $permission->id }})" spinner class="text-green-500 btn-outline btn-sm" />
                            @endif
                        </div>
                    </div>
                @empty
                    <x-alert class="alert-error" title="No permissions found." />
                @endforelse
                @endscope
             </x-table>
          
            </x-slot:content>
         </x-collapse>
        @empty
            <x-alert class="alert-error" title="No modules found." />
        @endforelse
    </x-drawer>
</div>
