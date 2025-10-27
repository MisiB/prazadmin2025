<div>

    <x-modulewelcomebanner :breadcrumbs="$breadcrumbs"/>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 mt-4">
        <x-card>
            <div class="flex items-center space-x-3">
                <div class="bg-yellow-200 p-3 rounded-full">
                    <x-icon name="o-clock" class="w-8 h-8 text-blue-600"/>
                </div>
                <div class="text-xl font-bold text-gray-700">{{ $totalpending }}</div>
                <div class="text-sm text-gray-600">Pending</div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center space-x-3">
                <div class="bg-blue-200 p-3 rounded-full">
                    <x-icon name="o-check" class="w-8 h-8 text-green-600"/>
                </div>
                <div class="text-xl font-bold text-gray-700">{{ $totalapproved }}</div>
                <div class="text-sm text-gray-600">Approved</div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center space-x-3">
                <div class="bg-blue-400 p-3 rounded-full">
                    <x-icon name="o-book-open" class="w-8 h-8 text-yellow-600"/>
                </div>
                <div class="text-xl font-bold text-gray-700">{{ $totalopened }}</div>
                <div class="text-sm text-gray-600">Open</div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center space-x-3">
                <div class="bg-green-200 p-3 rounded-full">
                    <x-icon name="m-clipboard-document-list" class="w-8 h-8 text-yellow-600"/>
                </div>
                <div class="text-xl font-bold text-gray-700">{{ $totaldelivered }}</div>
                <div class="text-sm text-gray-600">Delivered</div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center space-x-3">
                <div class="bg-green-700 p-3 rounded-full">
                    <x-icon name="o-hand-thumb-up" class="w-8 h-8 text-white"/>
                </div>
                <div class="text-xl font-bold text-gray-700">{{ $totalrecieved }}</div>
                <div class="text-sm text-gray-600">Received</div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center space-x-3">
                <div class="bg-red-700 p-3 rounded-full">
                    <x-icon name="c-arrow-left-end-on-rectangle" class="w-8 h-8 text-white"/>
                </div>
                <div class="text-xl font-bold text-gray-700">{{ $totalrejected }}</div>
                <div class="text-sm text-gray-600">Rejected</div>
            </div>
        </x-card>

    </div>

    <div class="mt-10">
        <x-card title="My Stores Requisitions" separator class="mt-5 border-2 border-gray-200">
            <x-slot:menu>
                <x-input placeholder="Search by emailed reference..." wire:model.live.debounce="searchuuid"/>
                <x-select wire:model.live.debounce="statusfilter" placeholder="Filter by status" :options="$statuslist" option-label="name" option-value="id" />
                <x-button icon="o-plus" label="Add Stores Requisition" class="btn-primary" wire:click="addrequisitionmodal=true"/>
            </x-slot:menu>

            <x-table :headers="$headersforpending" :rows="$storesrequisitions">
                @scope('cell_itembanner', $storesrequisition)
                    <img src="{{asset('images/img_placeholder.jpg')}}" alt="" class="w-[60px] h-auto">
                @endscope
                @scope('cell_itemscount', $storesrequisition)
                    <span>{{collect(json_decode($storesrequisition->requisitionitems,true))->count()}}</span>
                @endscope
                @scope('cell_status', $storesrequisition)
                    @if($storesrequisition->status=='P')
                        <span class="badge badge-warning">Pending</span>
                    @elseif($storesrequisition->status=='A')
                        <span class="badge badge-success">Approved</span>
                    @elseif($storesrequisition->status=='O')
                        <span class="badge badge-warning">Opened</span>
                    @elseif($storesrequisition->status=='D')
                        <span class="badge badge-warning">Delivered</span>
                    @elseif($storesrequisition->status=='C')
                        <span class="badge badge-success">Recieved</span>
                    @else
                        <span class="badge badge-error">Rejected</span>
                    @endif
                @endscope 
                @scope('cell_initiator',$storesrequisition)   
                    <span>{{$storesrequisition->initiator->name." ".$storesrequisition->initiator->surname}}</span>
                @endscope 
                @scope('cell_actions', $storesrequisition)
                    <div class="flex space-x-2">
                        <div>
                            <x-button icon="o-eye" 
                                wire:click="viewrequisition('{{$storesrequisition->storesrequisition_uuid}}', '{{$storesrequisition->initiator_id}}')" 
                                spinner class="text-blue-500 btn-outline btn-sm" 
                            />
                        </div>

                    @haspermission('storesrequisitions.access')
                        @if($storesrequisition->status=== 'D')
                            <div>
                                <x-button label="✅"
                                    wire:click="initiateacceptance('{{$storesrequisition->storesrequisition_uuid}}','{{$storesrequisition->initiator_id}}', true)" 
                                    wire:confirm="Do you want to accept delivery?" 
                                    spinner 
                                    class="bg-green-600 btn-outline btn-sm" 
                                    :disabled="$storesrequisition->status!=='D'"
                                />
                            </div>
                            <div>
                                <x-button label="❌"
                                    wire:click="initiateacceptance('{{$storesrequisition->storesrequisition_uuid}}','{{$storesrequisition->initiator_id}}', false)" 
                                    wire:confirm="Do you want to reject delivery?" 
                                    spinner 
                                    class="bg-red-600 btn-outline btn-sm" 
                                    :disabled="$storesrequisition->status!=='D'"
                                />
                            </div>
                        @endif
                        @if($storesrequisition->status=== 'P')
                            <div>
                                <x-button label="❌"
                                    wire:click="initiaterecall('{{$storesrequisition->storesrequisition_uuid}}')" 
                                    wire:confirm="Do you want to recall delivery by rejection?" 
                                    spinner 
                                    class="bg-red-600 btn-outline btn-sm" 
                                    :disabled="$storesrequisition->status!=='P'"
                                />
                            </div>
                        @endif
                
                        @if($storesrequisition->status=== 'O')
                            <div>
                                <x-button label="❌"
                                    wire:click="initiaterecall('{{$storesrequisition->storesrequisition_uuid}}')" 
                                    wire:confirm="Do you want to recall delivery by rejection?" 
                                    spinner 
                                    class="bg-red-600 btn-outline btn-sm" 
                                    :disabled="$storesrequisition->status!=='O'"
                                />
                            </div>
                        @endif        
                    @endhaspermission                        
                    </div>
                @endscope

                <x-slot:empty>
                    <x-alert class="alert-error" title="No departmental requests found. Visit ICT to make sure you are assigned to a department." />
                </x-slot:empty>
            </x-table>
        </x-card>    
    </div>

    <x-modal wire:model="addrequisitionmodal"  title="STORES REQUISITION FORM">
        <x-form wire:submit="sendrequisition">
            <div class="grid  gap-4" separator>
                <div class="grid justify-items-end">
                    <x-button icon="o-plus-small" 
                        wire:click="addrequisitionitem"
                        wire:confirm="Do you want to add another item?" 
                        class="text-blue-500 btn-outline btn-sm" 
                        spinner
                    />
                </div>
                @foreach($itemfields as $itemindex => $itemfield)
                <div>
                    <div>
                        Item No.{{$itemindex+1}}
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input  wire:model.live="itemfields.{{$itemindex}}.itemdetail" label="Item detail"></x-input>
                        <x-input  wire:model.live="itemfields.{{$itemindex}}.requiredquantity" label=" Required quantity"></x-input>
                    </div>
                </div>
                @endforeach
                <x-input  wire:model.live="purposeofrequisition" label="Purpose of requisition"></x-input>           
                @hasrole('Acting HOD')
                    <div></div>
                    <div>
                        <x-select :options="$hodassigneesmap" wire:model.live.debounce="assignedHodId"/>
                    </div>
                @endhasrole
                <div></div>
            </div>
            <x-slot:actions>
                <x-button label="Send" type="submit" class="btn-primary" spinner="sendrequisition" /> 
            </x-slot:actions>
        </x-form>
    </x-modal> 

    <x-modal wire:model="viewrequisitionmodal"  title="STORES REQUISITION VIEW NOTE">
        <div class="grid  gap-4" separator>
            @foreach($viewfields as $itemindex => $field)
            <div class="grid">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-input  placeholder="{{$field['itemdetail']}}" label="Item No:{{$itemindex+1}}" disabled class="text-black bg-gray-100 font-bold"></x-input>
                    <x-input  placeholder="{{$field['requiredquantity']}}" label=" Required quantity" disabled class="text-blue-900 bg-gray-100 font-bold"></x-input>
                    @if(isset($field['issuedquantity']))
                        <x-input  placeholder="{{$field['issuedquantity']}}" label=" Issued quantity" disabled class="text-blue-900 bg-gray-100 font-bold"></x-input>
                    @else
                        <x-input  placeholder="Not issued yet" label=" Issued quantity" disabled class="text-red-900 bg-gray-100 font-bold"></x-input>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </x-modal>  

