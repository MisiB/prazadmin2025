<div> 


    <x-modulewelcomebanner :breadcrumbs="$breadcrumbs"/>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
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
    </div>

    <div>
        <x-card title="My Leave Requests" separator class="mt-5 border-2 border-gray-200">
            <x-slot:menu>
                <x-input wire:model.live="year" min="2020" max="{{ now()->year }}" placeholder="Select Year" type="number" />
                <x-select wire:model.live="statusfilter" placeholder="Filter by status" :options="$statuslist" option-label="name" option-value="id" />
                <x-button icon="o-plus" label="Add Leave Request"  wire:click="addleaverequestmodal=true"
                class="bg-gradient-to-bl from-blue-600 to-blue-800 shadow-md shadow-gray-200 rounded-lg text-white"/>
            </x-slot:menu>

            <x-table :headers="$headers" :rows="$leaverequests">
                @scope('cell_status', $leaverequest)
                    @if($leaverequest->status=='APPROVED')
                        <span class="badge badge-success">Approved</span>
                    @elseif($leaverequest->status=='PENDING')
                        <span class="badge badge-warning">Pending</span>
                    @else
                        <span class="badge badge-error">Rejected</span>
                    @endif
                @endscope 
                @scope('cell_hod',$leaverequest)   
                    <span>{{$leaverequest->hod->name." ".$leaverequest->hod->surname}}</span>
                @endscope               
                <x-slot:empty>
                    <x-alert class="alert-error" title="No leave requests found." />
                </x-slot:empty>
            </x-table>
        </x-card>    
    </div>

    <x-modal wire:model="addleaverequestmodal"  title="Draft Leave Request" box-class="max-w-4xl">
        <x-form wire:submit="sendleaverequest">
            <div class="grid grid-cols-2 gap-4" separator>
                <x-input class="col-span-1" wire:model.live="firstname" hint="Firstname" readonly></x-input>
                <x-input class="col-span-1" wire:model.live="surname" hint="Surname" readonly></x-input>
                <x-input class="col-span-1" wire:model.live="employeenumber" hint="Employee Number"></x-input>
                <x-input class="col-span-1" wire:model.live="leaveapprovername" hint="Leave Request Approver" readonly></x-input>
                <x-select class="col-span-1" :options="$leavetypesmap" wire:model.live="selectedleavetypeid" hint="Selected Leave type"/>
                <x-datepicker class="col-span-1" wire:model.live="starttoenddate" hint="Start date - End date (Range)" :config="$dateRangeConfig"></x-datepicker>
                <x-input class="col-span-1" wire:model.live.debounce="daysappliedfor" hint="No of days applied for" type="number" readonly/>
                <x-datepicker class="col-span-1" wire:model.live.debounce="returndate" hint="Return date" readonly ></x-datepicker>
                <x-input class="col-span-1" wire:model.live="reasonforleave" hint="Reason of leave" ></x-input>
                <div class="col-span-1 grid justify-center">
                    <x-file wire:model.live="supportingdoc" hint="Supporting Document (Optional)" accept="application/pdf"/>
                </div>
                <x-textarea class="col-span-1" wire:model.live="addressonleave" hint="Address on leave" rows="11"></x-textarea>
                <div class="col-span-1 h-32">
                    <x-signature wire:model.live="employeesignature" hint="EMPLOYEE. Please, sign here" class="h-full"/>   
                </div>
                @can('Approvalflow.Leaverequest.HOD')
                    
                    <div class="col-span-1"></div>
                    <div class="col-span-1">
                        <x-select :options="$hodassigneesmap" wire:model.live.debounce="assignedHodId"/>
                    </div>
                @endcan
                <div></div>   
            </div>
            
            <x-slot:actions>
                <x-button label="Send" type="submit" spinner="sendleaverequest" 
                
                class="bg-gradient-to-bl from-blue-600 to-blue-800 shadow-md shadow-gray-200 rounded-lg text-white"/>
            </x-slot:actions>
        </x-form>
    </x-modal>  

</div>
