<div>
    <x-slot:sidebar drawer="main-drawer" collapsible class="p-2 text-gray-500 border border-r-gray-200">
    
    
    {{-- MENU --}}
    <x-menu activate-by-route active-class="bg-blue-300">
    
    
        <x-menu-item title="Dashboard" icon="o-home" link="{{ route('admin.home') }}" />
        <x-menu-separator />
        @forelse ($modules as $module)
                @if(in_array($module->default_permission, $permissions))
                <x-menu-sub title="{{ $module->name }}" icon="{{ $module->icon }}" class="text-gray-500">
                    @forelse ($module->submodules as $submodule)
                        @if(in_array($submodule->default_permission, $permissions))
                            <x-menu-item title="{{ $submodule->name }}" icon="{{ $submodule->icon }}" link="{{route($submodule->url)}}" class="text-gray-500" />
                        @endif
                    @empty
                    @endforelse
                </x-menu-sub>
                <x-menu-separator />
                @endif
             
    
        @empty
            <x-menu-item title="No Modules" />
        @endforelse
    
    </x-menu>
    </x-slot:sidebar>
    </div>
    