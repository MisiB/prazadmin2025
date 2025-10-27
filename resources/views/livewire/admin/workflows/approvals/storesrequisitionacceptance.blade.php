<div class="container mx-auto px-4 py-8 bg-gradient-to-tr from-blue-50 via-green-50 to-blue-100 min-h-screen">
    <div class="bg-white rounded-3xl shadow-2xl p-8 space-y-8 max-w-7xl mx-auto border border-blue-100">

        <!-- Welcome Message -->
        <div class="text-center">
            <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-blue-900 tracking-tight leading-snug">
                STORES DELIVERY NOTE
            </h1>
        </div>

        <div class="text-center text-gray-500 text-xs italic">
            (Delivery acceptance by {{ $employee->gender === 'M' ? 'mr' : 'mrs' }} {{ strtolower($employee->surname) }})
        </div>

        <!-- Header Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-4 justify-center mt-4">
            <div class="col-span-2 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-xl font-semibold text-green-700">
                🧾 Stores Requisition Acceptance
            </div>
            <div class="col-span-2 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-lg font-semibold text-blue-700">
                📃 Status: {{ $storesrequisition->status === 'P' ? '⏳ Pending' : ($storesrequisition->status === 'A' ? '✅ Approved' :($storesrequisition->status === 'V' ? '⏳Awaiting Clearance' : ($storesrequisition->status === 'D'?'✅ Delivered': ($storesrequisition->status === 'C'?'✅ Collected':'❌ Rejected'))) ) }}
            </div>
        </div>

        <!-- Employee Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-10 gap-4 mt-4 text-gray-800 text-sm leading-relaxed">
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4">👤 <strong>Approved By:</strong> {{ $hod->name }} {{ $hod->surname }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3">📦 <strong>Delivered By:</strong> {{ $adminissuer->name }} {{ $adminissuer->surname }}</div>
            <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4">🏢 <strong>Department:</strong> {{ $departmentname }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3">📦 <strong>Verified By:</strong> {{ $adminvalidator->name }} {{ $adminvalidator ->surname }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-3 lg:col-span-4">📝 <strong>Purpose:</strong> {{ $storesrequisition->purposeofrequisition }}</div>
            <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-base text-gray-700 pb-12"></div>
             

            @forelse( $storesrequisitionitems as $storesrequisitionitem)
                <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-gray-600">🎯 <strong>Request For: </strong> {{ $storesrequisitionitem['itemdetail'] }}</div>
                <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-gray-600">🔢 <strong>Required quantity: </strong> {{ $storesrequisitionitem['requiredquantity'] }}</div>                
                <div class="col-span-1 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-gray-600"></div>
                <div class="col-span-1 sm:col-start-2 lg:col-start-7 lg:col-span-3 text-gray-600">📤 <strong>Issued Quantity:</strong> {{ $storesrequisitionitem['issuedquantity'] }}</div>
            @empty
                <div class="col-span-2 sm:col-start-1 lg:col-start-3 lg:col-span-4 text-gray-600">
                    📦 <strong>No requests on this submission</strong>
                </div>    
            @endforelse
        </div>

        <!-- Form Start -->
        <x-form wire:submit="acceptrequisition">

            <!-- Comment Area -->
            <div class="grid grid-cols-1 lg:grid-cols-10 gap-4 mt-6">
                <div class="col-span-1 lg:col-span-10">
                    <x-textarea wire:model.live="comment"
                        hint="💬 INITIATOR'S Comment"
                        rows="4"
                        class="border border-gray-300 rounded-lg p-3 text-gray-700 focus:ring-2 focus:ring-blue-500 transition duration-300"
                        :disabled="$receiverrecord->decision !=null"
                    />
                </div>
            </div>

            <!-- Action Buttons -->
            <x-slot:actions>
                <div class="flex flex-wrap justify-center gap-6 mt-8">

                    {{-- Reject Button --}}            
                    <x-button label="❌ Reject"
                        wire:click="$set('isapproved',false)"
                        type="submit"
                        spinner="acceptrequisition"
                        class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-300 ease-in-out transform hover:scale-105 shadow-md"
                        :disabled="$receiverrecord->decision !=null && $receiverrecord->decision == true"
                    />

                    {{-- Approve Button --}}
                    <x-button label="✅ Approve"
                        wire:click="$set('isapproved',true)"
                        type="submit"
                        spinner="acceptrequisition"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-300 ease-in-out transform hover:scale-105 shadow-md"
                        :disabled="$receiverrecord->decision !=null && $receiverrecord->decision == false"
                    />
                </div>
            </x-slot:actions>
        </x-form>
    </div>
</div>