@haspermission('storesrequisitions.access')
    <x-modal wire:model="acceptancerequisitionmodal"  title="DELIVERY {{$isaccepted==true?'APPROVAL':'REJECTION'}} FORM">
        <x-form wire:submit="acceptrequisition">        
            <div class="grid  gap-4" separator>
                @foreach($deliveryfields as $itemindex => $itemfield)
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-input  wire:model.live="deliveryfields.{{$itemindex}}.itemdetail" label="Item No:{{$itemindex+1}}" disabled></x-input>
                        <x-input  wire:model.live="deliveryfields.{{$itemindex}}.requiredquantity" label=" Required quantity" disabled></x-input>
                        <x-input  wire:model.live="deliveryfields.{{$itemindex}}.issuedquantity" label=" Issued quantity" disabled></x-input>
                    </div>
                </div>
                @endforeach
            </div>            
            <x-slot:actions>
                <x-button label="Proceed" type="submit" class="btn-primary" spinner="acceptrequisition" /> 
            </x-slot:actions>
        </x-form>
    </x-modal> 

    <x-modal wire:model="recallrequisitionmodal"  title="DELIVERY RECALL FORM">
        <x-form wire:submit="recallrequisition">        
            <div class="grid  gap-4" separator>
                @foreach($deliveryfields as $itemindex => $itemfield)
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-input  wire:model.live="deliveryfields.{{$itemindex}}.itemdetail" label="Item No:{{$itemindex+1}}" disabled></x-input>
                        <x-input  wire:model.live="deliveryfields.{{$itemindex}}.requiredquantity" label=" Required quantity" disabled></x-input>
                    </div>
                </div>
                @endforeach
            </div>            
            <x-slot:actions>
                <x-button label="Recall" type="submit" class="btn-primary" spinner="recallrequisition" /> 
            </x-slot:actions>
        </x-form>
    </x-modal>
@endhaspermission   

</div>