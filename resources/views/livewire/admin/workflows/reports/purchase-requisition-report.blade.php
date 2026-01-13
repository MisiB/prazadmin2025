<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
        link-item-class="text-base" />

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
        <x-card class="border-2 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Total Requisitions</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $summaryStats['total_requisitions'] }}</div>
                </div>
                <x-icon name="o-document-text" class="w-12 h-12 text-blue-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Approved</div>
                    <div class="text-3xl font-bold text-green-600">{{ $summaryStats['approved_requisitions'] }}</div>
                </div>
                <x-icon name="o-check-badge" class="w-12 h-12 text-green-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Draft</div>
                    <div class="text-3xl font-bold text-purple-600">{{ $summaryStats['draft_requisitions'] }}</div>
                </div>
                <x-icon name="o-pencil-square" class="w-12 h-12 text-purple-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-orange-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Completed</div>
                    <div class="text-3xl font-bold text-orange-600">{{ $summaryStats['completed_requisitions'] }}</div>
                </div>
                <x-icon name="o-check-circle" class="w-12 h-12 text-orange-400" />
            </div>
        </x-card>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
        <x-card class="border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Awaiting Recommendation</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $summaryStats['awaiting_recommendation'] }}</div>
                </div>
                <x-icon name="o-clock" class="w-10 h-10 text-yellow-400" />
            </div>
        </x-card>

        <x-card class="border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Recommended</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $summaryStats['recommended_requisitions'] }}</div>
                </div>
                <x-icon name="o-hand-thumb-up" class="w-10 h-10 text-blue-400" />
            </div>
        </x-card>

        <x-card class="border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Rejected</div>
                    <div class="text-2xl font-bold text-red-600">{{ $summaryStats['rejected_requisitions'] }}</div>
                </div>
                <x-icon name="o-hand-thumb-down" class="w-10 h-10 text-red-400" />
            </div>
        </x-card>

        <x-card class="border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Awarded</div>
                    <div class="text-2xl font-bold text-indigo-600">{{ $summaryStats['awarded_requisitions'] }}</div>
                </div>
                <x-icon name="o-trophy" class="w-10 h-10 text-indigo-400" />
            </div>
        </x-card>
    </div>

    <!-- Filters -->
    <x-card title="Filters & Search" separator class="mt-5 border-2 border-gray-200">
        <!-- First Row: Date Range and Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-input wire:model.live="start_date" type="date" label="Start Date" />
            <x-input wire:model.live="end_date" type="date" label="End Date" />
            <x-select wire:model.live="status_filter" label="Status" :options="$statusOptions" />
        </div>
        
        <!-- Second Row: Department and Search -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <x-select wire:model.live="department_filter" label="Department" :options="$departments" 
                option-value="id" option-label="name" placeholder="Search by department">
            </x-select>
            <x-input wire:model.live.debounce.300ms="search" label="Search" 
                placeholder="PR #, Description, Purpose" icon="o-magnifying-glass" />
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2 mt-4">
            <x-button icon="o-funnel" class="btn-primary btn-sm" label="Apply Filters" wire:click="applyFilters" />
            <x-button icon="o-arrow-path" class="btn-outline btn-sm" label="Reset" wire:click="resetFilters" />
            <x-button icon="o-document-arrow-down" class="btn-success btn-sm" label="Export Excel" wire:click="exportToExcel" />
            <x-button icon="o-document-text" class="btn-error btn-sm" label="Export PDF" wire:click="exportToPdf" />
        </div>
    </x-card>

    <!-- Requisitions Report Table -->
    <x-card title="Purchase Requisitions Report" separator class="mt-5 border-2 border-gray-200">
        @if ($requisitions->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th class="w-32">PR Number</th>
                            <th class="w-48">Description</th>
                            <th class="w-40">Department</th>
                            <th class="w-24">Quantity</th>
                            <th class="w-32">Purpose</th>
                            <th class="w-32">Status</th>
                            <th class="w-40">Requested By</th>
                            <th class="w-32">Created</th>
                            <th class="w-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requisitions as $requisition)
                            <tr>
                                <td>
                                    <div class="font-semibold">{{ $requisition->prnumber }}</div>
                                    <div class="text-xs text-gray-500">Year: {{ $requisition->year }}</div>
                                </td>

                                <td>
                                    <div class="font-semibold">{{ \Illuminate\Support\Str::limit($requisition->description, 50) }}</div>
                                    <div class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($requisition->budgetitem?->name ?? 'N/A', 40) }}</div>
                                </td>

                                <td>
                                    <div class="text-sm">{{ $requisition->department->name ?? 'N/A' }}</div>
                                </td>

                                <td>
                                    <div class="font-semibold">{{ $requisition->quantity }}</div>
                                </td>

                                <td>
                                    <div class="text-sm">{{ \Illuminate\Support\Str::limit($requisition->purpose, 30) }}</div>
                                </td>

                                <!-- Status -->
                                <td>
                                    @php
                                        $statusColor = match ($requisition->status) {
                                            'DRAFT' => 'badge-ghost',
                                            'AWAITING_RECOMMENDATION' => 'badge-info',
                                            'RECOMMENDED' => 'badge-info',
                                            'APPROVED' => 'badge-success',
                                            'REJECTED' => 'badge-error',
                                            'AWARDED' => 'badge-success',
                                            'COMPLETED' => 'badge-success',
                                            default => 'badge-ghost',
                                        };
                                    @endphp
                                    <x-badge :value="$requisition->status" class="{{ $statusColor }} badge-sm" />
                                </td>

                                <td>
                                    <div class="text-xs space-y-1">
                                        <div><span class="font-semibold">{{ $requisition->requestedby->name ?? 'N/A' }}</span></div>
                                        @if ($requisition->recommendedby)
                                            <div class="text-gray-500">Recommended: {{ $requisition->recommendedby->name }}</div>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div class="text-xs space-y-1">
                                        <div><span class="font-semibold">Created:</span> {{ $requisition->created_at->format('Y-m-d') }}</div>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div class="flex gap-1">
                                        <x-button icon="o-eye" class="btn-ghost btn-xs" 
                                            link="{{ route('admin.workflows.purchaserequisition', $requisition->uuid) }}" 
                                            tooltip="View Details" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $requisitions->links() }}
            </div>
        @else
            <div class="flex items-center justify-center h-64">
                <div class="text-center">
                    <x-icon name="o-document-text" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <div class="text-lg text-gray-500">No requisitions found</div>
                    <div class="text-sm text-gray-400 mt-2">Try adjusting your filters</div>
                </div>
            </div>
        @endif
    </x-card>

    <!-- Detailed Breakdown by Status -->
    <x-card title="Status Breakdown" separator class="mt-5 border-2 border-gray-200">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
            @foreach ($statusOptions as $statusOption)
                @if ($statusOption['id'] !== 'ALL')
                    @php
                        $count = $requisitions->where('status', $statusOption['id'])->count();
                    @endphp
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-700">{{ $count }}</div>
                        <div class="text-xs text-gray-600 mt-1">{{ $statusOption['name'] }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </x-card>
</div>

