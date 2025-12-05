<div>
    <x-nav sticky class="rounded-md border shadow-sm">
        <x-slot:brand>
            
            {{-- Brand --}}
            <div>   <img src="/img/logo.jpg" class="lg:h-15 lg:w-24"/></div>
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
          
            <x-button  icon="o-bell"  class="btn-ghost btn-sm indicator" responsive>
                <span class="indicator-item badge badge-xs badge-error">5</span>
            </x-button>
            <x-dropdown >
                <x-slot:trigger>
                    <x-button class="btn-ghost btn-sm" label="Action" icon="o-cog-6-tooth" />
                </x-slot:trigger>
                <x-menu-item title="Customers"   
                  icon="o-user-group" 
                link="{{ route('admin.customers.showlist') }}"/>             
                <x-menu-separator />
                <x-menu-item title="New Ticket"   
                  icon="o-ticket" 
                link="{{ route('admin.myissues') }}"/>             
                <x-menu-separator />
             
            </x-dropdown>
            <x-dropdown>
                <x-slot:trigger>
                    <x-button class="btn-ghost btn-sm" icon="o-user-circle" label="Profile" />
                </x-slot:trigger>
                <x-menu-item title="Profile Settings" icon="o-cog-6-tooth" link="{{ route('profile.settings') }}" />
                       <x-menu-item 
                    title="Sign Out" 
                    icon="o-arrow-right-on-rectangle" 
                    link="{{ route('logout') }}"
                    class="text-red-500 hover:text-red-600" />
            </x-dropdown>

        </x-slot:actions>
        
    </x-nav>   
</div>
