<div>

    <x-modulewelcomebanner :breadcrumbs="$breadcrumbs"/>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 mt-4">
        <x-card>
            <div class="flex items-center space-x-3">
                <div class="bg-blue-200 p-3 rounded-full">
                    <x-icon name="o-check" class="w-8 h-8 text-green-600"/>
                </div>
                <div class="text-xl font-bold text-gray-700">{{ $totalapproved }}</div>
                <div class="text-sm text-gray-600">Awaiting Delivery</div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center space-x-3">
                <div class="bg-blue-400 p-3 rounded-full">
                    <x-icon name="o-book-open" class="w-8 h-8 text-yellow-600"/>
                </div>
                <div class="text-xl font-bold text-gray-700">{{ $totalopened }}</div>
                <div class="text-sm text-gray-600">Initiated Deliveries</div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center space-x-3">
                <div class="bg-blue-400 p-3 rounded-full">
                    <x-icon name="o-book-open" class="w-8 h-8 text-yellow-600"/>
                </div>
                <div class="text-xl font-bold text-gray-700">{{ $totalawaiting }}</div>
                <div class="text-sm text-gray-600">Awaiting Admin Clearance</div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center space-x-3">
                <div class="bg-green-200 p-3 rounded-full">
                    <x-icon name="m-clipboard-document-list" class="w-8 h-8 text-yellow-600"/>
                </div>
                <div class="text-xl font-bold text-gray-700">{{ $totaldelivered }}</div>
                <div class="text-sm text-gray-600">Awaiting Receiver Acceptance</div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center space-x-3">
                <div class="bg-green-700 p-3 rounded-full">
                    <x-icon name="o-hand-thumb-up" class="w-8 h-8 text-white"/>
                </div>
                <div class="text-xl font-bold text-gray-700">{{ $totalrecieved }}</div>
                <div class="text-sm text-gray-600">Accepted Requisitions</div>
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
        <x-input placeholder="Search by reference..." wire:model.live.debounce="searchuuid"/>          
    </div>
    
    <div class="mt-5">
        <x-tabs wire:model="storestabs">
            <x-tab name="delivery-tab">
                <x-slot:label>  
                    Awaiting Delivery
                </x-slot:label>

                <div>
                    <x-card title="Stores Requisitions Awaiting Delivery" separator class="mt-5 border-2 border-gray-200">
                        <x-slot:menu>
                            <x-select wire:model.live="statuslist" placeholder="Filter by status" :options="$statuslist" option-label="name" option-value="id" />
                        </x-slot:menu>

                        <x-table :headers="$headersforapproved" :rows="$storesrequisitionsawaitingdelivery">
                            @scope('cell_itembanner', $storesrequisition)
                                <img src="{{asset('images/img_placeholder.jpg')}}" alt="" class="w-[60px] h-auto">
                            @endscope
                            @scope('cell_itemscount', $storesrequisition)
                                <span>{{collect(json_decode($storesrequisition->requisitionitems,true))->count()}}</span>
                            @endscope                
                            @scope('cell_status', $storesrequisition)
                                @if($storesrequisition->status=='A')
                                    <span class="badge badge-warning">Approved</span>
                                @elseif($storesrequisition->status=='O')
                                    <span class="badge badge-warning">Opened</span>
                                @elseif($storesrequisition->status=='V')
                                    <span class="badge badge-warning">Verification</span>
                                @elseif($storesrequisition->status=='D')
                                    <span class="badge badge-warning">Delivered</span>
                                @elseif($storesrequisition->status=='C')
                                    <span class="badge badge-success">Received</span>
                                @else
                                    <span class="badge badge-error">Rejected</span>
                                @endif
                            @endscope
                            @scope('cell_initiator',$storesrequisition)   
                                <span>{{$storesrequisition->initiator->name." ".$storesrequisition->initiator->surname}}</span>
                            @endscope  
                                
                            
                            @scope('actions', $storesrequisition)
                                <div class="grid grid-flow-col space-x-2">
                                    
                                    <x-button icon="o-eye" 
                                        wire:click="viewrequisition('{{$storesrequisition->storesrequisition_uuid}}', '{{$storesrequisition->initiator_id}}')" 
                                        spinner class="text-blue-500 btn-outline btn-sm" 
                                    />
                                    @hasrole('Admin Issuer')
                                        <x-button icon="o-folder-plus" 
                                            wire:click="openrequisition('{{$storesrequisition->storesrequisition_uuid}}', '{{$storesrequisition->initiator_id}}')" 
                                            wire:confirm="Do you want to claim delivery?" 
                                            spinner 
                                            class="text-green-500  btn-outline btn-sm" 
                                            :disabled="$storesrequisition->status!=='A'"
                                        />
                                    
                                        <x-button icon="o-lock-closed"
                                            wire:click="initiatedelivery('{{$storesrequisition->storesrequisition_uuid}}','{{$storesrequisition->initiator_id}}')" 
                                            wire:confirm="Confirm delivery?" 
                                            spinner 
                                            class="text-zinc-500 btn-outline btn-sm" 
                                            :disabled="$storesrequisition->status!=='O'"
                                        />
                                    @endhasrole
                                    
                                </div>
                            @endscope    
                                      
                            <x-slot:empty>
                                <x-alert class="alert-error" title="No approved leave requests found." />
                            </x-slot:empty>
                        </x-table>
                    </x-card>    
                </div>    

            </x-tab>    

            <x-tab name="opened-tab">
                <x-slot:label>  
                    Initiated Deliveries
                </x-slot:label>

                <div>
                    <x-card title="Initiated Stores Requisitions Deliveries" separator class="mt-5 border-2 border-gray-200">

                        <x-table :headers="$headersforapproved" :rows="$storesrequisitionsopened">
                            @scope('cell_itembanner', $storesrequisition)
                                <img src="{{asset('images/img_placeholder.jpg')}}" alt="" class="w-[60px] h-auto">
                            @endscope
                            @scope('cell_itemscount', $storesrequisition)
                                <span>{{collect(json_decode($storesrequisition->requisitionitems,true))->count()}}</span>
                            @endscope                
                            @scope('cell_status', $storesrequisition)
                                @if($storesrequisition->status=='A')
                                    <span class="badge badge-warning">Approved</span>
                                @elseif($storesrequisition->status=='O')
                                    <span class="badge badge-warning">Opened</span>
                                @elseif($storesrequisition->status=='V')
                                    <span class="badge badge-warning">Verification</span>
                                @elseif($storesrequisition->status=='D')
                                    <span class="badge badge-warning">Delivered</span>
                                @elseif($storesrequisition->status=='C')
                                    <span class="badge badge-success">Received</span>
                                @else
                                    <span class="badge badge-error">Rejected</span>
                                @endif
                            @endscope
                            @scope('cell_initiator',$storesrequisition)   
                                <span>{{$storesrequisition->initiator->name." ".$storesrequisition->initiator->surname}}</span>
                            @endscope  
                                
                            @scope('actions', $storesrequisition)
                                <div class="flex space-x-2">
                                    <x-button icon="o-eye" 
                                        wire:click="viewrequisition('{{$storesrequisition->storesrequisition_uuid}}', '{{$storesrequisition->initiator_id}}')" 
                                        spinner class="text-blue-500 btn-outline btn-sm" 
                                    />
                                    @hasrole('Admin Issuer')
                                        <x-button icon="o-lock-closed"
                                            wire:click="initiateverification('{{$storesrequisition->storesrequisition_uuid}}','{{$storesrequisition->initiator_id}}')" 
                                            wire:confirm="Confirm request of delivery verification?" 
                                            spinner 
                                            class="text-zinc-500 btn-outline btn-sm" 
                                            :disabled="$storesrequisition->status!=='O'"
                                        />
                                    @endhasrole
                                </div>
                            @endscope        
                            <x-slot:empty>
                                <x-alert class="alert-error" title="No approved leave requests found." />
                            </x-slot:empty>
                        </x-table>
                    </x-card>    
                </div>    

            </x-tab>   

            <x-tab name="adminclearance-tab">
                <x-slot:label>  
                    Awaiting Admin Clearance
                </x-slot:label>

                <div>
                    <x-card title="Stores Requisitions Awaiting Delivery" separator class="mt-5 border-2 border-gray-200">

                        <x-table :headers="$headersforapproved" :rows="$storesrequisitionsawaitingclearance">
                            @scope('cell_itembanner', $storesrequisition)
                                <img src="{{asset('images/img_placeholder.jpg')}}" alt="" class="w-[60px] h-auto">
                            @endscope
                            @scope('cell_itemscount', $storesrequisition)
                                <span>{{collect(json_decode($storesrequisition->requisitionitems,true))->count()}}</span>
                            @endscope                
                            @scope('cell_status', $storesrequisition)
                                @if($storesrequisition->status=='A')
                                    <span class="badge badge-warning">Approved</span>
                                @elseif($storesrequisition->status=='O')
                                    <span class="badge badge-warning">Opened</span>
                                @elseif($storesrequisition->status=='V')
                                    <span class="badge badge-warning">Verification</span>
                                @elseif($storesrequisition->status=='D')
                                    <span class="badge badge-warning">Delivered</span>
                                @elseif($storesrequisition->status=='C')
                                    <span class="badge badge-success">Received</span>
                                @else
                                    <span class="badge badge-error">Rejected</span>
                                @endif
                            @endscope
                            @scope('cell_initiator',$storesrequisition)   
                                <span>{{$storesrequisition->initiator->name." ".$storesrequisition->initiator->surname}}</span>
                            @endscope  
                                
                            @scope('actions', $storesrequisition)
                                <div class="flex space-x-2">
                                    <x-button icon="o-eye" 
                                        wire:click="viewrequisition('{{$storesrequisition->storesrequisition_uuid}}', '{{$storesrequisition->initiator_id}}')" 
                                        spinner class="text-blue-500 btn-outline btn-sm" 
                                    />
                                    @hasrole('Admin Chair')
                                        <x-button label="✅"
                                            wire:click="initiatedelivery('{{$storesrequisition->storesrequisition_uuid}}','{{$storesrequisition->initiator_id}}', true)" 
                                            wire:confirm="Verify delivery?" 
                                            spinner 
                                            class="bg-green-600 btn-outline btn-sm" 
                                            :disabled="$storesrequisition->status!=='V'"
                                        />
                                        <x-button label="❌"
                                            wire:click="initiatedelivery('{{$storesrequisition->storesrequisition_uuid}}','{{$storesrequisition->initiator_id}}', false)" 
                                            wire:confirm="Do you want to reject delivery?" 
                                            spinner 
                                            class="bg-red-600 btn-outline btn-sm" 
                                            :disabled="$storesrequisition->status!=='V'"
                                        />
                                    @endhasrole
                                </div>
                            @endscope         
                            <x-slot:empty>
                                <x-alert class="alert-error" title="No approved leave requests found." />
                            </x-slot:empty>
                        </x-table>
                    </x-card>    
                </div>    

            </x-tab>   

            <x-tab name="delivered-tab">
                <x-slot:label>  
                    Awaiting Receiver Acceptance
                </x-slot:label>

                <div>
                    <x-card title="Stores Requisitions Awaiting Receiver Acceptance" separator class="mt-5 border-2 border-gray-200">

                        <x-table :headers="$headersforapproved" :rows="$storesrequisitionsdelivered">
                            @scope('cell_itembanner', $storesrequisition)
                                <img src="{{asset('images/img_placeholder.jpg')}}" alt="" class="w-[60px] h-auto">
                            @endscope
                            @scope('cell_itemscount', $storesrequisition)
                                <span>{{collect(json_decode($storesrequisition->requisitionitems,true))->count()}}</span>
                            @endscope                
                            @scope('cell_status', $storesrequisition)
                                @if($storesrequisition->status=='A')
                                    <span class="badge badge-warning">Approved</span>
                                @elseif($storesrequisition->status=='O')
                                    <span class="badge badge-warning">Opened</span>
                                @elseif($storesrequisition->status=='V')
                                    <span class="badge badge-warning">Verification</span>
                                @elseif($storesrequisition->status=='D')
                                    <span class="badge badge-warning">Delivered</span>
                                @elseif($storesrequisition->status=='C')
                                    <span class="badge badge-success">Received</span>
                                @else
                                    <span class="badge badge-error">Rejected</span>
                                @endif
                            @endscope
                            @scope('cell_initiator',$storesrequisition)   
                                <span>{{$storesrequisition->initiator->name." ".$storesrequisition->initiator->surname}}</span>
                            @endscope  
                                
                            @scope('actions', $storesrequisition)
                                <div class="flex space-x-2">
                                    <x-button icon="o-eye" 
                                        wire:click="viewrequisition('{{$storesrequisition->storesrequisition_uuid}}', '{{$storesrequisition->initiator_id}}')" 
                                        spinner class="text-blue-500 btn-outline btn-sm" 
                                    /> 
                                </div>
                            @endscope            
                            <x-slot:empty>
                                <x-alert class="alert-error" title="No approved leave requests found." />
                            </x-slot:empty>
                        </x-table>
                    </x-card>    
                </div>    

            </x-tab>  

            <x-tab name="completed-tab">
                <x-slot:label>  
                    Accepted Requistions
                </x-slot:label>
        
                <div>
                    <x-card title="Accepted Stores Requisitions" separator class="mt-5 border-2 border-gray-200">
                        <x-slot:menu>
                            <x-button label="Export"
                                wire:click="exportstoresrequisitionreport('C')"
                                wire:confirm="Confirm download?" 
                                spinner 
                                class="text-green-500  btn-outline btn-sm" 
                            />
                        </x-slot:menu>

                        <x-table :headers="$headersforapproved" :rows="$storesrequisitionsrecieved">
                            @scope('cell_itembanner', $storesrequisition)
                                <img src="{{asset('images/img_placeholder.jpg')}}" alt="" class="w-[60px] h-auto">
                            @endscope
                            @scope('cell_itemscount', $storesrequisition)
                                <span>{{collect(json_decode($storesrequisition->requisitionitems,true))->count()}}</span>
                            @endscope                
                            @scope('cell_status', $storesrequisition)
                                @if($storesrequisition->status=='A')
                                    <span class="badge badge-warning">Approved</span>
                                @elseif($storesrequisition->status=='O')
                                    <span class="badge badge-warning">Opened</span>
                                @elseif($storesrequisition->status=='V')
                                    <span class="badge badge-warning">Verification</span>
                                @elseif($storesrequisition->status=='D')
                                    <span class="badge badge-warning">Delivered</span>
                                @elseif($storesrequisition->status=='C')
                                    <span class="badge badge-success">Received</span>
                                @else
                                    <span class="badge badge-error">Rejected</span>
                                @endif
                            @endscope
                            @scope('cell_initiator',$storesrequisition)   
                                <span>{{$storesrequisition->initiator->name." ".$storesrequisition->initiator->surname}}</span>
                            @endscope  
                                
                            @scope('actions', $storesrequisition)
                                <div class="grid grid-flow-col space-x-2">                         
                                    <x-button icon="o-eye" 
                                        wire:click="viewrequisition('{{$storesrequisition->storesrequisition_uuid}}', '{{$storesrequisition->initiator_id}}')" 
                                        spinner class="text-blue-500 btn-outline btn-sm" 
                                    />
                                </div>
                            @endscope           
                            <x-slot:empty>
                                <x-alert class="alert-error" title="No approved leave requests found." />
                            </x-slot:empty>
                        </x-table>
                    </x-card>    
                </div>
            </x-tab>



            <x-tab name="rejetions-tab">
                <x-slot:label>  
                    Rejected Requisitions
                </x-slot:label>
        
                <div>
                    <x-card title="Rejected Stores Requisitions" separator class="mt-5 border-2 border-gray-200">

                        <x-table :headers="$headersforapproved" :rows="$storesrequisitionsrejected">
                            @scope('cell_itembanner', $storesrequisition)
                                <img src="{{asset('images/img_placeholder.jpg')}}" alt="" class="w-[60px] h-auto">
                            @endscope
                            @scope('cell_itemscount', $storesrequisition)
                                <span>{{collect(json_decode($storesrequisition->requisitionitems,true))->count()}}</span>
                            @endscope                
                            @scope('cell_status', $storesrequisition)
                                @if($storesrequisition->status=='A')
                                    <span class="badge badge-warning">Approved</span>
                                @elseif($storesrequisition->status=='O')
                                    <span class="badge badge-warning">Opened</span>
                                @elseif($storesrequisition->status=='V')
                                    <span class="badge badge-warning">Verification</span>
                                @elseif($storesrequisition->status=='D')
                                    <span class="badge badge-warning">Delivered</span>
                                @elseif($storesrequisition->status=='C')
                                    <span class="badge badge-success">Received</span>
                                @else
                                    <span class="badge badge-error">Rejected</span>
                                @endif
                            @endscope
                            @scope('cell_initiator',$storesrequisition)   
                                <span>{{$storesrequisition->initiator->name." ".$storesrequisition->initiator->surname}}</span>
                            @endscope  
                                
                            
                            @scope('actions', $storesrequisition)
                                <div class="grid grid-flow-col space-x-2">                         
                                    <x-button icon="o-eye" 
                                        wire:click="viewrequisition('{{$storesrequisition->storesrequisition_uuid}}', '{{$storesrequisition->initiator_id}}')" 
                                        spinner class="text-blue-500 btn-outline btn-sm" 
                                    />
                                </div>
                            @endscope    
                                      
                            <x-slot:empty>
                                <x-alert class="alert-error" title="No approved leave requests found." />
                            </x-slot:empty>
                        </x-table>
                    </x-card>    
                </div>
            </x-tab>            
            
        </x-tabs>
    </div>



 
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
    
    @hasrole('Admin Issuer')
    <x-modal wire:model="requisitionverificationmodal"  title="STORES REQUISITION DELIVERY FORM">
        <x-form wire:submit="sendrequisitionforverification">        
            <div class="grid  gap-4" separator>
                @foreach($deliveryfields as $itemindex => $itemfield)
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-input  wire:model.live="deliveryfields.{{$itemindex}}.itemdetail" label="Item No:{{$itemindex+1}}" disabled></x-input>
                        <x-input  wire:model.live="deliveryfields.{{$itemindex}}.requiredquantity" label=" Required quantity" disabled></x-input>
                        <x-input  wire:model.live="deliveryfields.{{$itemindex}}.issuedquantity" label=" Issued quantity"></x-input>
                    </div>
                </div>
                @endforeach
                <x-input  wire:model.live="issuercomment" label="Comment on the delivery details"></x-input>
            </div>            
            <x-slot:actions>
                <x-button label="Decline" type="button" class="btn-secondary" />
                <x-button label="Submit" type="submit" class="btn-primary" spinner="deliverrequisition" /> 
            </x-slot:actions>
        </x-form>
    </x-modal> 
    @endhasrole

    @hasrole('Admin Chair')
    <x-modal wire:model="deliveryrequisitionmodal"  title="DELIVERY VERIFICATION NOTE">
        <x-form wire:submit="deliverrequisition">        
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
                <x-input  wire:model.live="adminvalidatorcomment" label="Comment on the delivery details"></x-input>
            </div>            
            <x-slot:actions>
                <x-button label="Decline" type="button" class="btn-secondary" />
                <x-button label="Approve" type="submit" class="btn-primary" spinner="deliverrequisition" /> 
            </x-slot:actions>
        </x-form>
    </x-modal> 
    @endhasrole

</div>