<div class="container mx-auto px-4 py-8 bg-gradient-to-tr from-blue-50 via-green-50 to-blue-100 min-h-screen">
    <div class="bg-white rounded-3xl shadow-2xl p-8 space-y-8 max-w-7xl mx-auto border border-blue-100">

        <!-- Welcome Message -->
        <div class="text-center">
            <div class="text-4xl font-bold text-blue-900 leading-tight">
                LEAVE REQUEST NOTE
            </div>
        </div>

        <div class="text-center text-gray-500 mt-1 text-sm">
            (Online approval by {{ $approver->gender=='M'?'mr':'mrs' }} {{ strtolower($approver->surname) }})
        </div>
        
        <!-- Leave Application Header -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-4 justify-center mt-6">
            <div class="col-span-2 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-xl font-semibold text-green-700">📄 Leave Application Submission</div>
            <div class="col-span-2 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-lg font-semibold text-blue-700"> 📃Status: <span class="font-bold">{{ $requestrecord->status === 'P' ? '⏳ Pending' : ( $requestrecord->status === 'A'?'✅ Approved': ($requestrecord->status === 'C'?'🔙 Cancelled':'❌ Rejected' )) }}</span></div>
        </div>

        <!-- Employee Details Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-4 mt-6">
            <!-- Row 1: Firstname and Surname -->
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-base text-gray-700">👤 Firstname: {{ $employee->name }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-base text-gray-700">👤 Surname: {{ $employee->surname }}</div>

            <!-- Row 2: Employee No and Address while on leave -->
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-base text-gray-700">🆔 Employee No: {{ $employee->name }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-base text-gray-700">🏠 Address while on leave: {{ $requestrecord->addressonleave }}</div>

            <!-- Row 3: Department -->
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-base text-gray-700">🏢 Department: {{ $departmentname }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3">
                <x-button wire:click="$set('viewattachmentmodal', true)" label="📎 View Attachment"
                class="bg-gradient-to-bl from-blue-600 to-blue-800 shadow-md shadow-gray-200 rounded-lg text-white px-4 py-2"/>
            </div>

            <!-- Row 4: Leave Dates -->
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-base text-gray-700">📅 Leave Dates From: {{ $requestrecord->startdate }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-base text-gray-700">📅 To: {{ $requestrecord->enddate }}</div>

            <!-- Row 5: Number of Days Applied For and Date of Return -->
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-base text-gray-700">🔢 No of Days Applied For: {{ $requestrecord->daysappliedfor }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-base text-gray-700">↩️ Date of Return: {{ $requestrecord->returndate }}</div>
        </div>

        <!-- Decision Form Section -->
        <x-form wire:submit="processApplication">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-6 justify-center mt-6">
                    <!-- Leave Type and Reason -->
                    <div class="col-span-1 sm:col-span-2 lg:col-start-3 lg:col-span-4 text-blue-900 font-semibold">
                        🏖️ {{ $leavetyperecord->name }} Leave Application
                        <div class="text-sm mt-2 text-gray-600">📝 Reason: {{ $requestrecord->reasonforleave }}</div>
                    </div>
                </div>
            @if($requestrecord->status != 'C')
                <!-- HOD's Comment and Dept Manager/CEO Signature Section -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-6 mt-8 mx-6">
                    <!-- HOD's Comment -->
                    <div class="col-span-1 sm:col-span-2 lg:col-span-10">
                        <x-textarea wire:model.live.debounce="comment" 
                            hint="💬 HOD'S Comment" 
                            rows="4"
                            class="border border-gray-300 rounded-lg p-3 text-gray-700 focus:ring-2 focus:ring-blue-500 transition duration-300"
                            :disabled="$approvalrecord->decision !=null && $approvalrecord->decision == true"
                        />
                    </div>
                </div>


                <!-- Actions (Approve/Reject Buttons) -->
                <x-slot:actions>
                    <div class="flex flex-wrap justify-center gap-6 mt-8">

                        {{-- Reject Button --}}            
                        <x-button label="❌ Reject"
                            wire:click="$set('isapproved',false)"
                            type="submit"
                            spinner="processApplication"
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-300 ease-in-out transform hover:scale-105 shadow-md"
                            :disabled="$approvalrecord->decision !=null && $approvalrecord->decision == true"
                        />

                        {{-- Approve Button --}}
                        <x-button label="✅ Approve"
                            wire:click="$set('isapproved',true)"
                            type="submit"
                            spinner="processApplication"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-300 ease-in-out transform hover:scale-105 shadow-md"
                            :disabled="$approvalrecord->decision !=null && $approvalrecord->decision == false"
                        />
                    </div>
                </x-slot:actions>
            @endif
        </x-form>

        <x-modal wire:model="viewattachmentmodal" title="📄 PDF PREVIEW" box-class="max-w-4xl">    
            @if(!empty($attachmenturl))
                <iframe src="{{ $attachmenturl }}" frameborder="0" allowfullscreen></iframe>
            @else
                <div class="flex absolute inset-0 justify-center items-center bg-gray-100 rounded-lg">
                    <div class="text-center">
                        <x-icon name="o-document" class="mx-auto w-12 h-12 text-gray-400" />
                        <p class="mt-2 text-sm text-gray-600">Document not available</p>
                    </div>
                </div>
            @endif
        </x-modal>
    </div>
</div>