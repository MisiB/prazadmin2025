<div class="container mx-auto px-4 py-8 bg-gradient-to-tr from-blue-50 via-green-50 to-blue-100 min-h-screen">
    <div class="bg-white rounded-3xl shadow-2xl p-8 space-y-8 max-w-7xl mx-auto border border-blue-100">

        <!-- Welcome Message -->
        <div class="text-center">
            <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-blue-900 tracking-tight leading-snug">
                STORES APPROVALS NOTE 
            </h1>
        </div>

        <div class="text-center text-gray-500 mt-1 text-sm italic">
            (Online approval by {{ $approver->gender ==='M' ? 'mr' : 'mrs' }} {{ strtolower($approver->surname) }})
        </div>
        <!-- Requisition Header -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-4 justify-center mt-6">
            <div class="col-span-2 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-lg sm:text-xl font-semibold  text-green-700">
                🧾 Stores Requisition Submission
            </div>
            <div class="col-span-2 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-lg font-semibold text-blue-700">
                📃 Status: {{ $storesrequisition->status === 'P' ? '⏳ Pending' : ($storesrequisition->status === 'A' ? '✅ Approved' :($storesrequisition->status === 'V' ? '⏳Awaiting Clearance' : ($storesrequisition->status === 'D'?'✅ Delivered': ($storesrequisition->status === 'C'?'✅ Collected':'❌ Rejected'))) ) }}
            </div>
        </div>



        <!-- Employee Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-4 mt-6 text-sm sm:text-base">
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-gray-600">
                👤 <strong>Initiated By:</strong> {{ $employee->name }} {{ $employee->surname }}
            </div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-base text-gray-700">
                🏢 <strong>Department:</strong> {{ $departmentname }}
            </div>
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-gray-600">
                📝<strong>Purpose:</strong> {{ $storesrequisition->purposeofrequisition }}
            </div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-base text-gray-700 pb-12"></div>

            @forelse( $storesrequisitionitems as $storesrequisitionitem)
                <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-gray-600">
                    🎯 <strong>Request For: </strong> {{ $storesrequisitionitem['itemdetail'] }}
                </div>
                <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-gray-600">
                     🔢<strong>Required quantity: </strong> {{ $storesrequisitionitem['requiredquantity'] }}
                </div>
            @empty
                <div class="col-span-2 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-gray-600">
                    📦 <strong>No requests on this submission</strong>
                </div>    
            @endforelse
        </div>

        <!-- Approval Form -->
        <x-form wire:submit="approverequisition">
            <!-- Comment Section -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-6 mt-8 mx-6">
                <div class="col-span-1 sm:col-span-2 lg:col-span-10">
                    <x-textarea
                        wire:model.live="comment"
                        hint="🗒️ HOD's Comment"
                        rows="4"
                        class="border border-gray-300 rounded-lg p-3 text-gray-700 focus:ring-2 focus:ring-blue-500 transition duration-300"
                        :disabled="$approvalrecord->decision !=null && $approvalrecord->decision == true"
                    />
                </div>
            </div>

            <!-- Approve/Reject Buttons -->
            <x-slot:actions>
                <div class="flex justify-center gap-8 mt-6">
                    <!-- Reject Button -->
                    <x-button
                        label="❌ Reject"
                        wire:click="$set('isapproved', false)"
                        type="submit"
                        spinner="approverequisition"
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-300 ease-in-out transform hover:scale-105 shadow-md"
                        :disabled="$approvalrecord->decision !=null && $approvalrecord->decision == true"
                    />

                    <!-- Approve Button -->
                    <x-button
                        label="✅ Approve"
                        wire:click="$set('isapproved', true)"
                        type="submit"
                        spinner="approverequisition"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-300 ease-in-out transform hover:scale-105 shadow-md"
                        :disabled="$approvalrecord->decision !=null && $approvalrecord->decision == false"
                    />
                </div>
            </x-slot:actions>
        </x-form>
    </div>
</div>
