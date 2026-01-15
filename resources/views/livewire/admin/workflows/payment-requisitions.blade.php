<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-base" />
    <x-card title="Payment Requisitions" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..."/>
            @can('payment.requisition.create')
                <x-button icon="o-plus" class="btn-primary" label="New" @click="$wire.modal=true"/>
            @endcan
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$paymentrequisitions" class="table-zebra table-xs">
            @scope("cell_reference_number", $paymentrequisition)
            <div>{{ $paymentrequisition->reference_number }}</div>
            @endscope
            @scope("cell_purpose", $paymentrequisition)
            <div class="max-w-xs truncate" title="{{ $paymentrequisition->purpose }}">{{ $paymentrequisition->purpose }}</div>
            @endscope
            @scope("cell_total_amount", $paymentrequisition)
            <div>{{ $paymentrequisition->currency->name ?? 'USD' }} {{ number_format($paymentrequisition->total_amount, 2) }}</div>
            @endscope
            @scope("cell_status", $paymentrequisition)
            @php
                $statusColors = [
                    'DRAFT' => 'badge-warning',
                    'Submitted' => 'badge-info',
                    'HOD_RECOMMENDED' => 'badge-info',
                    'ADMIN_REVIEWED' => 'badge-info',
                    'ADMIN_RECOMMENDED' => 'badge-info',
                    'AWAITING_PAYMENT_VOUCHER' => 'badge-success',
                    'Rejected' => 'badge-error',
                ];
                $color = $statusColors[$paymentrequisition->status] ?? 'badge-ghost';
            @endphp
            <x-badge :value="$paymentrequisition->status" class="{{ $color }} badge-sm" />
            @endscope
            @scope("cell_action", $paymentrequisition)
            <div class="flex items-center space-x-2">
                <x-button icon="o-eye" class="btn-xs btn-success btn-outline" link="{{ route('admin.paymentrequisition',$paymentrequisition->uuid) }}"/>
                @if($paymentrequisition->status == "DRAFT")
                    @can("payment.requisition.edit")
                    <x-button icon="o-pencil" class="btn-xs btn-info btn-outline" wire:click="edit({{ $paymentrequisition->id }})" spinner/>
                    @endcan
                    @can("payment.requisition.submit")
                    <x-button icon="o-paper-airplane" class="btn-xs btn-success" 
                        wire:click="submit({{ $paymentrequisition->id }})" 
                        wire:confirm="Are you sure you want to submit this payment requisition?" spinner/>
                    @endcan
                    @can("payment.requisition.delete")
                    <x-button icon="o-trash" class="btn-xs btn-outline btn-error" wire:click="delete({{ $paymentrequisition->id }})" wire:confirm="Are you sure?" spinner/>
                    @endcan
                @endif
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No Payment Requisitions found."/>
            </x-slot:empty>
        </x-table>
        
        <div class="mt-4">
            {{ $paymentrequisitions->links() }}
        </div>
    </x-card>

 
    <x-modal title="{{ $id ? 'Edit Payment Requisition' : 'New Payment Requisition' }}" wire:model="modal" box-class="max-w-6xl" separator>
         <x-form wire:submit="save">
            <div class="grid grid-cols-2 gap-4">
                <x-select wire:model.live="budget_line_item_id" label="Budget Line Item" :options="$budgetitems" placeholder="Select Budget Line Item" option-label="activity" option-value="id" />
                <x-select wire:model="currency_id" label="Currency" :options="$currencies" placeholder="Select Currency" option-label="name" option-value="id" />
            </div>
            
            <div class="grid grid-cols-1 gap-4">
                <x-input wire:model="purpose" label="Purpose" />
            </div>

            <x-card title="{{ $payee_type === 'CUSTOMER' ? 'Customer' : 'Staff/User' }}" subtitle="Payee Information (Mandatory)" separator class="mt-5 border-2 border-gray-200">
                <x-slot:menu>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="payee_type" value="CUSTOMER" class="radio radio-primary" />
                            <span class="font-semibold">Customer</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="payee_type" value="USER" class="radio radio-primary" />
                            <span class="font-semibold">Staff/User</span>
                        </label>
                    </div>
                </x-slot:menu>
                
                @if($payee_type === 'CUSTOMER')
                    <x-input 
                        label="Registration Number" 
                        wire:model.live="payee_search" 
                        placeholder="Enter customer registration number to search"
                    />
                    @error('payee_id') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    
                    @if($selectedCustomer != null)
                        <table class="table table-xs table-zebra mt-3">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Registration Number</th>
                                    <th>Country</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $selectedCustomer->name }}</td>
                                    <td>{{ $selectedCustomer->regnumber }}</td>
                                    <td>{{ $selectedCustomer->country ?? 'N/A' }}</td>
                                    <td>{{ $selectedCustomer->type ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        @if($payee_search)
                            <x-alert class="alert-error mt-3" title="No customer found. Please enter a valid registration number to search."/>
                        @else
                            <x-alert class="alert-info mt-3" title="Please enter registration number to search for customer."/>
                        @endif
                    @endif
                @elseif($payee_type === 'USER')
                    <x-input 
                        label="User Name" 
                        wire:model.live="payee_search" 
                        placeholder="Enter user name to search"
                    />
                    @error('payee_id') <span class="text-error text-sm">{{ $message }}</span> @enderror
                    
                    @if($selectedUser != null)
                        <table class="table table-xs table-zebra mt-3">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>User Identifier</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $selectedUser->name }}</td>
                                    <td>{{ $selectedUser->email ?? 'N/A' }}</td>
                                    <td>{{ $selectedUser->name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        @if($payee_search)
                            <x-alert class="alert-error mt-3" title="No user found. Please enter a valid user name to search."/>
                        @else
                            <x-alert class="alert-info mt-3" title="Please enter user name to search for staff/user."/>
                        @endif
                    @endif
                @endif
            </x-card>

            <div class="divider">Line Items</div>
            
            <div class="space-y-3">
                @foreach($lineItems as $index => $lineItem)
                    <div class="border rounded-lg p-4 bg-base-100">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold">Line Item {{ $index + 1 }}</h4>
                            @if(count($lineItems) > 1)
                                <x-button icon="o-trash" class="btn-xs btn-error btn-outline" wire:click="removeLineItem({{ $index }})" />
                            @endif
                        </div>
                        <div class="grid grid-cols-4 gap-3">
                            <x-input wire:model.live="lineItems.{{ $index }}.quantity" type="number" label="Quantity" min="1" />
                            <x-input wire:model.live="lineItems.{{ $index }}.unit_amount" type="number" step="0.01" label="Unit Amount" min="0" />
                            <x-input wire:model="lineItems.{{ $index }}.line_total" readonly label="Line Total" />
                            <div class="flex items-end">
                                <x-textarea wire:model="lineItems.{{ $index }}.description" label="Description" rows="2" class="col-span-2" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                <x-button icon="o-plus" class="btn-outline btn-sm" label="Add Line Item" wire:click="addLineItem" />
            </div>

            <div class="divider">Attachments</div>

            <div class="space-y-3">
                <x-input type="file" wire:model="invoice_file" label="Invoice (Mandatory)" accept=".pdf,.jpg,.jpeg,.png" />
                @error('invoice_file') <span class="text-error text-sm">{{ $message }}</span> @enderror

                <x-input type="file" wire:model="tax_clearance_file" label="Tax Clearance (Mandatory)" accept=".pdf,.jpg,.jpeg,.png" />
                @error('tax_clearance_file') <span class="text-error text-sm">{{ $message }}</span> @enderror

                @foreach($other_attachments as $index => $attachment)
                    <div class="flex gap-2 items-end">
                        <x-input type="file" wire:model="other_attachments.{{ $index }}" label="Other Attachment (Optional)" accept=".pdf,.jpg,.jpeg,.png" class="flex-1" />
                        <x-button icon="o-trash" class="btn-error btn-sm" wire:click="removeOtherAttachment({{ $index }})" />
                    </div>
                @endforeach
                <x-button icon="o-plus" class="btn-outline btn-sm" label="Add Other Attachment" wire:click="addOtherAttachment" />
            </div>

            <div class="mt-4 p-4 bg-base-200 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="font-semibold">Total Amount:</span>
                    <span class="text-lg font-bold">{{ $this->selectedCurrency ? $this->selectedCurrency->name : 'USD' }} {{ number_format($total_amount, 2) }}</span>
                </div>
                @if($budget_line_item_id)
                    @if(!$this->isZigCurrency)
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-sm text-gray-600">Available Budget:</span>
                            <span class="text-sm font-semibold">{{ $this->selectedCurrency ? $this->selectedCurrency->name : 'USD' }} {{ number_format($maxbudget, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-sm text-gray-600">Available Quantity:</span>
                        <span class="text-sm font-semibold">{{ number_format($availableQuantity, 0) }}</span>
                    </div>
                    @if(!$this->isZigCurrency && $total_amount > $maxbudget)
                        <x-alert class="alert-error mt-2" title="Total amount exceeds available budget!" />
                    @endif
                    @php
                        $totalQuantity = collect($lineItems)->sum('quantity');
                    @endphp
                    @if($totalQuantity > $availableQuantity)
                        <x-alert class="alert-error mt-2" title="Total quantity ({{ $totalQuantity }}) exceeds available quantity ({{ $availableQuantity }})!" />
                    @endif
                    @if($this->isZigCurrency)
                        <x-alert class="alert-info mt-2" title="ZiG currency selected - Budget amount will not be reserved (quantity will still be tracked)." />
                    @endif
                @endif
            </div>

            <x-slot:actions>
                <x-button  class="btn-outline btn-error" label="Close" wire:click="$wire.modal = false"/>
                <x-button  class="btn-primary" label="Save" type="submit" spinner="save"/>
            </x-slot:actions>
         </x-form>
       
    </x-modal>
</div>
