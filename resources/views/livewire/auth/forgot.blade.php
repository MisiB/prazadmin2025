<div class="mx-auto w-full md:w-2/3">
    
    <div class="min-h-screen">
        <div class="flex-col lg:p-12 lg:px-60 lg:pt-36 lg:flex-row-reverse">
            <div class="flex justify-center mb-4">
                <img src="{{ $accountsetting == null ? '/img/logo.jpg' : $accountsetting->logo }}" class="w-20 h-20"/>
              
            </div>

            <h1 class="text-2xl font-bold text-center">Forgot Password</h1>
            <p class="p-4 text-center text-gray-500">Enter your email to receive a password reset link.</p>
        <div class="p-6 rounded-md border-gray-200 border-1">
        @if($error)
            <x-alert icon="o-exclamation-triangle" class="mb-4 alert-error" title="{{ $error }}" />
        @endif

        <x-form wire:submit="forgot" class="space-y-2">
            <x-input 
                label="Email Address"
                wire:model="email"
                type="email"
                icon="o-envelope"
                placeholder="Enter your email"
            />

           


            <x-button 
                label="Send Password Reset Link" 
                class="w-full text-white bg-black" 
                type="submit"
                spinner="forgot"
            />
        </x-form>
        <div class="mt-4 text-center">
            Or, return to <a href="{{ route('login') }}" class="text-primary hover:text-primary-600">Login</a>
        </div>
        </div>
    </div>
</div>
<x-modal title="Password Reset" wire:model="modal">
    @if($status == "success")
    <div class="grid gap-2">
        <div class="p-3 bg-green-300 rounded-md">
            <x-icon name="o-check-circle" class="text-green-500" />
            {{ $message }}
        </div>
    </div>
    <div class="grid gap-2 mt-2">
        <x-button label="Close" link="{{ route('login') }}" class="w-full text-white bg-black" />
    </div>
    @else
    <div class="p-3 bg-green-300 rounded-md">
        <x-icon name="o-exclamation-triangle" class="text-red-500" />
        {{ $message }}
    </div>
    <div class="grid gap-2 mt-2">
        <x-button label="Try again" @click="$wire.modal = false" class="w-full text-white bg-black" />
    </div>
    @endif
</x-modal>


