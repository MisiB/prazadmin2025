<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    <x-card title="Workshops Invoices" separator progress-indicator class="mt-4 border-2 border-gray-200">
            <x-slot:menu>
               <x-input wire:model.live="search" placeholder="Search workshops..." />
            </x-slot:menu>
            <x-table :headers="$headers" :rows="$workshops" separator progress-indicator show-empty-text with-pagination empty-text="Nothing Here!">
                @scope('cell_title', $row)
                    <div>{{ $row->title }}</div>
                @endscope
                @scope('cell_created_at', $row)
                    <div>{{ $row->created_at->diffForHumans() }}</div>
                @endscope
                @scope('cell_action', $row)
                    <div class="flex gap-2">
                        <x-button label="View" wire:click="viewworkshop({{ $row->id }})" class="btn btn-xs btn-info"/>
                    </div>
                @endscope
            </x-table>
    </x-card>

    <x-modal wire:model="viewmodal" title="Workshop Details" box-class="h-screen w-screen max-w-full m-0 rounded-none">
     @php
        $awaitingtotal = 0;
        $paidtotal = 0;
        $total = 0;
         if(count($invoices) > 0){
         $awaitingtotal = $invoices->where('status','AWAITING')->count() ?? 0;
         $paidtotal = $invoices->where('status','PAID')->count() ?? 0;
         $total = $invoices->count() ?? 0;}
     @endphp
     <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="p-4 bg-gray-100 rounded-lg">
            <div class="text-2xl font-bold">{{ $total }}</div>
            <div class="text-sm text-gray-500">Total Orders</div>
        </div>
        <div class="p-4 bg-gray-100 rounded-lg">
            <div class="text-2xl font-bold">{{ $awaitingtotal }}</div>
            <div class="text-sm text-gray-500">Awaiting Orders</div>
        </div>
        <div class="p-4 bg-gray-100 rounded-lg">
            <div class="text-2xl font-bold">{{ $paidtotal }}</div>
            <div class="text-sm text-gray-500">Paid Orders</div>
        </div>
     </div>
     <x-card title="Invoices" class="mt-4 border-2 border-gray-200" separator>
        <x-slot:menu>
          <x-select wire:model.live="status" :options="$statuslist" placeholder="Select Status" />
          <x-select wire:model.live="currency_id" :options="$currencies" placeholder="Select Currency" option-label="name" option-value="id" />
        
       </x-slot:menu>
       <x-table :headers="$headerinvoices" :rows="$invoices" separator progress-indicator show-empty-text  empty-text="Nothing Here!">
        @scope('cell_action', $row)
                    <div class="flex gap-2">
                        <x-button label="View" wire:click="viewinvoice({{ $row->id }})" class="btn btn-xs btn-info"/>
                    </div>
                @endscope
       </x-table>
     </x-card>
    </x-modal>
    <x-modal wire:model="viewinvoicemodal" title="Invoice Details" box-class="max-w-5xl h-screen">
       <div class="font-bold">Wallet Balance: {{ $invoice?->currency?->name }} {{ str_replace(',', '', $suspense) }}</div>
       <div class="mt-5">
       <x-tabs wire:model="selectedTab" class="mt-5">
        <x-tab name="users-tab" label="Invoice Details">
            <x-card class="border-2 border-gray-200" separator>
          <table class="table table-zebra">
            <tbody>
                <tr><th>Invoice Number</th><td>{{ $invoice?->invoicenumber }}</td></tr>
                <tr><th>Invoice Date</th><td>{{ $invoice?->created_at }}</td></tr>
                <tr><th>Invoice Amount</th><td>{{ $invoice?->currency?->name }} {{ $invoice?->cost }}</td></tr>
                <tr><th>Customer</th><td>{{ $invoice?->customer?->name }}</td></tr>
                <tr><th>Delegates</th><td>{{ $invoice?->delegates }}</td></tr>
                <tr><th>Invoice Status</th><td>{{ $invoice?->status }}</td></tr>
            </tbody>
          </table>
          <x-slot:actions>
          
            <x-button label="Settle" wire:click="settleinvoice({{ $invoice?->id }})" class="btn btn-primary"/>
            
          </x-slot:actions>
          </x-card>
          
        </x-tab>
        <x-tab name="tricks-tab" label="Proof of Payment">
            @php
                $documentUrl = $invoice?->workshoporder?->documenturl;
                $documentExists = $documentUrl && \Illuminate\Support\Facades\Storage::disk('public')->exists($documentUrl);
            @endphp
            @if($documentExists)
                <div class="w-full h-screen overflow-hidden">
                    <iframe src="{{ Storage::disk('public')->url($documentUrl) }}" class="w-full h-full" frameborder="0"></iframe>
                </div>
            @else
                <div class="flex items-center justify-center h-96">
                    <div class="text-center">
                        <div class="text-6xl font-light text-gray-400 mb-4">404</div>
                        <div class="text-xl text-gray-500 font-medium">NOT FOUND</div>
                        <p class="text-gray-400 mt-4">Proof of payment document is not available</p>
                    </div>
                </div>
            @endif
        </x-tab>
        <x-tab name="musics-tab" label="Bank Transactions">
            @if($invoice?->customer)
            <livewire:admin.finance.searchtransactions :customer="$invoice?->customer" />
            @endif
        </x-tab>
    </x-tabs>
    </div>

    </x-modal>
       
</div>
