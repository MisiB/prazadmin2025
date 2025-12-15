@php
    use Carbon\Carbon;
@endphp 
<div>
 
    <x-modulewelcomebanner :breadcrumbs="$breadcrumbs"/>
 
    <!--Leave Balance Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-10">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-8 mb-4">
                <div class="p-3 bg-gradient-to-br from-green-300 to-green-600 rounded-xl shadow-lg shadow-purple-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div>                    
                    <div class="grid grid-cols-4 gap-12">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $leavestatementbalances['vacation'] }}</div>
                            <div class="text-xs text-gray-600">Vacation</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $leavestatementbalances['annual'] }}</div>
                            <div class="text-xs text-gray-600">Annual</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $leavestatementbalances['study'] }}</div>
                            <div class="text-xs text-gray-600">Study </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-3 bg-gradient-to-br from-green-300 to-green-600 rounded-xl shadow-lg shadow-purple-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div>                    
                    <div class="grid grid-cols-4 gap-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $leavestatementbalances['sick'] }}</div>
                            <div class="text-xs text-gray-600">Sick</div>
                        </div>
                        @if(strtolower($this->user->gender)==='f')
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $leavestatementbalances['maternity'] }}</div>
                            <div class="text-xs text-gray-600">Maternity</div>
                        </div>
                        @endif
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $leavestatementbalances['compassionate'] }}</div>
                            <div class="text-xs text-gray-600">Compassionate </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 my-8">
        <x-card class="shadow-md bg-gradient-to-tl from-gray-100 to-white rounded-xl">
            <div class="flex items-center space-x-3">
                <div class="grid grid-flow-row gap-2">
                    <div class="grid grid-flow-col gap-2">
                        <div class="bg-yellow-200 p-3 rounded-full">
                            <x-icon name="o-clock" class="w-8 h-8 text-blue-600"/>
                        </div>
                        <div class="text-sm text-gray-600 tracking-wide mt-4">Pending</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-gray-600 pl-3">{{ $totalpending }}</div>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card class="shadow-md bg-gradient-to-tl from-gray-100 to-white rounded-xl">
            <div class="flex items-center space-x-3">
                <div class="grid grid-flow-row gap-2">
                    <div class="grid grid-flow-col gap-2">
                        <div class="bg-blue-200 p-3 rounded-full">
                            <x-icon name="o-check" class="w-8 h-8 text-green-600"/>
                        </div>
                        <div class="text-sm text-gray-600 tracking-wide mt-4">Approved</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-gray-600 pl-3">{{ $totalapproved }}</div>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card class="shadow-md bg-gradient-to-tl from-gray-100 to-white rounded-xl">
            <div class="flex items-center space-x-3">
                <div class="grid grid-flow-row gap-2">
                    <div class="grid grid-flow-col gap-1">
                        <div class="bg-red-700 p-3 rounded-full">
                            <x-icon name="c-arrow-left-end-on-rectangle" class="w-8 h-8 text-white"/>
                        </div>

                        <div class="text-sm text-gray-600 tracking-wide mt-4">Rejected</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-gray-600 pl-3">{{ $totalrejected }}</div>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card class="shadow-md bg-gradient-to-tl from-gray-100 to-white rounded-xl">
            <div class="flex items-center space-x-3">

                <div class="grid grid-flow-row gap-2">
                    <div class="grid grid-flow-col gap-1">
                        <div class="bg-blue-700 p-3 rounded-full">
                            <x-icon name="o-clock" class="w-8 h-8 text-white"/>
                        </div>
                        <div class="text-sm text-gray-600 tracking-wide mt-4">Cancelled</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-gray-600 pl-3">{{ $totalcancelled }}</div>
                    </div>

                </div>
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
            {{$leaverequests->links()}}

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
                        <x-select :options="$hodassigneesmap" wire:model.live="assignedhodid"  label="Assign Acting Person" option-label="name" option-value="id" placeholder="Select Acting Person"/>
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