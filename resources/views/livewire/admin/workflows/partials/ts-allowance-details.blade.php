<div class="space-y-4">
    <!-- Applicant Section -->
    <div class="bg-white p-4 rounded-lg border">
        <h3 class="text-lg font-semibold mb-3 text-gray-700">Applicant Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <x-input label="Application Number" value="{{ $allowanceDetails->application_number }}" readonly />
            <x-input label="Full Name" value="{{ $allowanceDetails->full_name }}" readonly />
            <x-input label="Job Title" value="{{ $allowanceDetails->job_title }}" readonly />
            <x-input label="Department" value="{{ $allowanceDetails->department->name ?? 'N/A' }}" readonly />
            <x-input label="Grade" value="{{ $allowanceDetails->grade }}" readonly />
            <x-input label="Trip Start Date" value="{{ $allowanceDetails->trip_start_date?->format('Y-m-d') }}" readonly />
            <x-input label="Trip End Date" value="{{ $allowanceDetails->trip_end_date?->format('Y-m-d') }}" readonly />
            <x-input label="Number of Days" value="{{ $allowanceDetails->number_of_days }}" readonly />
            <x-input label="Submission Date" value="{{ $allowanceDetails->submission_date?->format('Y-m-d H:i:s') ?? 'N/A' }}" readonly />
        </div>
        <div class="mt-3">
            <x-textarea label="Reason for Allowances" readonly rows="3">{{ $allowanceDetails->reason_for_allowances }}</x-textarea>
        </div>
        @if ($allowanceDetails->trip_attachment_path)
            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-center gap-2">
                    <x-icon name="o-paper-clip" class="w-5 h-5 text-blue-600" />
                    <span class="text-sm font-medium text-blue-800">Trip Supporting Document</span>
                    <a href="{{ asset('storage/' . $allowanceDetails->trip_attachment_path) }}" 
                        target="_blank" 
                        class="ml-auto btn btn-sm btn-outline btn-info">
                        <x-icon name="o-document-arrow-down" class="w-4 h-4" />
                        View Attachment
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Allowance Breakdown Section -->
    <div class="bg-white p-4 rounded-lg border">
        <h3 class="text-lg font-semibold mb-3 text-gray-700">Allowance Breakdown</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <x-input label="Out of Station/Subsistence" value="${{ number_format($allowanceDetails->out_of_station_subsistence, 2) }}" readonly />
            <x-input label="Overnight Allowance" value="${{ number_format($allowanceDetails->overnight_allowance, 2) }}" readonly />
            <x-input label="Bed Allowance" value="${{ number_format($allowanceDetails->bed_allowance, 2) }}" readonly />
            <x-input label="Breakfast" value="${{ number_format($allowanceDetails->breakfast, 2) }}" readonly />
            <x-input label="Lunch" value="${{ number_format($allowanceDetails->lunch, 2) }}" readonly />
            <x-input label="Dinner" value="${{ number_format($allowanceDetails->dinner, 2) }}" readonly />
            <x-input label="Fuel" value="${{ number_format($allowanceDetails->fuel, 2) }}" readonly />
            <x-input label="Toll Gates" value="${{ number_format($allowanceDetails->toll_gates, 2) }}" readonly />
            <x-input label="Mileage/Distance (km)" value="{{ number_format($allowanceDetails->mileage_estimated_distance, 2) }}" readonly />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4 pt-4 border-t">
            <x-input label="Calculated Subtotal" value="${{ number_format($allowanceDetails->calculated_subtotal, 2) }}" readonly class="font-bold" />
            <x-input label="Balance Due" value="${{ number_format($allowanceDetails->balance_due, 2) }}" readonly class="font-bold" />
        </div>
    </div>

    <!-- Approval History -->
    @if($allowanceDetails->approvals && $allowanceDetails->approvals->count() > 0)
        <div class="bg-white p-4 rounded-lg border">
            <h3 class="text-lg font-semibold mb-3 text-gray-700">Approval History</h3>
            <div class="space-y-2">
                @foreach($allowanceDetails->approvals as $approval)
                    <div class="border rounded p-3 text-sm">
                        <div class="grid grid-cols-2 gap-2">
                            <div><span class="font-medium">Approver:</span> {{ $approval->approver->name ?? 'N/A' }}</div>
                            <div><span class="font-medium">Date:</span> {{ $approval->created_at->format('Y-m-d H:i:s') }}</div>
                            <div><span class="font-medium">Decision:</span> {{ $approval->decision ?? 'N/A' }}</div>
                            <div><span class="font-medium">Status:</span> {{ $approval->workflowParameter->status ?? 'N/A' }}</div>
                            @if($approval->comment)
                                <div class="col-span-2"><span class="font-medium">Comment:</span> {{ $approval->comment }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

