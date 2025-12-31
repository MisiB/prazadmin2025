<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box"
        link-item-class="text-base" />
    <x-card title="Travel & Subsistence Allowances" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..." />
            @can('tsa.create')
                <x-button icon="o-plus" class="btn-primary" label="New Application" @click="$wire.modal=true" />
            @endcan
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$allowances" class="table-zebra table-xs">
            @scope('cell_application_number', $allowance)
                <div>{{ $allowance->application_number }}</div>
            @endscope
            @scope('cell_full_name', $allowance)
                <div>{{ $allowance->full_name }}</div>
            @endscope
            @scope('cell_trip_start_date', $allowance)
                <div>{{ $allowance->trip_start_date?->format('d M Y') }}</div>
            @endscope
            @scope('cell_trip_end_date', $allowance)
                <div>{{ $allowance->trip_end_date?->format('d M Y') }}</div>
            @endscope
            @scope('cell_reason_for_allowances', $allowance)
                <div class="max-w-xs truncate" title="{{ $allowance->reason_for_allowances }}">
                    {{ $allowance->reason_for_allowances }}
                </div>
            @endscope
            @scope('cell_balance_due', $allowance)
                <div>${{ number_format($allowance->balance_due, 2) }}</div>
            @endscope
            @scope('cell_status', $allowance)
                @php
                    $statusColor = match ($allowance->status) {
                        'DRAFT' => 'badge-warning',
                        'SUBMITTED' => 'badge-info',
                        'UNDER_REVIEW' => 'badge-info',
                        'RECOMMENDED' => 'badge-info',
                        'APPROVED' => 'badge-success',
                        'FINANCE_VERIFIED' => 'badge-success',
                        'PAYMENT_PROCESSED' => 'badge-success',
                        'REJECTED' => 'badge-error',
                        'ARCHIVED' => 'badge-ghost',
                        default => 'badge-ghost',
                    };
                @endphp
                <x-badge :value="$allowance->status" class="{{ $statusColor }}" />
            @endscope
            @scope('cell_action', $allowance)
                <div class="flex items-center space-x-2">
                    <x-button icon="o-eye" class="btn-xs btn-success btn-outline"
                        link="{{ route('admin.workflows.ts-allowance', $allowance->uuid) }}" />
                    @if ($allowance->status == 'DRAFT')
                        @can('tsa.edit.draft')
                            <x-button icon="o-pencil" class="btn-xs btn-info btn-outline" wire:click="edit({{ $allowance->id }})"
                                spinner />
                        @endcan
                        @can('tsa.submit')
                            <x-button icon="o-paper-airplane" class="btn-xs btn-primary btn-outline"
                                wire:click="submit({{ $allowance->id }})" wire:confirm="Are you sure you want to submit this application?"
                                spinner />
                        @endcan
                        @can('tsa.edit.draft')
                            <x-button icon="o-trash" class="btn-xs btn-outline btn-error" wire:click="delete({{ $allowance->id }})"
                                wire:confirm="Are you sure?" spinner />
                        @endcan
                    @endif
                </div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No T&S Allowance applications found." />
            </x-slot:empty>
        </x-table>

        <div class="mt-4">
            {{ $allowances->links() }}
        </div>
    </x-card>

    <x-modal title="{{ $id ? 'Edit T&S Allowance Application' : 'New T&S Allowance Application' }}" wire:model="modal"
        box-class="max-w-6xl" separator>
        <x-form wire:submit="save">
            <div class="space-y-6">
                {{-- Trip Information --}}
                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold mb-3">Trip Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <x-input wire:model="job_title" label="Job Title" />
                        <x-select wire:model.live="grade_band_id" label="Grade Band" :options="$gradeBands"
                            option-label="code" option-value="id" placeholder="Select Grade Band">
                            <x-slot:append>
                                @if ($selectedConfig)
                                    <span class="text-xs text-success">✓ Rates loaded</span>
                                @endif
                            </x-slot:append>
                        </x-select>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <x-input wire:model.live="trip_start_date" type="date" label="Trip Start Date" />
                        <x-input wire:model.live="trip_end_date" type="date" label="Trip End Date" />
                        <x-input wire:model="number_of_days" type="number" label="Number of Days" readonly
                            class="bg-gray-100" />
                    </div>
                    <div class="mt-4">
                        <x-textarea wire:model="reason_for_allowances" label="Reason for Allowances" rows="3" />
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trip Supporting Document</label>
                        <input type="file" wire:model="trip_attachment" 
                            accept=".pdf,.doc,.docx"
                            class="file-input file-input-bordered w-full" />
                        <p class="text-xs text-gray-500 mt-1">Optional. Upload supporting document (PDF, Word). Max 10MB</p>
                        @if ($existing_attachment_path)
                            <div class="mt-2 p-2 bg-green-50 rounded border border-green-200">
                                <div class="flex items-center gap-2">
                                    <x-icon name="o-document" class="w-5 h-5 text-green-600" />
                                    <span class="text-sm text-green-700">Current attachment: </span>
                                    <a href="{{ asset('storage/' . $existing_attachment_path) }}" 
                                        target="_blank" 
                                        class="text-sm text-blue-600 hover:underline">
                                        View Document
                                    </a>
                                </div>
                            </div>
                        @endif
                        @error('trip_attachment')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Allowance Breakdown --}}
                <div class="border-b pb-4">
                    <h3 class="text-lg font-semibold mb-3">Allowance Breakdown</h3>
                    @if ($selectedConfig)
                        <div class="alert alert-info mb-4">
                            <x-icon name="o-information-circle" class="w-5 h-5" />
                            <div>
                                <p>Rates are auto-calculated based on the selected grade band.</p>
                                <ul class="text-xs mt-1 list-disc list-inside">
                                    <li>Out of Station: flat rate (x1 always)</li>
                                    <li>Overnight & Bed: x{{ max(0, ($number_of_days ?: 0) - 1) }} nights (excludes end date)</li>
                                    <li>Meals: select which days to include (all days, first day only, last day only, or first & last)</li>
                                    <li>Dinner: excludes last travel day automatically</li>
                                    <li>Transport (Fuel, Toll Gates): enter amounts manually</li>
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Subsistence & Accommodation --}}
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-600 mb-2">Subsistence & Accommodation</h4>
                        <div class="grid grid-cols-3 gap-4">
                            {{-- Out of Station - Always included --}}
                            <div>
                                <label class="label text-xs">Out of Station/Subsistence <span class="badge badge-sm badge-warning">x1</span></label>
                                <div class="flex gap-2">
                                    <x-input wire:model="out_of_station_rate" type="number" step="0.01" 
                                        prefix="$" readonly class="bg-gray-100 flex-1" placeholder="Rate" />
                                    <x-input wire:model="out_of_station_subsistence" type="number" step="0.01" 
                                        prefix="$" readonly class="bg-green-50 flex-1 font-semibold" placeholder="Total" />
                                </div>
                            </div>

                            {{-- Overnight - Always included, excludes end date --}}
                            <div>
                                <label class="label text-xs">Overnight Allowance <span class="badge badge-sm badge-warning">x{{ max(0, ($number_of_days ?: 0) - 1) }} nights</span></label>
                                <div class="flex gap-2">
                                    <x-input wire:model="overnight_rate" type="number" step="0.01" 
                                        prefix="$" readonly class="bg-gray-100 flex-1" placeholder="Rate" />
                                    <x-input wire:model="overnight_allowance" type="number" step="0.01" 
                                        prefix="$" readonly class="bg-green-50 flex-1 font-semibold" placeholder="Total" />
                                </div>
                            </div>

                            {{-- Bed Allowance - Optional with checkbox and night selection --}}
                            <div class="border rounded-lg p-3 {{ $include_bed_allowance ? 'bg-green-50 border-green-300' : 'bg-gray-50' }}">
                                <div class="flex items-center gap-2 mb-2">
                                    <x-checkbox wire:model.live="include_bed_allowance" />
                                    <label class="label text-xs cursor-pointer">Bed Allowance 
                                        @if($include_bed_allowance)
                                            <span class="badge badge-sm badge-warning">x{{ $bed_nights }} nights</span>
                                        @endif
                                    </label>
                                </div>
                                @if($include_bed_allowance)
                                    <div class="mb-2">
                                        <select wire:model.live="bed_option" class="select select-bordered select-xs w-full">
                                            @foreach($this->nightOptions as $opt)
                                                <option value="{{ $opt['id'] }}">{{ $opt['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-input wire:model="bed_rate" type="number" step="0.01" 
                                            prefix="$" readonly class="bg-gray-100 flex-1" placeholder="Rate" />
                                        <x-input wire:model="bed_allowance" type="number" step="0.01" 
                                            prefix="$" readonly class="bg-green-100 flex-1 font-semibold" placeholder="Total" />
                                    </div>
                                @else
                                    <div class="text-xs text-gray-500 italic">Check to include</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Meals - Optional with checkboxes and day selection --}}
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-600 mb-2">Meals <span class="text-xs text-gray-400">(Check to include and select which days)</span></h4>
                        <div class="grid grid-cols-3 gap-4">
                            {{-- Breakfast --}}
                            <div class="border rounded-lg p-3 {{ $include_breakfast ? 'bg-green-50 border-green-300' : 'bg-gray-50' }}">
                                <div class="flex items-center gap-2 mb-2">
                                    <x-checkbox wire:model.live="include_breakfast" />
                                    <label class="label text-xs cursor-pointer">Breakfast 
                                        @if($include_breakfast)
                                            <span class="badge badge-sm badge-info">x{{ $breakfast_days }}</span>
                                        @endif
                                    </label>
                                </div>
                                @if($include_breakfast)
                                    <div class="mb-2">
                                        <select wire:model.live="breakfast_option" class="select select-bordered select-xs w-full">
                                            @foreach($this->mealOptions as $opt)
                                                <option value="{{ $opt['id'] }}">{{ $opt['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-input wire:model="breakfast_rate" type="number" step="0.01" 
                                            prefix="$" readonly class="bg-gray-100 flex-1" placeholder="Rate" />
                                        <x-input wire:model="breakfast" type="number" step="0.01" 
                                            prefix="$" readonly class="bg-green-100 flex-1 font-semibold" placeholder="Total" />
                                    </div>
                                @else
                                    <div class="text-xs text-gray-500 italic">Check to include</div>
                                @endif
                            </div>

                            {{-- Lunch --}}
                            <div class="border rounded-lg p-3 {{ $include_lunch ? 'bg-green-50 border-green-300' : 'bg-gray-50' }}">
                                <div class="flex items-center gap-2 mb-2">
                                    <x-checkbox wire:model.live="include_lunch" />
                                    <label class="label text-xs cursor-pointer">Lunch 
                                        @if($include_lunch)
                                            <span class="badge badge-sm badge-info">x{{ $lunch_days }}</span>
                                        @endif
                                    </label>
                                </div>
                                @if($include_lunch)
                                    <div class="mb-2">
                                        <select wire:model.live="lunch_option" class="select select-bordered select-xs w-full">
                                            @foreach($this->mealOptions as $opt)
                                                <option value="{{ $opt['id'] }}">{{ $opt['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-input wire:model="lunch_rate" type="number" step="0.01" 
                                            prefix="$" readonly class="bg-gray-100 flex-1" placeholder="Rate" />
                                        <x-input wire:model="lunch" type="number" step="0.01" 
                                            prefix="$" readonly class="bg-green-100 flex-1 font-semibold" placeholder="Total" />
                                    </div>
                                @else
                                    <div class="text-xs text-gray-500 italic">Check to include</div>
                                @endif
                            </div>

                            {{-- Dinner - Special: (days - 1) because no dinner on last day --}}
                            <div class="border rounded-lg p-3 {{ $include_dinner ? 'bg-green-50 border-green-300' : 'bg-gray-50' }}">
                                <div class="flex items-center gap-2 mb-2">
                                    <x-checkbox wire:model.live="include_dinner" />
                                    <label class="label text-xs cursor-pointer">Dinner 
                                        @if($include_dinner)
                                            <span class="badge badge-sm badge-warning">x{{ $dinner_days }}</span>
                                            <span class="text-xs text-orange-600">(excludes last day)</span>
                                        @endif
                                    </label>
                                </div>
                                @if($include_dinner)
                                    <div class="mb-2">
                                        <select wire:model.live="dinner_option" class="select select-bordered select-xs w-full">
                                            @foreach($this->mealOptions as $opt)
                                                <option value="{{ $opt['id'] }}">{{ $opt['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <p class="text-xs text-orange-500 mt-1">Note: Dinner on last day is never included</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-input wire:model="dinner_rate" type="number" step="0.01" 
                                            prefix="$" readonly class="bg-gray-100 flex-1" placeholder="Rate" />
                                        <x-input wire:model="dinner" type="number" step="0.01" 
                                            prefix="$" readonly class="bg-green-100 flex-1 font-semibold" placeholder="Total" />
                                    </div>
                                @else
                                    <div class="text-xs text-gray-500 italic">Check to include</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Transport - Manual Entry --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-600 mb-2">Transport <span class="text-xs text-gray-400">(Enter amounts manually)</span></h4>
                        <div class="grid grid-cols-3 gap-4">
                            {{-- Fuel - Manual entry --}}
                            <div>
                                <x-input wire:model.live="fuel" type="number" step="0.01" 
                                    label="Fuel" prefix="$" placeholder="Enter fuel amount" />
                            </div>

                            {{-- Toll Gates - Manual entry --}}
                            <div>
                                <x-input wire:model.live="toll_gates" type="number" step="0.01" 
                                    label="Toll Gates" prefix="$" placeholder="Enter toll gate amount" />
                            </div>

                            {{-- Mileage --}}
                            <div>
                                <label class="label text-xs">Mileage (Rate: ${{ number_format($mileage_rate_per_km, 2) }}/km)</label>
                                <x-input wire:model.live="mileage_estimated_distance" type="number" step="0.01" 
                                    label="" suffix="km" placeholder="Enter estimated distance" />
                                @if ($mileage_estimated_distance > 0 && $mileage_rate_per_km > 0)
                                    <div class="text-xs text-green-600 mt-1">
                                        = ${{ number_format($mileage_estimated_distance * $mileage_rate_per_km, 2) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Summary --}}
                <div class="bg-primary/10 p-4 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold">Total Allowance</span>
                        <span class="text-2xl font-bold text-primary">${{ number_format($total_allowance, 2) }}</span>
                    </div>
                    @if ($mileage_estimated_distance > 0 && $mileage_rate_per_km > 0)
                        <div class="text-sm text-gray-600 mt-1">
                            Includes mileage: {{ number_format($mileage_estimated_distance, 2) }} km × ${{ number_format($mileage_rate_per_km, 2) }} = ${{ number_format($mileage_estimated_distance * $mileage_rate_per_km, 2) }}
                        </div>
                    @endif
                </div>
            </div>

            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" @click="$wire.modal = false" />
                <x-button class="btn-primary" label="Save" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>

