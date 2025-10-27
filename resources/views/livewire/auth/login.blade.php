<div class="mx-auto">

    <div class="min-h-screen hero">
        <div class="flex-col hero-content lg:flex-row-reverse">

            <div class="object-center w-full max-w-sm shadow-2xl card bg-base-100 shrink-0">
                <figure>
                    <x-icon name="o-shield-exclamation" class="w-36 h-36" />
                </figure>

                <div class="card-body">
                    <h3 class="text-5xl font-bold text-center">Welcome</h3>
                    <p class="py-6 text-center">
                        Please click on the button below  to signin, this system will use your Office 365  account for authentication.
                    </p>
                    <div class="mt-2 form-control">
                        <x-button class="text-white w-full btn btn-success" link="{{route('connect')}}" no-wire-navigate>Login</x-button>
                        <x-button class="btn-ghost w-full mt-2 btn-sm" wire:click="loginmodal = true">Manual Login</x-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-modal title="Manual Login" wire:model="loginmodal">
       @if($error)
      <x-alert title="invalid login details" class="alert-error" icon="o-exclamation-triangle" />
       @endif 
        <x-form wire:submit="login">
            <div class="grid gap-3 mt-3">
                <x-input wire:model="email" placeholder="Email" type="email" />
                <x-input wire:model="password" placeholder="Password" type="password" />
                <x-button class="w-full btn-success" label="Login"  type="submit"/>
            </div>

        </x-form>
    </x-modal>
</div>