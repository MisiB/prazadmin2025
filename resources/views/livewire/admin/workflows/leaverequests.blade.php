@php
    use Carbon\Carbon;
@endphp
<div>

    <x-modulewelcomebanner :breadcrumbs="$breadcrumbs"/>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 my-8">
        <x-card class="border-2 border-blue-200  shadow-sm shadow-blue-200 bg-gradient-to-bl from-white to-gray-200">
            <div class="flex items-center space-x-3">
                <div class="bg-yellow-200 p-3 rounded-full">
                    <x-icon name="o-clock" class="w-8 h-8 text-blue-600"/>
                </div>
                <div class="text-4xl font-bold text-gray-600">{{ $totalpending }}</div>
                <div class="text-sm text-gray-600 tracking-wide">Pending</div>
            </div>
        </x-card>

        <x-card class="border-2 border-blue-200  shadow-sm shadow-blue-200 bg-gradient-to-bl from-white to-gray-200">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-200 p-3 rounded-full">
                    <x-icon name="o-check" class="w-8 h-8 text-green-600"/>
                </div>
                <div class="text-4xl font-bold text-gray-600">{{ $totalapproved }}</div>
                <div class="text-sm text-gray-600 tracking-wide">Approved</div>
            </div>
        </x-card>

        <x-card class="border-2 border-blue-200  shadow-sm shadow-blue-200 bg-gradient-to-bl from-white to-gray-200">
            <div class="flex items-center space-x-3">
                <div class="bg-red-700 p-3 rounded-full">
                    <x-icon name="c-arrow-left-end-on-rectangle" class="w-8 h-8 text-white"/>
                </div>
                <div class="text-4xl font-bold text-gray-600">{{ $totalrejected }}</div>
                <div class="text-sm text-gray-600 tracking-wide">Rejected</div>
            </div>
        </x-card>
        <x-card class="border-2 border-blue-200  shadow-sm shadow-blue-200 bg-gradient-to-bl from-white to-gray-200">
            <div class="flex items-center space-x-3">
                <div class="bg-blue-700 p-3 rounded-full">
                    <x-icon name="o-clock" class="w-8 h-8 text-white"/>
                </div>
                <div class="text-4xl font-bold text-gray-600">{{ $totalcancelled }}</div>
                <div class="text-sm text-gray-600 tracking-wide">Cancelled</div>
            </div>
        </x-card>
    </div>

    <div>
        <x-card title="My Leave Requests" separator class="mt-5 border-2 border-gray-200">
            <x-slot:menu>
                <x-input placeholder="Search by emailed REF ..." wire:model.live="searchuuid"/>
                <x-select wire:model.live="statusfilter" placeholder="Filter by status" :options="$statuslist" option-label="name" option-value="id" />
                <x-button icon="o-plus" label="Add Leave Request" wire:click="initiateleaveaddition"
                class="bg-gradient-to-bl from-blue-600 to-blue-800 shadow-md shadow-gray-200 rounded-lg text-white"/>
            </x-slot:menu>
            
            {{$leaverequests->links()}}
            <x-table :headers="$headers" :rows="$leaverequests">
                @scope('cell_status', $leaverequest)
                    @if($leaverequest->status=='A')
                        <span class="badge bg-gradient-to-b from-green-300 to-green-800 text-white">Approved</span>
                    @elseif($leaverequest->status=='P')
                        <span class="badge bg-gradient-to-b from-yellow-200 to-yellow-500 text-white">Pending</span>
                    @elseif($leaverequest->status=='C')
                        <span class="badge bg-gradient-to-b from-blue-300 to-blue-800 text-white">Cancelled</span>
                    @else
                        <span class="badge bg-gradient-to-b from-red-400 to-red-800 text-white">Rejected</span>
                    @endif
                @endscope 
                @scope('cell_hod',$leaverequest)   
                    <span>{{$leaverequest->hod?->name}} {{$leaverequest->hod?->surname??'-'}}</span>
                @endscope  
                @scope('cell_approver',$leaverequest)   
                    <span>{{$this->leaverequestService->getleaverequestapproval($leaverequest->leaverequestuuid)->user->name." ".$this->leaverequestService->getleaverequestapproval($leaverequest->leaverequestuuid)->user->surname}}</span>
                @endscope               
                @scope('actions', $leaverequest)
                    
                    @if(Carbon::parse($leaverequest->startdate) > now())
                        <div class="flex space-x-2">
                            <x-button icon="o-cog-6-tooth" 
                                wire:click="cancelrequest('{{$leaverequest->leaverequestuuid}}')"
                                wire:confirm="Do you want to recall your request?" 
                                class="text-green-500 btn-outline btn-sm" 
                                spinner 
                                :disabled=" $leaverequest->status!=='P' && $leaverequest->status!=='A' "
                            />
                        </div>
                    @else
                        <div class="flex space-x-2">
                            <x-button icon="o-cog-6-tooth" 
                                wire:click="cancelrequest('{{$leaverequest->leaverequestuuid}}')"
                                wire:confirm="Do you want to recall your request?" 
                                class="text-green-500 btn-outline btn-sm" 
                                spinner 
                                :disabled=" $leaverequest->status!=='P' "
                            />
                        </div>
                    @endif
                @endscope                
                <x-slot:empty>
                    <x-alert class="alert-error" title="No leave requests found." />
                </x-slot:empty>
            </x-table>
        </x-card>    
    </div>

    <x-modal wire:model="addleaverequestmodal"  title="Draft Leave Request Form" box-class="max-w-4xl">
        <x-form wire:submit="sendleaverequest" >
            <div class="grid grid-cols-2 gap-4" separator>
                <x-input class="col-span-1" wire:model.live="firstname" label="Firstname" readonly></x-input>
                <x-input class="col-span-1" wire:model.live="surname" label="Surname" readonly></x-input>
                <x-input class="col-span-1" wire:model.live="employeenumber" label="Employee Number"></x-input>
                <x-input class="col-span-1" wire:model.live="leaveapprovername" label="Leave Request Approver" readonly></x-input>
                <x-select class="col-span-1" :options="$leavetypesmap" wire:model.live="selectedleavetypeid" label="Selected Leave type" option-label="name" option-value="id" placeholder="Select leave type"/>
                <x-datepicker class="col-span-1" wire:model.live="starttoenddate" label="Start date - End date (Range)" :config="$dateRangeConfig"></x-datepicker>
                <x-input class="col-span-1" wire:model.live.debounce="daysappliedfor" label="No of days applied for" type="number" readonly/>
                <x-datepicker class="col-span-1" wire:model.live.debounce="returndate" label="Return date" readonly ></x-datepicker>
                <x-input class="col-span-1" wire:model.live="reasonforleave" label="Reason of leave" ></x-input>
                <div class="col-span-1 grid justify-center">
                    <x-file wire:model.live="supportingdoc" label="Supporting Document (Optional)" accept="application/pdf"/>
                </div>
                <x-textarea class="col-span-1" wire:model.live="addressonleave" label="Address on leave" rows="4"></x-textarea>
                @hasrole('Acting HOD')
                    <div class="col-span-1">
                        <x-select :options="$hodassigneesmap" wire:model.live="assignedhodid"  label="Assign HOD" option-label="name" option-value="id" placeholder="Select Acting HOD"/>
                    </div>
                @endhasrole
                <div></div>   
            </div>
            
            <x-slot:actions>
                <x-button label="Send" type="submit" spinner="sendleaverequest" 
                class="bg-gradient-to-bl from-blue-600 to-blue-800 shadow-md shadow-gray-200 rounded-lg text-white"/>
            </x-slot:actions>
        </x-form>
    </x-modal>  

</div>