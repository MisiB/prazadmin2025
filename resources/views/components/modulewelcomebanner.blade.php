@props(['breadcrumbs'])
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">

    <div class="p-6 bg-gradient-to-r from-blue-500 to-indigo-600 border-b border-gray-200 flex justify-between items-center">

        <div>
            <x-breadcrumbs :items="$breadcrumbs" 
                class="bg-white p-2 mb-4 pl-10 -ml-8 rounded-box overflow-x-auto whitespace-nowrap"
                link-item-class="text-base" 
            />
            <h1 class="text-2xl font-bold text-white"> 
                @php
                    $hour = date('H');
                    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
                @endphp
                {{ $greeting }}, {{ auth()->user()->name ?? 'Admin' }}!
            </h1>
            <p class="text-white opacity-80 mt-1">Here's an overview of your {{ $breadcrumbs[1]['label'] }}.</p>
            <p class="text-white opacity-80 mt-1">{{ now()->format('l, F j, Y') }}</p>
        </div>
    </div>

</div>
