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

  

    <div class="grid grid-cols-1 gap-4 mt-4 mb-4 md:grid-cols-4">
        <x-card title="Total " separator>
            <div class="flex items-center justify-between">
                <div class="text-3xl font-bold">{{ $summaries['awaiting_count'] + $summaries['pending_count'] + $summaries['paid_count'] }}</div>
                <x-icon name="o-document-text" class="w-8 h-8 text-primary"/>
            </div>
            <div class="text-sm text-gray-500">Total: {{ number_format($summaries['total_amount']) }}</div>
        </x-card>

        <x-card title="Awaiting" separator>
            <div class="flex items-center justify-between">
                <div class="text-3xl font-bold">{{ $summaries['awaiting_count'] }}</div>
                <x-icon name="o-clock" class="w-8 h-8 text-warning"/>
            </div>
            <div class="text-sm text-gray-500">Total: {{ number_format($summaries['awaiting_total']) }}</div>
        </x-card>

        <x-card title="Pending" separator>
            <div class="flex items-center justify-between">
                <div class="text-3xl font-bold">{{ $summaries['pending_count'] }}</div>
                <x-icon name="o-clock" class="w-8 h-8 text-info"/>
            </div>
            <div class="text-sm text-gray-500">Total: {{ number_format($summaries['pending_total']) }}</div>
        </x-card>

        <x-card title="Paid " separator>
            <div class="flex items-center justify-between">
                <div class="text-3xl font-bold">{{ $summaries['paid_count'] }}</div>
                <x-icon name="o-check-circle" class="w-8 h-8 text-success"/>
            </div>
            <div class="text-sm text-gray-500">Total: {{ number_format($summaries['paid_total']) }}</div>
        </x-card>
    </div>

    <x-card title="Workshop orders" subtitle="{{ $workshop->Title }} starting on:{{ $workshop->StartDate }}  ending on:{{ $workshop->EndDate }}" separator>
        <x-slot:menu>
            @can("workshops.modify")
            <x-button label="Create Order" icon="o-plus" wire:click="$set('showCreateModal', true)" class="btn-primary"/>
            @endcan
        </x-slot:menu>
        <x-tabs wire:model="selectedTab">
            <x-tab name="awaiting-tab" label="Awaiting" icon="o-clock">
                <x-card title="Awaiting" separator progress-indicator class="mt-4">
                    <x-table :headers="$headers" :rows="$awaiting" separator progress-indicator   show-empty-text empty-text="Nothing Here!">
                        @scope('cell_amount',$row)
                        {{ $row->currency->Name }} {{ number_format((float)$row->amount) }}
                        @endscope
                        @scope('cell_action',$row)
                        <div class="flex gap-2">
                            <x-button label="View" icon="o-eye" class="btn btn-xs btn-primary" wire:click="viewOrder({{ $row->id }})"/>
                                @can("workshops.modify")
                                 <x-button label="Edit" icon="o-pencil" class="bg-blue-400 btn btn-xs" wire:click="editOrder({{ $row->id }})"/>
                        
                            <x-button label="Delete" icon="o-trash" class="bg-red-400 btn btn-xs" wire:click="delete({{ $row->id }})"/>
                                @endcan
                        </div>
                        @endscope
                      </x-table>
                </x-card>
            </x-tab>
            <x-tab name="pending-tab" label="Pending" icon="o-clock">
                <x-card title="Pending " separator progress-indicator class="mt-4">
                    <x-table :headers="$headers" :rows="$pending" separator progress-indicator   show-empty-text empty-text="Nothing Here!">
                        @scope('cell_amount',$row)
                        {{ $row->currency->Name }}{{ number_format((float)$row->amount) }}
                        @endscope
                        @scope('cell_action',$row)
                        <div class="flex gap-2">
                            <x-button label="View" icon="o-eye" class="btn btn-xs btn-primary" wire:click="viewOrder({{ $row->id }})"/>
                                @can("workshops.modify")
                            <x-button label="Delete" icon="o-trash" class="bg-red-400 btn btn-xs" wire:click="delete({{ $row->id }})"/>
                                  <x-button label="Edit" icon="o-pencil" class="bg-blue-400 btn btn-xs" wire:click="editOrder({{ $row->id }})"/>
                        
                                @endcan
                        </div>
                        @endscope
                      </x-table>
                </x-card>
            </x-tab>
            <x-tab name="paid-tab" label="Paid" icon="o-check-circle">
                <x-card title="Paid" separator progress-indicator class="mt-4">
                    <x-table :headers="$headers" :rows="$paid" separator progress-indicator   show-empty-text empty-text="Nothing Here!">
                        @scope('cell_amount',$row)
                        {{ $row->currency->Name }} {{ number_format((float)$row->amount) }}
                        @endscope
                        @scope('cell_action',$row)
                        <div class="flex gap-2">
                            <x-button label="View" icon="o-eye" class="btn btn-xs btn-primary" wire:click="viewOrder({{ $row->id }})"/>
                            <x-button label="Delegates" icon="o-users" class="btn btn-xs btn-primary" wire:click="getDelegates({{ $row->id }})"/>
                        </div>
                        @endscope
                      </x-table>
                </x-card>
            </x-tab>
            <x-tab name="delegate-tab" label="Delegates" icon="o-users">
                <x-button label="Export to CSV" icon="o-arrow-down-tray" wire:click="exportDelegatesToCsv" class="btn-secondary"/>
  
                <x-table :headers="$delegateheaders" :rows="$fulldelegatelist" separator progress-indicator show-empty-text empty-text="No delegates added yet!">
                    @scope('cell_action',$row)
                    <div class="flex gap-2">
                        @can("workshops.modify")
                        <x-button label="Edit" icon="o-pencil" class="btn btn-xs btn-primary" wire:click="editDelegate({{ $row->id }})"/>
                        <x-button label="Delete" icon="o-trash" class="bg-red-400 btn btn-xs" wire:click="deleteDelegate({{ $row->id }})"/>
                            @endcan
                    </div>
                    @endscope
                </x-table>
            </x-tab>
        </x-tabs>
    </x-card>

    <!-- Create Invoice Modal -->
    <x-modal wire:model="showCreateModal" title="Create Order" box-class="w-11/12 max-w-5xl">
        <div class="grid grid-cols-4 gap-1">
            <x-input label="Name" wire:model="name" />
            <x-input label="Surname" wire:model="surname" />
            <x-input label="Email" type="email" wire:model="email" />
            <x-input label="Number of Delegates" type="number" wire:model.live="delegates" min="1" />
        
        </div>

        <div class="grid grid-cols-4 gap-1 mt-4">
            
             <x-select label="Currency" wire:model.live="currencyId" :options="$currencies" option-label="Name" option-value="id" />
             <x-select label="Exchange Rate" wire:model.live="exchangerate_id" :options="$exchangerates" option-label="name" option-value="id" />
            <div class="form-control">
                <x-input  label="Total Cost" type="text" readonly 
                    value="{{ $cost ? number_format($cost, 2) . ' ' . ($currencies->firstWhere('id', $currencyId)?->Name ?? '') : '' }}"
                />
                <label class="label">
                    <span class="label-text-alt">Base cost per delegate: {{ number_format($workshop->Cost, 2) }} {{ $workshop->currency->Name }}</span>
                </label>
            </div>
            <x-input type="file" wire:model="document" label="Proof of Payment" />
        </div>

        <div class="grid gap-4 mt-4">
        

         <x-card title="Search account" separator progress-indicator>
            <x-input  wire:model="search">
                <x-slot:append>
                    {{-- Add `rounded-s-none` class (RTL support) --}}
                    <x-button label="Search" icon="o-check" class="btn-primary rounded-s-none" wire:click="searchAccount" spinner="searchAccount"/>
                </x-slot:append>
            </x-input>
            <x-table :headers="$accountheaders" :rows="$accounts" separator progress-indicator   show-empty-text empty-text="Nothing Here!">
               
                @scope('cell_action',$row)
                @if($row->id == $this->customer_id)
                <x-button label="Selected"  class="btn btn-xs"/>
                @else   
                <x-button label="Select"  class="btn btn-xs btn-primary" wire:click="selectAccount({{ $row->id }})"/>
                @endif
                
                @endscope
              </x-table>
         </x-card>
                   </div>


        <x-slot:actions>
            <div class="flex justify-between w-full">
                <x-button label="Cancel" wire:click="$set('showCreateModal', false)"/>
                <x-button label="Create Invoice" wire:click="createInvoice" class="btn-primary"/>
            </div>
        </x-slot>
    </x-modal>

    <!-- Order Details Modal -->
    <x-modal wire:model="showOrderModal" title="Order Details" subtitle="{{ $selectedOrder?->ordernumber ?? '' }}"  box-class="w-11/12 max-w-5xl" separator>
        @if($selectedOrder)
        <div class="grid grid-cols-2 gap-4">
            <x-card title="Order Information" separator>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-semibold">Name:</span>
                        <span>{{ $selectedOrder->name }} {{ $selectedOrder->surname }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Email:</span>
                        <span>{{ $selectedOrder->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Delegates:</span>
                        <span>{{ $selectedOrder->delegates }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Amount:</span>
                        <span>{{ $selectedOrder->currency->Name }} {{ number_format($selectedOrder->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Status:</span>
                        <span class="badge {{ $selectedOrder->status === 'PAID' ? 'badge-success' : ($selectedOrder->status === 'PENDING' ? 'badge-warning' : 'badge-info') }}">
                            {{ $selectedOrder->status }}
                        </span>
                    </div>
                </div>
            </x-card>

            <x-card title="Organization Details" separator>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-semibold">Name:</span>
                        <span>{{ $selectedOrder->customer->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Registration Number:</span>
                        <span>{{ $selectedOrder->customer->regnumber ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Type:</span>
                        <span>{{ $selectedOrder->customer->type ?? 'N/A' }}</span>
                    </div>
                </div>
            </x-card>

      

           
        </div>
        <x-card title="Invoice Information" separator>
            <x-slot:menu>
                @if($this->getDocumentUrl())
                <div class="flex justify-center">
                    <x-button 
                        label="View Document" 
                        icon="o-document-text" 
                        class="btn-primary" 
                        onclick="window.open('{{ $this->getDocumentUrl() }}', '_blank')"
                    />
                </div>
            @else
                <div class="text-center text-gray-500">
                    No document available
                </div>
            @endif
            </x-slot:menu>
            <div>
                <div class="flex justify-between">
                    <span class="font-semibold">Invoice Number:</span>
                    <span>{{ $selectedOrder->invoice?->invoicenumber ?? 'Invoice not found' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Invoice Status:</span>
                    <span class="badge {{ $selectedOrder->invoice?->status === 'PAID' ? 'badge-success' : ($selectedOrder->invoice?->status === 'PENDING' ? 'badge-warning' : 'badge-info') }}">
                        {{ $selectedOrder->invoice?->status ?? 'N/A' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Invoice Amount:</span>
                    <span>{{ $selectedOrder->currency->name }} {{ number_format($selectedOrder->invoice?->amount ?? 0, 2) }}</span>
                </div>
                
                {{-- Add the settlement button here --}}
                <div class="flex justify-center mt-4">
                    @if($selectedOrder->invoice?->status === 'PENDING')
                        <x-button label="Settle Invoice" wire:click="openPaymentModal({{ $selectedOrder->id }})" class="btn-success"/>
                    @elseif($selectedOrder->invoice?->status === 'PAID')
                        <span class="badge badge-success">Invoice Paid</span>
                    @else
                        <span class="text-red-500">No invoice available</span>
                    @endif
                </div>
            </div>
            {{-- <x-slot:actions>
                <div class="flex justify-between w-full">
                    @if($selectedOrder->invoice?->status === 'PENDING')
                  @else
                  <p class="text-red-500">Invoice not found</p>
                   @endIf
                   
                </div>
            </x-slot> --}}
        </x-card>
        @endif
    </x-modal>
    <x-modal  wire:model="showDelegateModal" title="Delegates" box-class="w-11/12 max-w-5xl" separator>
    <x-card title="Workshop delegates" separator>
        <x-slot:menu>
            <div class="flex gap-2">
                           <x-button label="Create Delegate" icon="o-plus" wire:click="$set('showCreateDelegateModal', true)" class="btn-primary"/>
            </div>
        </x-slot:menu>
        <x-table :headers="$delegateheaders" :rows="$delegatelist" separator progress-indicator show-empty-text empty-text="No delegates added yet!">
            @scope('cell_action',$row)
            <div class="flex gap-2">
                <x-button label="Edit" icon="o-pencil" class="btn btn-xs btn-primary" wire:click="editDelegate({{ $row->id }})"/>
                <x-button label="Delete" icon="o-trash" class="bg-red-400 btn btn-xs" wire:click="deleteDelegate({{ $row->id }})"/>
            </div>
            @endscope
        </x-table>
    </x-card>
    </x-modal>

    <!-- Create Delegate Modal -->
    <x-modal wire:model="showCreateDelegateModal" title="Add New Delegate" separator>
        <div class="grid grid-cols-2 gap-4">
            <x-input label="Name" wire:model="delegateName" />
            <x-input label="Surname" wire:model="delegateSurname" />
            <x-input label="Email" type="email" wire:model="delegateEmail" />
            <x-input label="Phone" wire:model="delegatePhone" />
            <x-input label="Title" wire:model="delegateTitle" />
            <x-input label="National ID" wire:model="delegatenationalId" />
            <x-input label="Gender" wire:model="delegategender" />
            <x-input label="Designation" wire:model="delegatedesignation" />
        </div>
        <x-slot:actions>
            <div class="flex justify-end gap-2">
                <x-button label="Cancel" wire:click="$set('showCreateDelegateModal', false)"/>
                <x-button label="Add Delegate" class="btn-primary" wire:click="createDelegate"/>
            </div>
        </x-slot:actions>
    </x-modal>

    <!-- Edit Delegate Modal -->
    <x-modal wire:model="showEditDelegateModal" title="Edit Delegate" separator>
        <div class="grid grid-cols-2 gap-4">
            <x-input label="Name" wire:model="delegateName" />
            <x-input label="Surname" wire:model="delegateSurname" />
            <x-input label="Email" type="email" wire:model="delegateEmail" />
            <x-input label="Phone" wire:model="delegatePhone" />
            <x-input label="Title" wire:model="delegateTitle" />
            <x-input label="National ID" wire:model="delegatenationalId" />
            <x-input label="Gender" wire:model="delegategender" />
            <x-input label="Designation" wire:model="delegatedesignation" />
        </div>
        <x-slot:actions>
            <div class="flex justify-end gap-2">
                <x-button label="Cancel" wire:click="$set('showEditDelegateModal', false)"/>
                <x-button label="Update Delegate" class="btn-primary" wire:click="updateDelegate"/>
            </div>
        </x-slot:actions>
    </x-modal>

    <x-modal wire:model='showEditOrderModal' title="Edit Order">
        <x-form wire:submit='UpdateOrder'>
            <div class="grid gap-2">
                <x-input  label="Delegates" wire:model.live='delegates'/>
                <x-select label="Currency" wire:model.live='currencyId' :options="$currencies" option-label="Name" option-value="id"/>
                <x-input label="Workshop cost" readonly wire:model='price'/>
                 <x-select label="Exchange Rate" wire:model.live="exchangerate_id" :options="$exchangerates" option-label="name" option-value="id" />

                <x-input label="Total cost" readonly wire:model='cost'/>
            </div>
            <x-slot:actions>
                     <div class="flex justify-end gap-2">
                <x-button label="Cancel" wire:click="showEditOrderModal=false"/>
                <x-button label="Update Order" class="btn-primary" wire:click="UpdateOrder"/>
            </div>
            </x-slot:actions>
        </x-form>
    </x-modal>


{{-- Update the existing Pay Invoice button section --}}
<x-slot:actions>
    <div class="flex justify-between w-full">
        @if($selectedOrder && $selectedOrder->invoice?->status === 'PENDING')
            <div class="flex gap-2">
                @can('settle-invoices')
                    <x-button label="Settle Invoice" wire:click="openPaymentModal({{ $selectedOrder->id }})" class="btn-success"/>
                @endcan
                {{-- <x-button label="Manage Customer" link="{{ route('admin.management.customer',$selectedOrder->customer_id) }}" class="btn-info"/> --}}
            </div>
        @elseif($selectedOrder && $selectedOrder->invoice?->status === 'PAID')
            <div class="flex items-center gap-2">
                <span class="badge badge-success">Paid</span>
                {{-- <x-button label="View Customer" link="{{ route('admin.management.customer',$selectedOrder->customer_id) }}" class="btn-info"/> --}}
            </div>
        @else
            <p class="text-red-500">{{ $selectedOrder ? 'Invoice not found' : 'No order selected' }}</p>
        @endif
    </div>
</x-slot>

{{-- Add the Payment Modal at the end of the file --}}
<x-modal wire:model="showPaymentModal" title="Settle Invoice" max-width="4xl">
    @if($selectedOrder)
    <div class="space-y-4">
        {{-- Invoice Details --}}
        <x-card title="Invoice Details" separator>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="font-semibold">Invoice Number:</span>
                    <span>{{ $selectedOrder->invoice?->invoicenumber ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="font-semibold">Amount:</span>
                    <span>{{ $selectedOrder->currency->name }} {{ number_format($selectedOrder->invoice?->amount ?? 0, 2) }}</span>
                </div>
                <div>
                    <span class="font-semibold">Customer:</span>
                    <span>{{ $selectedOrder->customer->name ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="font-semibold">Status:</span>
                    <span class="badge badge-warning">{{ $selectedOrder->invoice?->status ?? 'N/A' }}</span>
                </div>
            </div>
        </x-card>

        {{-- Payment Form --}}
        <x-card title="Settlement Details" separator>
            <div class="grid grid-cols-1 gap-4">
                <x-input 
                    label="Receipt Number" 
                    wire:model="receiptNumber" 
                    placeholder="Auto-generated receipt number"
                    hint="This receipt number is automatically generated"
                    readonly
                />
                
                <x-input 
                    label="Payment Amount" 
                    wire:model="paymentAmount" 
                    type="number" 
                    step="0.01"
                    min="0.01"
                    prefix="{{ $selectedOrder->currency->name ?? '' }}"
                />

                <x-select 
                    label="Select Suspense Wallet" 
                    wire:model.live="selectedSuspenseId"
                    placeholder="Choose a suspense wallet"
                    :options="$availableSuspenses"
                    option-label="label"
                    option-value="id"
                />

                @if(empty($availableSuspenses))
                    <div class="alert alert-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <span>No available NONREFUNDABLE suspense wallets found for this customer. Workshop settlements can only use non-refundable accounts.</span>
                    </div>
                @endif
            </div>
        </x-card>
    </div>
    @endif

    <x-slot:actions>
        <div class="flex justify-between w-full">
            <x-button label="Cancel" wire:click="closePaymentModal" />
            <x-button 
                label="Settle Invoice" 
                wire:click="settleInvoice" 
                class="btn-success"
                :disabled="empty($availableSuspenses)"
                spinner="settleInvoice"
            />
        </div>
    </x-slot>
</x-modal>
</div>
