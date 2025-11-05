<div>
    <div class="text-sm breadcrumbs">
        <ul class="flex">
            <li>
                <x-button label="Home" link="{{ route('admin.home') }}" class="rounded-none btn-ghost" icon="o-home"/>
            </li>
            <li>
                <x-button label="Workshops" link="{{ route('admin.workshop.index')  }}" class="rounded-none btn-ghost" />
            </li>
            <li><x-button class="border-l-2 rounded-none border-l-gray-200 btn-ghost"  label="{{ $workshop->Title }}"/></li>
        </ul>
    </div> 

     @php
     if($workshop){
         $total  = $workshop->orders->count();
         $awaiting = $workshop->orders->where('status','AWAITING')->count() ?? 0;
         $pending = $workshop->orders->where('status','PENDING')->count() ?? 0;
         $paid = $workshop->orders->where('status','PAID')->count() ?? 0;
     }
     
     @endphp
  

    <div class="grid grid-cols-1 gap-4 mt-4 mb-4 md:grid-cols-4">
      <div class="p-4 bg-gray-100 rounded-lg">
        <div class="text-2xl font-bold">{{ $total }}</div>
        <div class="text-sm text-gray-500">Total Orders</div>
      </div>
      <div class="p-4 bg-gray-100 rounded-lg">
        <div class="text-2xl font-bold">{{ $awaiting }}</div>
        <div class="text-sm text-gray-500">Awaiting Orders</div>
      </div>
      <div class="p-4 bg-gray-100 rounded-lg">
        <div class="text-2xl font-bold">{{ $pending }}</div>
        <div class="text-sm text-gray-500">Pending Orders</div>
      </div>
      <div class="p-4 bg-gray-100 rounded-lg">
        <div class="text-2xl font-bold">{{ $paid }}</div>
        <div class="text-sm text-gray-500">Paid Orders</div>
      </div>
    </div>

    <x-card title="{{ $workshop->title }}  orders" subtitle="starting on:{{ $workshop->start_date }}  ending on:{{ $workshop->end_date }}" separator class="mt-4 border-2 border-gray-200">
        <x-slot:menu>
            @can("workshops.modify")
             <livewire:components.workshoporder.createorder :workshop="$workshop" />
            @endcan
        </x-slot:menu>
        <x-tabs wire:model="selectedTab">
            <x-tab name="awaiting-tab" label="Awaiting" icon="o-clock">

                <livewire:components.workshoporder.details :workshop="$workshop" :status="'AWAITING'" />
             
            </x-tab>
            <x-tab name="pending-tab" label="Pending" icon="o-clock">
                <livewire:components.workshoporder.details :workshop="$workshop" :status="'PENDING'" />
            </x-tab>
            <x-tab name="paid-tab" label="Paid" icon="o-check-circle">
                <livewire:components.workshoporder.details :workshop="$workshop" :status="'PAID'" />
            </x-tab>
            <x-tab name="delegate-tab" label="Delegates" icon="o-users">
              <livewire:components.workshoporder.delegates :workshop="$workshop" />
            </x-tab>
        </x-tabs>
    </x-card>

 
</div>
