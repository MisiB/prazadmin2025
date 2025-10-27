<div>
   <x-card>
    <x-input placeholder="Search" wire:model.live="search"/>
    <x-table :headers="$headers" :rows="$tenders" pagination>
        @scope('cell_tender', $tender)
        <ul>
           <li><b>Tender Number:</b>{{ $tender->tender_number }}</li>
           <li><b>Tender Title:</b>{{ $tender->tender_title }}</li>
           <li><b>Customer:</b>{{ $tender->customer->name }}({{ $tender->customer->regnumber }})</li>
        </ul>
        @endscope
        @scope('cell_dates', $tender)
            {{ $tender->closing_date }} {{ $tender->closing_time }}
        @endscope
        @scope('cell_status', $tender)
        @php
            $status = Carbon\Carbon::parse($tender->closing_date)->isPast() ? 'Closed' : 'Open';
        @endphp
        @if($status == 'Closed')
            <x-badge :value="$status" class="bg-red-500 text-white" />
        @else
            <x-badge :value="$status" class="bg-green-500 text-white" />
        @endif
        @endscope
        @scope('actions', $tender)
            <div class="flex space-x-2">
                <x-button icon="o-eye" wire:click="gettender({{ $tender->id }})" class="text-blue-500 btn-ghost btn-sm" />
            </div>
        @endscope
        <x-slot:empty>
            <x-alert class="alert-error" title="No tenders found." />
        </x-slot:empty>
    </x-table>
   </x-card>
   <x-modal title="Tender" wire:model="modal" box-class="max-w-2xl rounded-0">
    <x-button icon="o-pencil" wire:click="edittender" class="btn-outline btn-sm"/>
      <table class="table table-zebra">
        <tr>
            <td class="font-bold">Tender Number</td>
            <td>{{ $tender?->tender_number }}</td>
        </tr>
        <tr>
            <td class="font-bold">Tender Title</td>
            <td>{{ $tender?->tender_title }}</td>
        </tr>
        <tr>
            <td class="font-bold">Customer</td>
            <td>{{ $tender?->customer?->name }}</td>
        </tr>
        <tr>
            <td class="font-bold">Customer Regnumber</td>
            <td>{{ $tender?->customer?->regnumber }}</td>
        </tr>
        <tr>
            <td class="font-bold">Closing Date</td>
            <td>{{ $tender?->closing_date }}</td>
        </tr>
        <tr>
            <td class="font-bold">Closing Time</td>
            <td>{{ $tender?->closing_time }}</td>
        </tr>
        <tr>
            <td class="font-bold">Status</td>
            <td>{{ $tender?->status }}</td>
        </tr>
        <tr>
            <td class="font-bold">Tender URL</td>
            <td>{{ $tender?->tender_url }}</td>
        </tr>
        <tr>
            <td class="font-bold">Tender File</td>
            <td>{{ $tender?->tender_file }}</td>
        </tr>
      </table>
      <div>
  
        <x-card title="Tender fees" separator>
            <x-slot:menu>
                <x-button icon="o-plus" wire:click="addtenderfeemodal=true" class="btn-primary btn-sm"/>
            </x-slot:menu>
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th>Inventory Item</th>
                    <th>Amount</th>
                    <th>Validity Period(days)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tender?->tenderfees??[] as $tenderfee)
                <tr>
                    <td>{{ $tenderfee->inventoryitem->name }}</td>
                    <td>{{ $tenderfee->currency->name }}{{ $tenderfee->amount }}</td>
                    <td>{{ $tenderfee->validityperiod??'N/A' }}</td>
                    <td>
                        <div class="flex space-x-2 justify-end">
                            <x-button icon="o-pencil" wire:click="getfee({{ $tenderfee->id }})" class="btn-outline btn-sm"/>
                            <x-button icon="o-trash" wire:click="deletefee({{ $tenderfee->id }})" class="btn-error btn-sm" wire:confirm="Are you sure you want to delete this tender fee?"/>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No tender fees found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </x-card>
      
      </div>
   </x-modal>


   <x-modal title="Edit tender" wire:model="edittendermodal">
    <x-form wire:submit.prevent="savetender">
        <div class="grid grid-cols-2 gap-2">
        <x-input placeholder="Tender Number" wire:model="tender_number"/>
        <x-input placeholder="Tender Title" wire:model="tender_title"/>
        <x-select placeholder="Status" wire:model="status" :options="$statuses" option-label="name" option-value="id"/>
   
        
        <x-select placeholder="Tender Type" wire:model="tender_type" :options="$tendertypes" option-label="name" option-value="id"/>
        <x-input type="date" placeholder="Closing Date" wire:model="closing_date"/>
        <x-input type="time" placeholder="Closing Time" wire:model="closing_time"/>
            </div>
            <div class="grid gap-2 mt-2">
            <x-textarea placeholder="Tender Description" wire:model="tender_description"/>
            </div>
         
            <x-slot:actions>
                <x-button label="Cancel" wire:click="edittendermodal = false"/>
                <x-button label="Save" type="submit" class="btn-primary" spinner="savetender"/>
            </x-slot:actions>
    </x-form>
   </x-modal>

   <x-modal title="{{ $tenderfee_id ? 'Edit tender fee' : 'Add tender fee' }}" wire:model="addtenderfeemodal">
    <x-form wire:submit.prevent="savetenderfee">
        <div class="grid grid-cols-2 gap-2">
        <x-select placeholder="Inventory Item" wire:model="inventoryitem_id" :options="$inventoryitems" option-label="name" option-value="id"/>
        <x-select placeholder="Currency" wire:model="currency_id" :options="$currencies" option-label="name" option-value="id"/>
        <x-input placeholder="Amount" wire:model="amount"/>
        <x-input type="number" placeholder="Validity Period" wire:model="validityperiod"/>
            </div>
            <x-slot:actions>
                <x-button label="Cancel" wire:click="addtenderfeemodal = false"/>
                <x-button label="Save" type="submit" class="btn-primary" spinner="savetenderfee"/>
            </x-slot:actions>
    </x-form>
   </x-modal>
</div>
