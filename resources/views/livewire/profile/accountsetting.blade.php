<div>
    <div class="p-3 mt-2 bg-white rounded-md border-2 border-gray-200">
        <div class="flex justify-start items-center">
            <x-button class="rounded-none border-r-gray-200 border-r-1 btn-ghost" label="Home" link="{{ route('admin.home') }}" />
            <x-button class="rounded-none btn-ghost" label="Account Settings" />
        </div>
    </div>

    <x-card title="Account Settings" subtitle="Manage your account settings" class="mt-2 border-2 border-gray-200" separator>
     <x-form  wire:submit.prevent="save">
        <div class="grid grid-cols-2 gap-4">
            <x-input wire:model="name" label="Organisation Name" />
            <x-input wire:model="email" label="General Emails" />
        </div>
        <div class="grid grid-cols-2 gap-4">
            <x-input wire:model="phone" label="GeneralPhonenumbers" />
            <x-input wire:model="address" label="Organisation Address" />
        </div>
        <div class="grid gap-4">
            <x-file label="Organisation Logo" wire:model="logo" accept="image/png, image/jpeg"> 
                <img src="{{ $logo ?? '/empty-user.jpg' }}" class="h-40 rounded-lg" />
            </x-file>
        </div>
        <x-slot:actions>
                 <x-button label="Save" class="btn-primary" type="submit" spinner="save" />
        </x-slot:actions>
     </x-form>
    </x-card>
</div>
