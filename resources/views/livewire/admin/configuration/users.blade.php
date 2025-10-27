<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
      <x-card title="Users" class="mt-5 border-2 border-gray-200" separator>
          <x-slot:menu>
              <div class="flex items-center gap-4">
                  <x-input icon="o-magnifying-glass" wire:model.live="search" placeholder="Search users..." class="max-w-sm" />
                  <x-button class="btn-outline" label="New User" icon="o-plus" @click="$wire.addUser"  responsive/>
              </div>
          </x-slot:menu>
          <x-table :headers="$headers" :rows="$users" pagination>
              @scope('actions', $user)
              <div class="flex space-x-2">
                  <x-button icon="o-eye" wire:click="edit({{ $user->user_id }})"  class="text-blue-500 btn-ghost btn-sm" />
                        </div>
              @endscope
              @scope('cell_roles', $user)
                  {{ $user?->roles->pluck('name')->join(', ') }}
              @endscope
              <x-slot:empty>
                  <x-alert class="alert-error" title="No users found." />
              </x-slot:empty>
          </x-table> 
      </x-card>
      <x-modal wire:model="modal" title="{{ $id ? 'Edit User' : 'New User' }}" separator box-class="max-w-3xl">
         @if($error)
            <x-alert class="alert-error" title="{{ $error }}" />
         @endif
          <x-form wire:submit="save">
              <div class="grid grid-cols-2 gap-2">
                  <x-input label="Name" wire:model="name" />
                  <x-input label="Email" wire:model="email" />
              </div>
              <div class="grid grid-cols-2 gap-2">
                  <x-select label="Gender" wire:model="gender"  placeholder="Select a gender" :options="[['id' => 'M', 'name' => 'Male'], ['id' => 'F', 'name' => 'Female']]" />
       
                  <x-select label="Status" wire:model="status"  placeholder="Select a status" :options="[['id' => 'Active', 'name' => 'Active'], ['id' => 'Blocked', 'name' => 'Blocked']]" />
              </div>
          
              <div class="grid grid-cols-1 gap-2">
                <x-card class="bg-gray-50">
                    <div class="text-lg font-bold">Account types</div>
                    @forelse($accounttypes as $accounttype)
                    <x-collapse class="mt-5 border-2 border-gray-200">
                        <x-slot:heading>
                            {{ $accounttype->name }}
                        </x-slot:heading>
                        <x-slot:content>
                            <x-table :headers="$defaultheaders" :rows="$accounttype->roles">
                                @scope('actions', $role)
                                <div class="flex space-x-2">
                                    @if(in_array($role->id, $this->selectedroles))
                                           <x-button icon="o-trash" wire:click="deleterole({{ $role->id }})" wire:confirm="Are you sure?" spinner class="text-red-500 btn-outline btn-sm" />
                                         @else
                                    <x-button icon="o-plus" wire:click="addrole({{ $role->id }})" spinner class="text-blue-500 btn-outline btn-sm" />
                                    @endif
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
              </div>
    
                       
              <x-slot:actions>
                <x-button label="Delete User" wire:click="delete({{ $id }})" wire:confirm="Are you sure?" class="btn-error" spinner />
                  <x-button label="Cancel" @click="$wire.modal = false" />
                  <x-button label="Save" type="submit" class="btn-primary" spinner="save" />
              </x-slot:actions>
          </x-form>
      </x-modal>
  </div>
  