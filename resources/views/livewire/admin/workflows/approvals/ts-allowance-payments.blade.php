<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
        link-item-class="text-base" />

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-5">
        <x-card class="border-2 border-orange-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Awaiting Payment</div>
                    <div class="text-3xl font-bold text-orange-600">{{ $awaitingPaymentAllowances->count() }}</div>
                </div>
                <x-icon name="o-clock" class="w-12 h-12 text-orange-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Paid Today</div>
                    <div class="text-3xl font-bold text-green-600">{{ $paymentsToday }}</div>
                </div>
                <x-icon name="o-check-circle" class="w-12 h-12 text-green-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Paid This Month</div>
                    <div class="text-3xl font-bold text-purple-600">{{ $paymentsThisMonth }}</div>
                </div>
                <x-icon name="o-currency-dollar" class="w-12 h-12 text-purple-400" />
            </div>
        </x-card>

        <x-card class="border-2 border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-600">Total Paid This Month (USD)</div>
                    <div class="text-2xl font-bold text-blue-600">${{ number_format($totalPaidThisMonth, 2) }}</div>
                </div>
                <x-icon name="o-banknotes" class="w-12 h-12 text-blue-400" />
            </div>
        </x-card>
    </div>

    <!-- Currency Breakdown Card -->
    @if($totalPaidByCurrency->count() > 0)
        <x-card title="This Month's Payments by Currency" separator class="mt-5 border-2 border-indigo-200">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($totalPaidByCurrency as $currencyData)
                    <div class="p-4 rounded-lg border-2 {{ $currencyData['currency_name'] === 'USD' ? 'bg-green-50 border-green-300' : 'bg-orange-50 border-orange-300' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-lg {{ $currencyData['currency_name'] === 'USD' ? 'text-green-700' : 'text-orange-700' }}">
                                {{ $currencyData['currency_name'] }}
                            </span>
                            <span class="text-xs bg-gray-200 px-2 py-1 rounded-full">
                                {{ $currencyData['count'] }} payment(s)
                            </span>
                        </div>
                        <div class="text-2xl font-bold {{ $currencyData['currency_name'] === 'USD' ? 'text-green-800' : 'text-orange-800' }}">
                            @if($currencyData['currency_name'] === 'USD')
                                ${{ number_format($currencyData['total_original'], 2) }}
                            @else
                                {{ number_format($currencyData['total_original'], 2) }}
                            @endif
                        </div>
                        @if($currencyData['currency_name'] !== 'USD')
                            <div class="text-xs text-gray-500 mt-1">
                                USD Equivalent: ${{ number_format($currencyData['total_usd'], 2) }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            
            <!-- Total Summary -->
            <div class="mt-4 p-4 bg-indigo-100 rounded-lg border-2 border-indigo-300">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-semibold text-indigo-800">Total (All Currencies in USD)</span>
                        <p class="text-xs text-indigo-600">Combined value of all payments this month</p>
                    </div>
                    <span class="text-3xl font-bold text-indigo-800">${{ number_format($totalPaidThisMonth, 2) }}</span>
                </div>
            </div>
        </x-card>
    @endif

    <!-- Allowances Awaiting Payment (After CEO Approval) -->
    <x-card title="Allowances Awaiting Payment" separator class="mt-5 border-2 border-orange-200">
        <x-slot:menu>
            <span class="text-sm text-gray-500">CEO Approved → Ready for Payment</span>
        </x-slot:menu>
        
        @if ($awaitingPaymentAllowances->count() > 0)
            <div class="space-y-3">
                @foreach ($awaitingPaymentAllowances as $allowance)
                    @php
                        $isExpanded = $this->isAllowanceExpanded($allowance->uuid);
                        $daysWaiting = $allowance->updated_at ? $allowance->updated_at->diffInDays(now()) : 0;
                    @endphp
                    <div class="border border-gray-200 rounded-lg shadow-sm">
                        <!-- Allowance Header -->
                        <div class="p-3 bg-white rounded-t-lg cursor-pointer hover:bg-gray-50 transition-colors"
                            wire:click="toggleAllowance('{{ $allowance->uuid }}')">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <x-icon name="o-chevron-{{ $isExpanded ? 'down' : 'right' }}" class="w-4 h-4" />
                                    <div>
                                        <div class="font-semibold">{{ $allowance->application_number }}</div>
                                        <div class="text-sm text-gray-600">{{ $allowance->full_name }} - {{ $allowance->department->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <div class="font-semibold text-lg">${{ number_format($allowance->balance_due, 2) }}</div>
                                        <div class="text-xs text-gray-500">{{ $daysWaiting }} days since approval</div>
                                    </div>
                                    @can('tsa.payment.execute')
                                        <x-button icon="o-currency-dollar" class="btn-success btn-sm" label="Execute Payment"
                                            @click.stop="$wire.openPaymentModal('{{ $allowance->uuid }}')" />
                                    @endcan
                                </div>
                            </div>
                        </div>

                        <!-- Allowance Details (Expanded) -->
                        @if ($isExpanded)
                            @php
                                $allowanceDetails = $this->getAllowanceByUuid($allowance->uuid);
                            @endphp
                            <div class="p-4 bg-gray-50 space-y-4">
                                <!-- Applicant Section -->
                                <div class="bg-white p-4 rounded-lg border">
                                    <h3 class="text-lg font-semibold mb-3 text-gray-700">Applicant Information</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                        <x-input label="Application Number" value="{{ $allowanceDetails->application_number }}" readonly />
                                        <x-input label="Full Name" value="{{ $allowanceDetails->full_name }}" readonly />
                                        <x-input label="Department" value="{{ $allowanceDetails->department->name ?? 'N/A' }}" readonly />
                                        <x-input label="Job Title" value="{{ $allowanceDetails->job_title }}" readonly />
                                        <x-input label="Grade" value="{{ $allowanceDetails->grade }}" readonly />
                                        <x-input label="Trip Start Date" value="{{ $allowanceDetails->trip_start_date?->format('d M Y') }}" readonly />
                                        <x-input label="Trip End Date" value="{{ $allowanceDetails->trip_end_date?->format('d M Y') }}" readonly />
                                        <x-input label="Number of Days" value="{{ $allowanceDetails->number_of_days }}" readonly />
                                    </div>
                                                    <div class="mt-3">
                                                        <x-textarea label="Reason for Allowances" readonly rows="2">{{ $allowanceDetails->reason_for_allowances }}</x-textarea>
                                                    </div>
                                                    @if ($allowanceDetails->trip_attachment_path)
                                                        <div class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
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

                                <!-- Allowance Breakdown -->
                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                    <h3 class="text-lg font-semibold mb-3 text-gray-700">Allowance Breakdown</h3>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                                        <div class="bg-white p-3 rounded border">
                                            <div class="text-xs text-gray-500">Out of Station</div>
                                            <div class="font-semibold">${{ number_format($allowanceDetails->out_of_station_subsistence ?? 0, 2) }}</div>
                                        </div>
                                        <div class="bg-white p-3 rounded border">
                                            <div class="text-xs text-gray-500">Overnight</div>
                                            <div class="font-semibold">${{ number_format($allowanceDetails->overnight_allowance ?? 0, 2) }}</div>
                                        </div>
                                        <div class="bg-white p-3 rounded border">
                                            <div class="text-xs text-gray-500">Bed Allowance</div>
                                            <div class="font-semibold">${{ number_format($allowanceDetails->bed_allowance ?? 0, 2) }}</div>
                                        </div>
                                        <div class="bg-white p-3 rounded border">
                                            <div class="text-xs text-gray-500">Breakfast</div>
                                            <div class="font-semibold">${{ number_format($allowanceDetails->breakfast ?? 0, 2) }}</div>
                                        </div>
                                        <div class="bg-white p-3 rounded border">
                                            <div class="text-xs text-gray-500">Lunch</div>
                                            <div class="font-semibold">${{ number_format($allowanceDetails->lunch ?? 0, 2) }}</div>
                                        </div>
                                        <div class="bg-white p-3 rounded border">
                                            <div class="text-xs text-gray-500">Dinner</div>
                                            <div class="font-semibold">${{ number_format($allowanceDetails->dinner ?? 0, 2) }}</div>
                                        </div>
                                        <div class="bg-white p-3 rounded border">
                                            <div class="text-xs text-gray-500">Fuel</div>
                                            <div class="font-semibold">${{ number_format($allowanceDetails->fuel ?? 0, 2) }}</div>
                                        </div>
                                        <div class="bg-white p-3 rounded border">
                                            <div class="text-xs text-gray-500">Toll Gates</div>
                                            <div class="font-semibold">${{ number_format($allowanceDetails->toll_gates ?? 0, 2) }}</div>
                                        </div>
                                        <div class="bg-white p-3 rounded border">
                                            <div class="text-xs text-gray-500">Mileage ({{ number_format($allowanceDetails->mileage_estimated_distance ?? 0, 0) }} km)</div>
                                            <div class="font-semibold">${{ number_format(($allowanceDetails->mileage_estimated_distance ?? 0) * ($allowanceDetails->gradeBand?->activeConfig?->mileage_rate_per_km ?? 0), 2) }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-4 p-4 bg-green-100 rounded-lg">
                                        <div class="flex justify-between items-center">
                                            <span class="font-semibold text-lg">Total Balance Due</span>
                                            <span class="text-2xl font-bold text-green-700">${{ number_format($allowanceDetails->balance_due, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approval History -->
                                @if($allowanceDetails->workflow && $allowanceDetails->workflow->workflowparameters)
                                    <div class="bg-white p-4 rounded-lg border">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Approval History</h3>
                                        <div class="space-y-3">
                                            @foreach ($allowanceDetails->workflow->workflowparameters->sortBy('order') as $wp)
                                                @php
                                                    // Get ALL approvals for this step, sorted by date
                                                    $stepApprovals = $allowanceDetails->approvals?->where('workflowparameter_id', $wp->id)->sortBy('created_at') ?? collect();
                                                    $latestApproval = $stepApprovals->last();
                                                    $currentStatus = $latestApproval?->status ?? 'PENDING';
                                                    $headerColor = match ($currentStatus) {
                                                        'APPROVED' => 'bg-green-50 border-green-300',
                                                        'REJECTED' => 'bg-red-50 border-red-300',
                                                        'SEND_BACK' => 'bg-orange-50 border-orange-300',
                                                        'PENDING' => 'bg-yellow-50 border-yellow-300',
                                                        default => 'bg-gray-50 border-gray-300',
                                                    };
                                                @endphp
                                                <div class="rounded-lg border-2 {{ $headerColor }} overflow-hidden">
                                                    <div class="p-3 font-semibold text-sm {{ $headerColor }} border-b">
                                                        Step {{ $wp->order }}: {{ $wp->name }}
                                                        @if($stepApprovals->count() > 1)
                                                            <span class="text-xs text-gray-500 ml-2">({{ $stepApprovals->count() }} actions)</span>
                                                        @endif
                                                    </div>
                                                    <div class="p-3 space-y-2 bg-white">
                                                        @forelse ($stepApprovals as $approval)
                                                            @php
                                                                $statusColor = match ($approval->status) {
                                                                    'APPROVED' => 'bg-green-100 text-green-800 border-green-300',
                                                                    'REJECTED' => 'bg-red-100 text-red-800 border-red-300',
                                                                    'SEND_BACK' => 'bg-orange-100 text-orange-800 border-orange-300',
                                                                    default => 'bg-gray-100 text-gray-800 border-gray-300',
                                                                };
                                                            @endphp
                                                            <div class="p-2 rounded border {{ $statusColor }} text-xs">
                                                                <div class="flex items-center justify-between mb-1">
                                                                    <span class="font-medium">{{ $approval->approver->name ?? 'N/A' }}</span>
                                                                    <span class="px-2 py-0.5 rounded-full font-medium {{ $statusColor }}">{{ $approval->status }}</span>
                                                                </div>
                                                                <div class="text-gray-600">
                                                                    <div>Date: {{ $approval->created_at?->format('d M Y H:i:s') ?? '--' }}</div>
                                                                    @if($approval->comment)
                                                                        <div class="italic mt-1">"{{ $approval->comment }}"</div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="text-xs text-gray-500 italic p-2 bg-yellow-50 rounded">
                                                                Awaiting action
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Action Button -->
                                <div class="flex justify-end pt-4 border-t">
                                    @can('tsa.payment.execute')
                                        <x-button icon="o-currency-dollar" class="btn-success" label="Execute Payment"
                                            @click="$wire.openPaymentModal('{{ $allowance->uuid }}')" />
                                    @endcan
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex items-center justify-center h-48">
                <div class="text-center">
                    <x-icon name="o-check-circle" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <div class="text-lg text-gray-500">No allowances awaiting payment</div>
                    <div class="text-sm text-gray-400 mt-2">All CEO approved allowances have been paid</div>
                </div>
            </div>
        @endif
    </x-card>

    <!-- Payment Execution Modal -->
    <x-modal wire:model="paymentmodal" title="Execute Payment" box-class="max-w-4xl">
        <x-form wire:submit="executepayment">
            @if($selectedAllowanceUuid)
                @php
                    $selectedAllowance = $this->getAllowanceByUuid($selectedAllowanceUuid);
                @endphp
                <div class="mb-4 p-4 bg-gray-100 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><strong>Application:</strong> {{ $selectedAllowance->application_number }}</div>
                        <div><strong>Applicant:</strong> {{ $selectedAllowance->full_name }}</div>
                        <div><strong>Trip:</strong> {{ $selectedAllowance->trip_start_date?->format('d M Y') }} - {{ $selectedAllowance->trip_end_date?->format('d M Y') }}</div>
                        <div><strong>Amount Due:</strong> <span class="text-lg font-bold text-green-600">${{ number_format($selectedAllowance->balance_due, 2) }}</span></div>
                    </div>
                </div>

                <!-- Split Payment Toggle -->
                <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-blue-800">Split Payment</span>
                            <p class="text-xs text-blue-600">Enable to pay partially in USD and partially in local currency (e.g., ZiG)</p>
                        </div>
                        <x-toggle wire:model.live="isSplitPayment" class="toggle-primary" />
                    </div>
                </div>
            @endif

            @if(!$isSplitPayment)
                {{-- SINGLE CURRENCY PAYMENT --}}
                <div class="grid grid-cols-2 gap-4">
                    <!-- Currency Selection -->
                    <x-select wire:model.live="currency_id" label="Currency" 
                        :options="$this->currencies" 
                        option-value="id" 
                        option-label="name"
                        placeholder="Select currency" required />
                    
                    <!-- Amount Paid (in selected currency) -->
                    <x-input wire:model.live="amount_paid" type="number" step="0.01" 
                        label="Amount Paid{{ $this->selectedCurrency ? ' (' . $this->selectedCurrency->name . ')' : '' }}" 
                        prefix="{{ $this->selectedCurrency && ($this->selectedCurrency->name === 'USD' || $this->selectedCurrency->name === 'US Dollar') ? '$' : '' }}" 
                        required />
                </div>
                
                <!-- Exchange Rate Selection (only for non-USD currencies) -->
                @if($this->selectedCurrency && $this->selectedCurrency->name !== 'USD' && $this->selectedCurrency->name !== 'US Dollar')
                    <div class="mt-4">
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Select Exchange Rate <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="exchangerate_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">Select the exchange rate used</option>
                                @foreach($this->availableExchangeRates as $rate)
                                    <option value="{{ $rate->id }}">
                                        Rate: {{ number_format($rate->value, 4) }} - by {{ $rate->user->name }} ({{ $rate->created_at->format('M d, Y H:i') }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Select the exchange rate that was used for this payment</p>
                        </div>
                        
                        @if($exchangerate_id)
                            <div class="mt-2 p-3 bg-blue-50 rounded-lg">
                                <div class="text-sm">
                                    <strong>Exchange Rate:</strong> 1 USD = {{ number_format($exchange_rate_used, 4) }} {{ $this->selectedCurrency->name }}
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Display USD Equivalent -->
                    @if($amount_paid_usd)
                        <div class="mt-4">
                            <x-input label="USD Equivalent" 
                                value="${{ number_format($amount_paid_usd, 2) }}" 
                                readonly 
                                hint="This is the USD value that will be recorded" />
                        </div>
                    @endif
                @endif
            @else
                {{-- SPLIT PAYMENT --}}
                <div class="space-y-4">
                    <!-- USD Portion -->
                    <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                        <h4 class="font-semibold text-green-800 mb-3 flex items-center gap-2">
                            <x-icon name="o-currency-dollar" class="w-5 h-5" />
                            USD Portion
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label text-sm">Percentage</label>
                                <div class="flex items-center gap-2">
                                    <input type="range" wire:model.live="usd_percentage" min="0" max="100" step="5" 
                                        class="range range-success range-sm flex-1" />
                                    <span class="font-bold text-green-700 w-16 text-right">{{ number_format($usd_percentage, 0) }}%</span>
                                </div>
                            </div>
                            <x-input wire:model.live="usd_amount" type="number" step="0.01" 
                                label="USD Amount" prefix="$" required />
                        </div>
                    </div>

                    <!-- Local Currency Portion -->
                    <div class="p-4 bg-orange-50 rounded-lg border border-orange-200">
                        <h4 class="font-semibold text-orange-800 mb-3 flex items-center gap-2">
                            <x-icon name="o-banknotes" class="w-5 h-5" />
                            Local Currency Portion ({{ number_format($local_percentage, 0) }}%)
                        </h4>
                        <div class="grid grid-cols-2 gap-4 mb-3">
                            <x-select wire:model.live="local_currency_id" label="Currency" 
                                :options="$this->nonUsdCurrencies" 
                                option-value="id" 
                                option-label="name"
                                placeholder="Select local currency" required />
                            <div>
                                <label class="label text-sm">Percentage</label>
                                <div class="flex items-center gap-2">
                                    <input type="range" wire:model.live="local_percentage" min="0" max="100" step="5" 
                                        class="range range-warning range-sm flex-1" />
                                    <span class="font-bold text-orange-700 w-16 text-right">{{ number_format($local_percentage, 0) }}%</span>
                                </div>
                            </div>
                        </div>

                        @if($local_currency_id)
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Exchange Rate <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.live="local_exchangerate_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                                    required>
                                    <option value="">Select exchange rate</option>
                                    @foreach($this->availableLocalExchangeRates as $rate)
                                        <option value="{{ $rate->id }}">
                                            Rate: {{ number_format($rate->value, 4) }} - {{ $rate->created_at->format('M d, Y') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if($local_exchange_rate_used)
                                <div class="p-3 bg-white rounded border">
                                    <div class="text-sm mb-2">
                                        <strong>Rate:</strong> 1 USD = {{ number_format($local_exchange_rate_used, 4) }} {{ $this->localCurrency->name }}
                                    </div>
                                    <div class="text-lg font-bold text-orange-700">
                                        {{ $this->localCurrency->name }} Amount: {{ number_format($local_amount, 2) }}
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Split Summary -->
                    @if($usd_amount > 0 || $local_amount > 0)
                        <div class="p-4 bg-gray-100 rounded-lg border-2 border-gray-300">
                            <h4 class="font-semibold text-gray-800 mb-2">Payment Summary</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>USD Payment:</span>
                                    <span class="font-semibold">${{ number_format($usd_amount, 2) }} ({{ number_format($usd_percentage, 0) }}%)</span>
                                </div>
                                @if($this->localCurrency && $local_amount > 0)
                                    <div class="flex justify-between">
                                        <span>{{ $this->localCurrency->name }} Payment:</span>
                                        <span class="font-semibold">{{ number_format($local_amount, 2) }} ({{ number_format($local_percentage, 0) }}%)</span>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500">
                                        <span>Local Equivalent in USD:</span>
                                        <span>${{ number_format($selectedAllowance->balance_due * ($local_percentage / 100), 2) }}</span>
                                    </div>
                                @endif
                                <hr class="my-2">
                                <div class="flex justify-between font-bold text-lg">
                                    <span>Total USD Covered:</span>
                                    <span class="text-green-600">${{ number_format($selectedAllowance->balance_due, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
            
            <div class="grid grid-cols-2 gap-4 mt-4">
                <x-input wire:model="payment_method" label="Payment Method" 
                    placeholder="e.g., Bank Transfer, Cheque, Cash" required />
                <x-input wire:model="payment_reference" label="Payment Reference" 
                    placeholder="e.g., TXN123456" required />
                <x-input wire:model="payment_date" type="date" label="Payment Date" required />
            </div>
            
            <div class="mt-4">
                <x-file wire:model="proof_of_payment" label="Proof of Payment" accept=".pdf,.jpg,.jpeg,.png" 
                    hint="Max 10MB. PDF, JPG, PNG" required />
            </div>
            <div class="mt-4">
                <x-textarea wire:model="payment_notes" label="Notes (Optional)" rows="3" 
                    placeholder="Any additional notes about this payment..." />
            </div>
            <x-slot:actions>
                <x-button class="btn-outline btn-error" label="Close" @click="$wire.paymentmodal = false" />
                <x-button class="btn-success" label="Execute Payment" type="submit" spinner="executepayment" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
