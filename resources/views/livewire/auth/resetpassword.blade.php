<div class="mx-auto w-full md:w-2/3">
    
    <div class="min-h-screen">
        <div class="flex-col lg:p-12 lg:px-60 lg:pt-36 lg:flex-row-reverse">
            <div class="flex justify-center mb-4">
                <img src="{{ $accountsetting == null ? '/img/logo.jpg' : $accountsetting->logo }}" class="w-20 h-20"/>
              
            </div>

            <h1 class="text-2xl font-bold text-center">Reset Password</h1>
            <p class="p-4 text-center text-gray-500">Enter your email to receive a password reset link.</p>
        <div class="p-6 rounded-md border-gray-200 border-1">
        @if($error)
            <x-alert icon="o-exclamation-triangle" class="mb-4 alert-error" title="{{ $error }}" />
        @endif

        <x-form wire:submit="PasswordReset" class="space-y-2">
            <x-input 
                label="Email Address"
                wire:model="email"
                type="email"
                icon="o-envelope"
                placeholder="Enter your email"
            />

            <x-input 
                label="New Password"
                wire:model="password"
                type="password"
                icon="o-lock-closed"
                placeholder="Enter your password"
            />
            <x-input 
            label="Confirm Password"
            wire:model="password_confirmation"
            type="password"
            icon="o-lock-closed"
            placeholder="Confirm your password"
        />

          

            <x-button 
                label="Reset Password" 
                class="w-full text-white bg-black" 
                type="submit"
                spinner="PasswordReset"
            />
        </x-form>
        </div>
    </div>
</div>

</div>


