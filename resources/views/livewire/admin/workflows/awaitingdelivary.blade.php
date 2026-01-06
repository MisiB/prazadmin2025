<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
    link-item-class="text-base" />
    <x-card title="Awaiting Delivery" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-input type="text" wire:model.live="search" placeholder="Search..."/>
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$rows" class="table-zebra table-xs">
            @scope("cell_budgetitem", $row)
            <div>{{ $row->budgetitem->activity }}</div>
            @endscope
            @scope("cell_purpose", $row)
            <div>{{ $row->purpose }}</div>
            @endscope
            @scope("cell_quantity", $row)
            <div>{{ $row->quantity }}</div>
            @endscope
            @scope("cell_unitprice", $row)
            <div>{{ $row->budgetitem->currency->name }} {{ $row->budgetitem->unitprice }}</div>
            @endscope
            @scope("cell_total", $row)
            <div>{{ $row->budgetitem->currency->name }} {{ $row->budgetitem->unitprice * $row->quantity }}</div>
            @endscope
            @scope("cell_created_at", $row)
            <div>{{ $row->created_at->diffForHumans() }}</div>
            @endscope
            @scope("cell_updated_at", $row)
            <div>{{ $row->updated_at->diffForHumans() }}</div>
            @endscope
            @scope("cell_action", $row)
            <div class="flex items-center space-x-2">
                <x-button icon="o-eye" class="btn-xs btn-success btn-outline" wire:click="getpurchaseerequisition({{ $row->id }})"/>
          
            </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No Purchase Requisitions found."/>
            </x-slot:empty>
        </x-table>
    </x-card>

    <x-modal wire:model="modal" title="Purchase Requisition Details" separator box-class="max-w-6xl" progress-indicator>
        <x-card title="Purchase Requisition" subtitle="{{ $purchaserequisition?->status }}" separator class="mt-5 border-2 border-gray-200">
            <x-slot:menu>
                @if($purchaserequisition?->status == "AWAITING_PMU")
                @can("procurement.approve")
                <x-button icon="o-check" class="btn-success" label="Approve" @click="$wire.approve" wire:confirm="Are you sure you want to approve this purchase requisition?"/>
                @endcan
                @endif
            </x-slot:menu>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <x-input label="PR Number" placeholder="{{ $purchaserequisition?->prnumber }}" readonly />
                <x-input label="Department" placeholder="{{ $purchaserequisition?->department?->name }}" readonly />
                <x-input label="Year" placeholder="{{ $purchaserequisition?->year }}" readonly />
                <x-input label="Budget Item" placeholder="{{ $purchaserequisition?->budgetitem?->activity }}" readonly />
                      <x-input label="Requested By" placeholder="{{ $purchaserequisition?->requestedby?->name }}" readonly />
                <x-input label="Recommended By" placeholder="{{ $purchaserequisition?->recommendedby?->name }}" readonly />
                    <x-textarea label="Purpose" placeholder="{{ $purchaserequisition?->purpose }}" readonly />
                        <x-textarea label="Description" placeholder="{{ $purchaserequisition?->description }}" readonly />
                    
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <x-input label="Quantity" placeholder="{{ $purchaserequisition?->quantity }}" readonly />
                <x-input label="Unit Price" placeholder="{{ $purchaserequisition?->budgetitem?->currency?->name }} {{ $purchaserequisition?->budgetitem?->unitprice }}" readonly />
                <x-input label="Total" placeholder="{{ $purchaserequisition?->budgetitem?->currency?->name }} {{ $purchaserequisition?->budgetitem?->unitprice * $purchaserequisition?->quantity }}" readonly />
            </div>     
                
           
        </x-card>

        <x-card title="Awards" separator class="mt-5 border-2 border-gray-200">
            <x-slot:menu>
                @if($purchaserequisition?->awards->count() > 0)
                    <div class="flex items-center gap-4 flex-wrap">
                        <div class="font-bold">Total award: {{ $purchaserequisition?->budgetitem?->currency?->name }}{{ $purchaserequisition?->awards->sum('amount') }}</div>
                        @if($purchaserequisition?->status == "AWAITING_DELIVERY")
                            @php
                                $totalQuantity = $purchaserequisition->awards->sum('quantity');
                                $totalDelivered = $purchaserequisition->awards->sum(function($award) { return $award->quantity_delivered ?? 0; });
                                $totalRemaining = $totalQuantity - $totalDelivered;
                                $deliveryProgress = $totalQuantity > 0 ? ($totalDelivered / $totalQuantity) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium">Delivery Progress:</span>
                                <x-badge :value="$totalDelivered . ' / ' . $totalQuantity" class="badge-info badge-sm" />
                                @if($totalRemaining == 0)
                                    <x-badge value="Complete" class="badge-success badge-sm" />
                                @else
                                    <x-badge :value="$totalRemaining . ' remaining'" class="badge-warning badge-sm" />
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
                @if($purchaserequisition?->status == "AWAITING_PMU")
                    <x-button icon="o-plus" class="btn-primary" label="Add Award" @click="$wire.awardmodal=true" />
                @endif
            </x-slot:menu>
            <div class="overflow-x-auto -mx-4 px-4">
                <table class="table table-zebra w-full">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Tender Number</th>
                    <th>Quantity</th>
                    <th>Quantity Delivered</th>
                    <th>Remaining</th>
                    <th>Amount</th>
                    <th>Payment Currency</th>
                    <th>Status</th>
                    <th>Delivery Date</th>
                    <th>Documents</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchaserequisition?->awards??[] as $award)
                @php
                    $quantityDelivered = $award->quantity_delivered ?? 0;
                    $remaining = $award->quantity - $quantityDelivered;
                    $isFullyDelivered = $quantityDelivered >= $award->quantity;
                    $deliveryStatusColor = $isFullyDelivered ? 'badge-success' : ($quantityDelivered > 0 ? 'badge-warning' : 'badge-ghost');
                @endphp
                <tr>
                    <td>{{ $award->customer->name }}</td>
                    <td>{{ $award->tendernumber }}</td>
                    <td>{{ $award->quantity }}</td>
                    <td>
                        <x-badge :value="$quantityDelivered" class="{{ $deliveryStatusColor }} badge-sm" />
                    </td>
                    <td>
                        <x-badge :value="$remaining" class="badge-info badge-sm" />
                    </td>
                    <td>{{ $purchaserequisition?->budgetitem?->currency?->name }}{{ $award->amount }}</td>
                    <td>
                        @if($award->paymentcurrency)
                            <div class="space-y-1">
                                <div class="font-semibold text-sm">{{ $award->paymentcurrency->name }}</div>
                                @if($award->is_split_payment && $award->secondpaymentcurrency)
                                    <div class="text-xs text-gray-600">+ {{ $award->secondpaymentcurrency->name }}</div>
                                @endif
                                @if($award->pay_at_prevailing_rate && strtoupper($award->paymentcurrency->name) === 'ZIG')
                                    <x-badge value="Prevailing Rate" class="badge-info badge-xs" />
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400">Not set</span>
                        @endif
                    </td>
                    <td>{{ $award->status }}</td>
                    <td>
                        @if($award->delivery_date)
                            {{ \Carbon\Carbon::parse($award->delivery_date)->format('Y-m-d') }}
                        @else
                            <span class="text-gray-400">Not delivered</span>
                        @endif
                    </td>
                    <td>
                        <x-button icon="o-document" class="indicator btn-xs btn-outline btn-primary" wire:click="getdocuments({{ $award->id }})">
                            <x-badge value="{{ $award->documents->count() }}" class="badge-secondary badge-sm indicator-item" />
                        </x-button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-red-500">No awards found</td>
                </tr>
                @endforelse
            </tbody>
           </table>
            </div>

        </x-card>

        {{-- Delivery Actions Section --}}
        @if($purchaserequisition?->status == "AWAITING_DELIVERY")
            @can("ADMIN.DELIVERY.ACCESS")
                @php
                    $undeliveredAwards = $purchaserequisition->awards->filter(function($award) {
                        $quantityDelivered = $award->quantity_delivered ?? 0;
                        return $award->status == "APPROVED" && $quantityDelivered < $award->quantity;
                    });
                    $awardsWithNotes = $purchaserequisition->awards->filter(function($award) {
                        return !empty($award->delivery_notes);
                    });
                @endphp
                   @if($undeliveredAwards->count() > 0 || $awardsWithNotes->count() > 0)
                    <x-card title="Delivery Actions" separator class="mt-5 border-2 border-gray-200">
                       <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                               @foreach($undeliveredAwards as $award)
                                   @php
                                       $quantityDelivered = $award->quantity_delivered ?? 0;
                                       $remaining = $award->quantity - $quantityDelivered;
                                   @endphp
                                   <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                       <div class="flex items-center justify-between mb-2">
                                           <div>
                                               <div class="font-semibold text-sm">{{ $award->customer->name }}</div>
                                               <div class="text-xs text-gray-600">{{ $award->tendernumber }}</div>
                                               @if($award->paymentcurrency)
                                                   <div class="text-xs text-gray-500 mt-1">
                                                       <span class="font-medium">Payment:</span> {{ $award->paymentcurrency->name }}
                                                       @if($award->is_split_payment && $award->secondpaymentcurrency)
                                                           <span> + {{ $award->secondpaymentcurrency->name }}</span>
                                                       @endif
                                                       @if($award->pay_at_prevailing_rate && strtoupper($award->paymentcurrency->name) === 'ZIG')
                                                           <x-badge value="Prevailing Rate" class="badge-info badge-xs ml-1" />
                                                       @endif
                                                   </div>
                                               @endif
                                           </div>
                                           <x-badge :value="$remaining . ' remaining'" class="badge-warning badge-sm" />
                                       </div>
                                       <div class="flex gap-2">
                                           <x-button icon="o-truck" class="btn-success btn-sm flex-1" 
                                               label="Record Delivery" 
                                               wire:click="openDeliveryModal({{ $award->id }})" />
                                           @if($award->delivery_notes)
                                               <x-button icon="o-information-circle" class="btn-info btn-sm btn-outline" 
                                                   wire:click="$wire.deliveryNotesModal = true; $wire.selectedAwardId = {{ $award->id }}" 
                                                   title="View delivery notes" />
                                           @endif
                                       </div>
                                   </div>
                               @endforeach
                               @foreach($awardsWithNotes->filter(function($award) use ($undeliveredAwards) {
                                   return !$undeliveredAwards->contains('id', $award->id);
                               }) as $award)
                                   <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                       <div class="flex items-center justify-between mb-2">
                                           <div>
                                               <div class="font-semibold text-sm">{{ $award->customer->name }}</div>
                                               <div class="text-xs text-gray-600">{{ $award->tendernumber }}</div>
                                           </div>
                                           <x-badge value="Delivered" class="badge-success badge-sm" />
                                       </div>
                                       <x-button icon="o-information-circle" class="btn-info btn-sm w-full btn-outline" 
                                           label="View Notes" 
                                           wire:click="$wire.deliveryNotesModal = true; $wire.selectedAwardId = {{ $award->id }}" />
                                   </div>
                               @endforeach
                       </div>
                    </x-card>
                   @endif
               @endcan
           @endif

        {{-- Delivery History Section --}}
        @if($purchaserequisition?->awards->count() > 0)
            @php
                $awardsWithDeliveries = $purchaserequisition->awards->filter(function($award) {
                    return $award->deliveries && $award->deliveries->count() > 0;
                });
            @endphp
            @if($awardsWithDeliveries->count() > 0)
                <x-card title="Delivery History" separator class="mt-5 border-2 border-gray-200 mx-auto max-w-full">
                    <div class="space-y-4">
                        @foreach($awardsWithDeliveries as $award)
                            <div class="bg-white p-4 rounded-lg border border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h4 class="font-semibold text-lg">{{ $award->customer->name }}</h4>
                                        <p class="text-sm text-gray-600">Tender Number: {{ $award->tendernumber }}</p>
                                    </div>
                                    <x-badge :value="$award->quantity_delivered . ' / ' . $award->quantity . ' delivered'" class="badge-info badge-sm" />
                                </div>
                                
                                <div class="space-y-2">
                                    @foreach($award->deliveries->sortByDesc('created_at') as $delivery)
                                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                                                <div>
                                                    <span class="font-medium text-gray-700">Quantity:</span>
                                                    <span class="ml-2">{{ $delivery->quantity_delivered }}</span>
                                                </div>
                                                <div>
                                                    <span class="font-medium text-gray-700">Date:</span>
                                                    <span class="ml-2">{{ \Carbon\Carbon::parse($delivery->delivery_date)->format('Y-m-d') }}</span>
                                                </div>
                                                <div>
                                                    <span class="font-medium text-gray-700">Recorded By:</span>
                                                    <span class="ml-2">{{ $delivery->deliveredby->name ?? 'N/A' }}</span>
                                                </div>
                                                @if($delivery->delivery_notes)
                                                    <div class="md:col-span-3">
                                                        <span class="font-medium text-gray-700">Notes:</span>
                                                        <p class="mt-1 text-gray-600">{{ $delivery->delivery_notes }}</p>
                                                    </div>
                                                @endif
                                                @if($delivery->invoice_filepath || $delivery->delivery_note_filepath || $delivery->tax_clearance_filepath)
                                                    <div class="md:col-span-3">
                                                        <span class="font-medium text-gray-700">Attachments:</span>
                                                        <div class="mt-2 flex flex-wrap gap-2">
                                                            @if($delivery->invoice_filepath)
                                                                <a href="{{ asset('storage/' . $delivery->invoice_filepath) }}" target="_blank" 
                                                                   class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs hover:bg-blue-200">
                                                                    <x-icon name="o-document" class="w-3 h-3" />
                                                                    Invoice
                                                                </a>
                                                            @endif
                                                            @if($delivery->delivery_note_filepath)
                                                                <a href="{{ asset('storage/' . $delivery->delivery_note_filepath) }}" target="_blank" 
                                                                   class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 rounded text-xs hover:bg-green-200">
                                                                    <x-icon name="o-document" class="w-3 h-3" />
                                                                    Delivery Note
                                                                </a>
                                                            @endif
                                                            @if($delivery->tax_clearance_filepath)
                                                                <a href="{{ asset('storage/' . $delivery->tax_clearance_filepath) }}" target="_blank" 
                                                                   class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs hover:bg-purple-200">
                                                                    <x-icon name="o-document" class="w-3 h-3" />
                                                                    Tax Clearance
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="md:col-span-3 text-xs text-gray-500">
                                                    Recorded: {{ $delivery->created_at->format('Y-m-d H:i:s') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        @endif
       
      
    </x-modal>
    <x-modal wire:model="documentmodal" title="Documents" separator box-class="max-w-3xl" progress-indicator>
        <table class="table table-xs table-zebra">
            <thead>
                <tr>
                    <th>Document</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents as $document)
                <tr>
                    <td>{{ $document->document }}</td>
                       <td class="flex justify-end space-x-2">      
                        <x-button icon="o-eye" class="btn-primary btn-xs" wire:click="ViewDocument({{ $document->id }})"/>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="text-center text-red-500">No documents found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-modal>
    <x-modal wire:model="viewdocumentmodal" title="Document" separator progress-indicator box-class="fixed inset-0 w-screen max-w-full h-screen max-h-full rounded-none">
        @if($currentdocument)
            <div class="w-full h-screen overflow-hidden">
                <iframe src="{{ $currentdocument }}" class="w-full h-full" frameborder="0"></iframe>
            </div>
        @else
            <div class="text-center text-red-500">Document not found</div>
        @endif
    </x-modal>

    <!-- Delivery Recording Modal -->
    <x-modal wire:model="deliverymodal" title="Record Delivery" separator box-class="max-w-2xl">
        @if($selectedAwardId)
            @php
                $award = $purchaserequisition?->awards->where('id', $selectedAwardId)->first();
                $quantityDelivered = $award->quantity_delivered ?? 0;
                $remaining = $award ? ($award->quantity - $quantityDelivered) : 0;
            @endphp
            @if($award)
                <x-form wire:submit="recordDelivery">
                    <div class="bg-blue-50 p-4 rounded-lg mb-4 border border-blue-200">
                        <h3 class="font-semibold text-gray-700 mb-2">Award Information</h3>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div><span class="font-medium">Customer:</span> {{ $award->customer->name }}</div>
                            <div><span class="font-medium">Tender Number:</span> {{ $award->tendernumber }}</div>
                            <div><span class="font-medium">Total Quantity:</span> {{ $award->quantity }}</div>
                            <div><span class="font-medium">Already Delivered:</span> {{ $quantityDelivered }}</div>
                            <div class="col-span-2"><span class="font-medium">Remaining:</span> <span class="text-primary font-bold">{{ $remaining }}</span></div>
                        </div>
                    </div>

                    <x-input wire:model="quantity_delivered" type="number" label="Quantity Delivered" 
                        min="1" :max="$remaining" 
                        hint="Maximum: {{ $remaining }}" required />
                    <x-input wire:model="delivery_date" type="date" label="Delivery Date" required />
                    <x-textarea wire:model="delivery_notes" label="Delivery Notes" 
                        placeholder="Enter any notes about this delivery (optional)" rows="3" />

                    {{-- Attachments Section --}}
                    <div class="space-y-4 mt-4 pt-4 border-t border-gray-200">
                        <h3 class="font-semibold text-gray-700 mb-3">Attachments <span class="text-red-500">*</span></h3>
                        <x-input wire:model="invoice_file" type="file" label="Invoice *" 
                            accept=".pdf,.jpg,.jpeg,.png"
                            hint="PDF, JPG, PNG (Max: 10MB)" required />
                        <x-input wire:model="delivery_note_file" type="file" label="Delivery Note *" 
                            accept=".pdf,.jpg,.jpeg,.png"
                            hint="PDF, JPG, PNG (Max: 10MB)" required />
                        <x-input wire:model="tax_clearance_file" type="file" label="Tax Clearance *" 
                            accept=".pdf,.jpg,.jpeg,.png"
                            hint="PDF, JPG, PNG (Max: 10MB)" required />
                    </div>

                    <x-slot:actions>
                        <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.deliverymodal = false" />
                        <x-button icon="o-check" class="btn-success" label="Record Delivery" type="submit" spinner="recordDelivery" />
                    </x-slot:actions>
                </x-form>
            @endif
        @endif
    </x-modal>

    <!-- Delivery Notes View Modal -->
    <x-modal wire:model="deliveryNotesModal" title="Delivery Notes" separator>
        @if($selectedAwardId)
            @php
                $award = $purchaserequisition?->awards->where('id', $selectedAwardId)->first();
            @endphp
            @if($award && $award->delivery_notes)
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes:</label>
                        <div class="p-3 bg-gray-50 rounded border">{{ $award->delivery_notes }}</div>
                    </div>
                    @if($award->delivery_date)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Date:</label>
                            <div class="p-3 bg-gray-50 rounded border">{{ \Carbon\Carbon::parse($award->delivery_date)->format('Y-m-d') }}</div>
                        </div>
                    @endif
                    @if($award->deliveredby)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Recorded By:</label>
                            <div class="p-3 bg-gray-50 rounded border">{{ $award->deliveredby->name }}</div>
                        </div>
                    @endif
                </div>
            @else
                <x-alert class="alert-info" title="No delivery notes available" />
            @endif
        @endif
        <x-slot:actions>
            <x-button class="btn-outline btn-error" label="Close" wire:click="$wire.deliveryNotesModal = false" />
        </x-slot:actions>
    </x-modal>
</div>
