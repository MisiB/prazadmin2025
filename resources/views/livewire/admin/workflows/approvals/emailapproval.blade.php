<div class="container mx-auto px-6 py-8">
    <!-- Main Wrapper div to ensure single root element -->
    <div class="bg-white rounded-lg shadow-lg p-6 space-y-8 max-w-7xl mx-auto">

        <!-- Welcome Message -->
        <div class="text-center">
            <div class="text-3xl font-extrabold text-gray-900 leading-tight">
                WELCOME {{ $approver->gender=='M'?'MR':'MRS' }} {{ strtoupper($approver->surname) }} TO OUR PRAZ LEAVE APPROVALS SERVICE PORTAL
            </div>
        </div>

        <!-- Leave Application Header -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-4 justify-center mt-6">
            <div class="col-span-2 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-xl font-semibold text-gray-800">Leave Application Submission</div>
            <div class="col-span-2 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-lg font-medium text-gray-600">PRAZ</div>
        </div>
        <div class="text-center text-gray-500 mt-1 text-sm">
            (completed by employee online)
        </div>

        <!-- Employee Details Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-4 mt-6">
            <!-- Row 1: Firstname and Surname -->
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-gray-600">Firstname: {{ $employee->name }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-gray-600">Surname: {{ $employee->name }}</div>

            <!-- Row 2: Employee No and Address while on leave -->
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-gray-600">Employee No: {{ $employee->name }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-gray-600">Address while on leave: {{ $employee->name }}</div>

            <!-- Row 3: Department -->
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-gray-600">Department: NULL</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3">
                <x-button wire:click="download('{{ $requestrecord->attachment_src }}')" class="btn-primary" label="Download Attachment"/>
            </div> <!-- Empty div to maintain two-column layout -->

            <!-- Row 4: Leave Dates -->
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-gray-600">Leave Dates From: {{ $requestrecord->startdate }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-gray-600">To: {{ $requestrecord->enddate }}</div>

            <!-- Row 5: Number of Days Applied For and Date of Return -->
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-gray-600">No of Days Applied For: {{ $requestrecord->daysappliedfor }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-gray-600">Date of Return: {{ $requestrecord->returndate }}</div>
        </div>

        <!-- Decision Form Section -->
        <x-form wire:submit="processApplication">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-6 justify-center mt-6">
                    <!-- Leave Type and Reason -->
                    <div class="col-span-1 sm:col-span-2 lg:col-start-3 lg:col-span-4 text-gray-700 font-medium">
                        {{ $leavetyperecord->name }} Leave Application
                        <div class="text-sm mt-2 text-gray-500">Reason: {{ $requestrecord->reasonforleave }}</div>
                    </div>

                    <!-- Employee Signature -->
                    <div class="col-span-1 sm:col-span-2 lg:col-start-7 lg:col-span-4 grid justify-center">
                        <div class="text-center text-gray-600 font-medium">Employee Signature:</div>
                        <img src="{{ $requestrecord->signature }}" class="bg-gray-100 h-16 w-full rounded-lg mt-2 shadow-sm" />
                    </div>
                </div>
                <!-- HOD's Comment and Dept Manager/CEO Signature Section -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-6 mt-8 mx-6">
                    <!-- HOD's Comment -->
                    <div class="col-span-1 sm:col-span-2 lg:col-span-10">
                        <x-textarea wire:model.live.debounce="comment" hint="HOD'S Comment" rows="4" class="border border-gray-300 rounded-lg p-3 text-gray-700 focus:ring-2 focus:ring-blue-500 transition duration-300" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-6 mt-8 mx-6">
                    <!-- Dept Manager/CEO Signature -->
                    <div class="col-span-1 sm:col-span-2 lg:col-span-10 text-center">
                        <x-signature wire:model.live.debounce="signature" hint="DEPT MANAGER/CEO. Please, sign here" class="col-span-2 h-32 bg-gray-100 border border-gray-300 rounded-lg p-3 shadow-sm transition duration-300" />
                    </div>
                    
                </div>

                <!-- Actions (Approve/Reject Buttons) -->
                <x-slot:actions>
                    <div class="flex justify-center gap-8 mt-6">
                        @if($approvalrecord->action==='R')
                            <x-button label="Reject" icon="o-x-mark" wire:click="$set('isapproved',false)" type="submit" spinner="processApplication" class="btn-danger hover:bg-red-700 transition-all duration-300 ease-in-out transform hover:scale-105 shadow-lg" disabled/>
                        @else
                            <x-button label="Reject" icon="o-x-mark" wire:click="$set('isapproved',false)" type="submit" spinner="processApplication" class="btn-danger hover:bg-red-700 transition-all duration-300 ease-in-out transform hover:scale-105 shadow-lg" />
                        @endif
                        @if($approvalrecord->action==='A')
                            <x-button label="Approve" icon="o-check" wire:click="$set('isapproved',true)" class="btn-primary hover:bg-blue-700 transition-all duration-300 ease-in-out transform hover:scale-105 shadow-lg" type="submit" spinner="processApplication" disabled/>
                        @else
                            <x-button label="Approve" icon="o-check" wire:click="$set('isapproved',true)" class="btn-primary hover:bg-blue-700 transition-all duration-300 ease-in-out transform hover:scale-105 shadow-lg" type="submit" spinner="processApplication"/>
                        @endif
                    </div>
                </x-slot:actions>
        </x-form>
    </div>
</div>