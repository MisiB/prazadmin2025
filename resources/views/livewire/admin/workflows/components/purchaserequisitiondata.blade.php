<div>
    <x-card title="Purchase Requisition" subtitle="{{ $purchaserequisition->status }}" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            @if($purchaserequisition->status == "AWAITING_RECOMMENDATION" && $purchaserequisition->department_id == auth()->user()->department->department_id)
            @can("purchaserequisition.recommend")
            <x-button icon="o-check" class="btn-primary" label="Make decision" @click="$wire.modal=true"/>
            @endcan
            @endif
        </x-slot:menu>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-input label="PR Number" placeholder="{{ $purchaserequisition->prnumber }}" readonly />
            <x-input label="Department" placeholder="{{ $purchaserequisition?->department?->name }}" readonly />
            <x-input label="Year" placeholder="{{ $purchaserequisition->year }}" readonly />
            <x-input label="Budget Item" placeholder="{{ $purchaserequisition?->budgetitem?->activity }}" readonly />
                  <x-input label="Requested By" placeholder="{{ $purchaserequisition?->requestedby?->name }}" readonly />
            <x-input label="Recommended By" placeholder="{{ $purchaserequisition?->recommendedby?->name }}" readonly />
                <x-textarea label="Purpose" placeholder="{{ $purchaserequisition->purpose }}" readonly />
                    <x-textarea label="Description" placeholder="{{ $purchaserequisition->description }}" readonly />
                
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-input label="Quantity" placeholder="{{ $purchaserequisition->quantity }}" readonly />
            <x-input label="Unit Price" placeholder="{{ $purchaserequisition->budgetitem->currency->name }} {{ $purchaserequisition->budgetitem->unitprice }}" readonly />
            <x-input label="Total" placeholder="{{ $purchaserequisition->budgetitem->currency->name }} {{ $purchaserequisition->budgetitem->unitprice * $purchaserequisition->quantity }}" readonly />
        </div>
            
       
    </x-card>
   
    <x-card separator class="mt-5 border-2 border-gray-200 mx-auto max-w-full">
    <x-tabs wire:model="selectedTab">

        <x-tab name="approval-tab" label="Approvals" icon="o-document-check">
    <x-card separator class="mt-5 border-2 border-gray-200 mx-auto max-w-full">
        <div class="grid  gap-4 p-4">
            @foreach ($purchaserequisition->workflow->workflowparameters->sortBy("order") as $workflowparameter)
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-gray-600">Step {{ $workflowparameter->order }}</span>
                    @php
                        $approval = $purchaserequisition->approvals?->where('workflowparameter_id', $workflowparameter->id)->first();
                        $status = $approval?->status ?? '--';
                        $statusColor = match($status) {
                            'APPROVED' => 'bg-green-100 text-green-800',
                            'REJECTED' => 'bg-red-100 text-red-800',
                            'PENDING' => 'bg-yellow-100 text-yellow-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">{{ $status }}</span>
                </div>
                <h3 class="text-xs font-medium text-gray-900 mb-2">{{ $workflowparameter->status }}</h3>
                <div class="space-y-2">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-sm text-gray-600">{{ $approval?->user->name ?? '--' }}</span>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                        <span class="text-sm text-gray-600">{{ $approval?->comment ?? '--' }}</span>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-gray-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-gray-600">{{ $approval?->created_at ?? '--' }}</span>
                    </div>
                    <div class="flex items-start">
                        @if($workflowparameter->status == $purchaserequisition->status && $status != "APPROVED")
                        @can($workflowparameter->permission->name)
                        <x-button icon="o-check" class="btn-primary" label="Make decision" @click="$wire.decisionmodal = true"/>
                        @endcan
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
            
        
    </x-card>
        </x-tab>
        <x-tab name="comments-tab" label="Comments" icon="o-chat-bubble-left-right">
            <div class="overflow-x-auto">
            <table class="table table-sm table-zebra w-full">
                <thead>
                    <tr>
                        <th>Comment</th>
                        <th>Commented By</th>
                        <th>Commented At</th>
                    </tr>
                </thead>
                <tbody>
                    @if($purchaserequisition->comments !=null)
                    @forelse ($purchaserequisition->comments as $comment)
                    <tr>
                        <td>{{ $comment['comment'] }}</td>
                        <td>{{ $comment['user_id'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($comment['created_at'])->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3">No comments</td>
                    </tr>
                    @endforelse
                    @else
                    <tr>
                        <td colspan="3" class="text-center p-3 text-gray-500">No comments</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        </x-tab>
    </x-tabs>
    </x-card>
    
    <x-modal wire:model="modal" title="Make Decision">
         <x-form wire:submit="recommend">
            <x-select label="Decision" wire:model.live="decision" placeholder="Select Decision" :options="[['id'=>'RECOMMEND','name'=>'RECOMMEND'],['id'=>'REJECT','name'=>'REJECT']]"/>
             @if($decision == "REJECT")
                <x-textarea label="Comment" wire:model="comment"/>
             @endif
             <x-pin label="Approval Code" wire:model="approvalcode" size="6" hide/>
        <x-slot:actions>
            <x-button icon="o-check" class="btn-primary" label="Submit" type="submit" spinner="recommend"/>
        </x-slot:actions>
        </x-form>
    </x-modal> 

    <x-modal wire:model="decisionmodal" title="Make Decision">
        <x-form wire:submit="savedecision">
           <x-select label="Decision" wire:model.live="decision" placeholder="Select Decision" :options="[['id'=>'APPROVED','name'=>'APPROVED'],['id'=>'REJECT','name'=>'REJECT']]"/>
            
               <x-textarea label="Comment" wire:model="comment"/>
               <x-pin label="Approval Code" wire:model="approvalcode" size="6" hide/>
            
       
       <x-slot:actions>
           <x-button icon="o-check" class="btn-primary" label="Submit" type="submit" spinner="savedecision"/>
       </x-slot:actions>
       </x-form>
   </x-modal> 
</div>
