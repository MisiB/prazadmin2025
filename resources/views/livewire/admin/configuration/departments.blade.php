<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"/>
    <x-card title="Departments"  separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-button icon="o-plus" label="New Department" wire:click="modal = true" class="btn-primary"/>
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$departments">
            @scope('cell_users', $department)
             
<x-button  icon="o-user" class="btn-circle btn-ghost indicator" wire:click="getusers({{ $department->id }})">
    
    <x-badge value="{{ $department->users->count() }}" class="badge-secondary badge-sm indicator-item" />
</x-button>
            @endscope
            @scope('actions', $department)
            <div class="flex space-x-2">
                <x-button icon="o-pencil" wire:click="edit({{ $department->id }})" spinner class="text-blue-500 btn-outline btn-sm" />
                <x-button icon="o-trash" wire:click="delete({{ $department->id }})" wire:confirm="Are you sure?" spinner class="text-red-500 btn-outline btn-sm" />
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No departments found." />
            </x-slot:empty>
        </x-table>
    </x-card>
    <x-modal title="{{ $id ? 'Edit' : 'New' }} Department" wire:model="modal">
        <x-form wire:submit="save">
            <div class="grid gap-2">
                <x-input label="Name" wire:model="name"/>
                <x-select label="Level" wire:model="level" placeholder="Select a level" :options="[['id' => 'L1', 'name' => 'Level 1'], ['id' => 'L2', 'name' => 'Level 2'], ['id' => 'L3', 'name' => 'Level 3'], ['id' => 'L4', 'name' => 'Level 4'], ['id' => 'L5', 'name' => 'Level 5']]"/>
            </div>
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.modal = false"/>
                <x-button label="{{ $id ? 'Update' : 'Save' }}" type="submit" class="btn-primary" spinner="save"/>
            </x-slot:actions>
        </x-form>
    </x-modal>
    <x-modal title="Department users" wire:model="usermodal" box-class="max-w-3xl">
        <x-button class="btn-primary" wire:click="addusermodal = true">Assign User</x-button>
        <table class="table table-zebra tabe-sm">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Position</th>
                    <th>Is HOD</th>
                    <th>Report To</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if ($departmentusers->count() > 0)
                @forelse ($departmentusers as $user)
                    <tr>
                        <td>{{ $user->user->name }} {{ $user->user->surname }}</td>
                        <td>{{ $user->position }}</td>
                        <td>
                            <x-badge value="{{ $user->isprimary ? 'Yes' : 'No' }}" class="{{ $user->isprimary ? 'badge-success' : 'badge-error' }}badge-sm"/>
                        </td>
                        <td>{{ $user?->supervisor?->name }}{{ $user?->supervisor?->surname }}</td>
                        <td class="flex space-x-2">
                            <x-button icon="o-pencil" wire:click="edituser({{ $user->id }})" spinner class="text-blue-500 btn-ghost btn-sm" />
                            <x-button icon="o-trash" wire:click="deleteuser({{ $user->id }})" wire:confirm="Are you sure?" spinner class="text-red-500 btn-ghost btn-sm" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-red-600">No users found.</td>
                    </tr>
                @endforelse
                @else
                    <tr>
                        <td colspan="5" class="text-center text-red-600">No department selected.</td>
                    </tr>
                @endif
            </tbody>
        </table>    
    </x-modal>
    <x-modal title="{{ $departmentuser_id ? 'Edit' : 'New' }} User" wire:model="addusermodal" box-class="max-w-3xl">
        <x-form wire:submit="saveuser">
            <div class="grid gap-2">
                <x-select label="User" wire:model.live="user_id" placeholder="Select a user" option-value="id" option-label="email" :options="$users"/>
                <x-input label="Position" wire:model="position"/>
                <x-select label="Is HOD" wire:model="isprimary" placeholder="Select" option-value="id" option-label="name" :options="[['id' => 1, 'name' => 'Yes'], ['id' => 0, 'name' => 'No']]"/>
                <x-select label="Report To" wire:model="reportto" placeholder="Select a supervisor" option-value="id" option-label="email" :options="$users"/>
            </div>
            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.addusermodal = false"/>
                <x-button label="{{ $departmentuser_id ? 'Update' : 'Save' }}" class="btn-primary"  type="submit" spinner="saveuser"/>
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
