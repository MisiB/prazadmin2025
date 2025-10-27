<div>
    <div class="p-2 mt-2 bg-gray-50 rounded-md border-gray-200">
        <div class="flex justify-start items-center">
            <x-button class="rounded-none btn-sm border-r-gray-200 border-r-1 btn-ghost" label="Home" link="{{ route('admin.home') }}" />
            <x-button class="text-white bg-black rounded-none btn-sm" label="Profile Settings" />
        </div>
    </div>

    <x-card title="Profile Settings" subtitle="Manage your profile settings" class="mt-2 border-gray-200" separator>
        <div class="grid grid-cols-4 gap-4">
            <x-menu class="w-64 border border-dashed">
                <x-menu-item title="Manage Profile" wire:click="showProfile" spinner />
                <x-menu-item title="Manage Password" wire:click="showPassword" spinner />
                <x-menu-item title="Manage Approval Code" wire:click="showApprovalCode" spinner />
            </x-menu>
            
                <div class="col-span-3">
                @if ($profile)
                    <x-card title="Profile" subtitle="Manage your profile settings" class="mt-2 border-2 border-gray-200" separator>
                       <x-form wire:submit.prevent="updateProfile">
                        <x-input wire:model="name" label="Name" />
                        <x-input wire:model="email" label="Email" />
                        <x-input wire:model="phonenumber" label="Phone Number" />
                        <x-input wire:model="country" label="Country" />
                        <x-button class="btn-primary" label="Update Profile" type="submit" spinner="updateProfile" />
                       </x-form> 
                    </x-card>
                @endif
                @if ($pword)
                  
                    <x-card title="Password" subtitle="Manage your password settings" class="mt-2 border-2 border-gray-200" separator>
                       <x-form wire:submit.prevent="updatePassword">
                        <x-input wire:model="current_password" label="Current Password" type="password" />
                        <x-input wire:model="password" label="New Password" type="password" />
                        <x-input wire:model="password_confirmation" label="Confirm Password" type="password" />
                        <x-button class="btn-primary" label="Update Password" type="submit" spinner="updatePassword" />
                       </x-form> 
                    </x-card>
                    
                @endif
                @if ($showcode)
                    <x-card title="Approval Code" subtitle="Manage your approval code settings" class="mt-2 border-2 border-gray-200" separator>
                       <x-form wire:submit.prevent="updateApprovalCode">
                        <x-pin wire:model="approvalcode" size="6" hide label="Approval Code" />
                        <x-button class="btn-primary" label="Update Approval Code" type="submit" spinner="updateApprovalCode" />
                       </x-form> 
                    </x-card>
                @endif
                </div>
            

        </div>
    </x-card>

</div>
