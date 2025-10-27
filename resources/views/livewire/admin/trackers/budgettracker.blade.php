<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
    <!-- Modern Breadcrumbs -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-gray-200 px-4 py-3">
        <div class="max-w-7xl mx-auto">
            <x-breadcrumbs :items="$breadcrumbs" 
                class="bg-gray-50 p-3 rounded-xl overflow-x-auto whitespace-nowrap"
                link-item-class="text-base hover:text-blue-600 transition-colors" />
        </div>
    </div>

    <!-- Modern Header with Glassmorphism -->
    <div class="bg-white/80 backdrop-blur-sm shadow-sm border-b border-gray-200 mb-6 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-3 tracking-tight">Department Budget Tracker</h1>
                    <p class="text-gray-600">Monitor and manage department budget allocations and spending</p>
                </div>
                
                @if(count($budgets) > 0)
                <div class="flex flex-col sm:flex-row gap-3">
                    <x-select wire:model.live="currentBudgetId" :options="$budgets" option-label="name" option-value="id" placeholder="Select Budget" class="min-w-[250px]" />
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        @if($currentBudgetId)
            <!-- Summary Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Budget Card -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-blue-100 text-sm font-semibold uppercase tracking-wide">Total Budget</p>
                        <p class="text-4xl font-bold">${{ number_format($budgetSummary['total_budget'], 0) }}</p>
                        <p class="text-blue-100 text-sm">{{ $budgetSummary['total_departments'] }} departments</p>
                </div>
            </div>

            <!-- Budget Spent Card -->
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-red-100 text-sm font-semibold uppercase tracking-wide">Budget Spent</p>
                        <p class="text-4xl font-bold">${{ number_format($budgetSummary['total_spent'], 0) }}</p>
                        <div class="mt-3">
                            <div class="w-full bg-white/20 rounded-full h-2 backdrop-blur-sm">
                                <div class="bg-white h-2 rounded-full transition-all duration-500" style="width: {{ $budgetSummary['percentage_spent'] }}%"></div>
                            </div>
                            <p class="text-red-100 text-sm mt-1">{{ $budgetSummary['percentage_spent'] }}% of total</p>
                    </div>
                </div>
            </div>

            <!-- Budget Remaining Card -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-green-100 text-sm font-semibold uppercase tracking-wide">Remaining</p>
                        <p class="text-4xl font-bold">${{ number_format($budgetSummary['total_remaining'], 0) }}</p>
                        <p class="text-green-100 text-sm">{{ 100 - $budgetSummary['percentage_spent'] }}% available</p>
                    </div>
                </div>

                <!-- Departments Card -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
            </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-purple-100 text-sm font-semibold uppercase tracking-wide">Departments</p>
                        <p class="text-4xl font-bold">{{ $budgetSummary['total_departments'] }}</p>
                        <p class="text-purple-100 text-sm">Active budgets</p>
                    </div>
                </div>
            </div>
        
        <!-- Department Budgets Section -->
            <div class="space-y-6">
                @forelse($departmentBudgets as $department)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Department Header -->
                        <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 px-6 py-5 border-b border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/30">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900">{{ $department['name'] }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">Department Budget Overview</p>
                                    </div>
                                </div>

                                <span class="px-4 py-2 rounded-lg font-semibold text-sm {{ $department['status']['color'] == 'green' ? 'bg-green-100 text-green-800' : ($department['status']['color'] == 'yellow' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $department['status']['label'] }}
                                </span>
                                        </div>

                            <!-- Department Stats Grid -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div class="bg-white rounded-xl px-4 py-3 border border-blue-200">
                                    <div class="flex items-center gap-2 text-blue-700 text-sm font-semibold mb-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Allocated
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900">${{ number_format($department['allocated'], 0) }}</div>
                                </div>
                                
                                <div class="bg-white rounded-xl px-4 py-3 border border-red-200">
                                    <div class="flex items-center gap-2 text-red-700 text-sm font-semibold mb-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                                        </svg>
                                        Spent
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900">${{ number_format($department['spent'], 0) }}</div>
                                </div>
                                
                                <div class="bg-white rounded-xl px-4 py-3 border border-green-200">
                                    <div class="flex items-center gap-2 text-green-700 text-sm font-semibold mb-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Remaining
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900">${{ number_format($department['remaining'], 0) }}</div>
                                </div>
                                
                                <div class="bg-white rounded-xl px-4 py-3 border border-purple-200">
                                    <div class="flex items-center gap-2 text-purple-700 text-sm font-semibold mb-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Items
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900">{{ $department['items_count'] }}</div>
                                        </div>
                                    </div>

                            <!-- Progress Bar -->
                            <div class="mt-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-semibold text-gray-700">Budget Utilization</span>
                                    <span class="text-sm font-bold {{ $department['status']['color'] == 'green' ? 'text-green-700' : ($department['status']['color'] == 'yellow' ? 'text-yellow-700' : 'text-red-700') }}">
                                        {{ $department['percentage'] }}%
                                    </span>
                                    </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div 
                                        class="h-3 rounded-full transition-all duration-500 {{ $department['status']['color'] == 'green' ? 'bg-gradient-to-r from-green-500 to-green-600' : ($department['status']['color'] == 'yellow' ? 'bg-gradient-to-r from-yellow-400 to-yellow-500' : 'bg-gradient-to-r from-red-500 to-red-600') }}" 
                                        style="width: {{ $department['percentage'] }}%"
                                    ></div>
                </div>
            </div>
        </div>

                        <!-- Budget Items Section -->
                        @php
                            $deptItems = $this->budgetRepository->getbudgetitemsbydepartment($currentBudgetId, $department['id']);
                        @endphp

                        @if($deptItems->count() > 0)
                        <div class="p-6 bg-gray-50">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-bold text-gray-900">Budget Items Breakdown</h4>
                                <span class="text-sm text-gray-600">{{ $deptItems->count() }} items</span>
                            </div>

                            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
                                <table class="w-full">
                                    <thead>
                                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                                            <th class="text-left py-4 px-4 font-semibold text-gray-700 text-sm uppercase tracking-wide">Expense Category</th>
                                            <th class="text-left py-4 px-4 font-semibold text-gray-700 text-sm uppercase tracking-wide">Activity</th>
                                            <th class="text-center py-4 px-4 font-semibold text-gray-700 text-sm uppercase tracking-wide">Qty × Price</th>
                                            <th class="text-right py-4 px-4 font-semibold text-gray-700 text-sm uppercase tracking-wide">Allocated</th>
                                            <th class="text-right py-4 px-4 font-semibold text-gray-700 text-sm uppercase tracking-wide">Spent</th>
                                            <th class="text-right py-4 px-4 font-semibold text-gray-700 text-sm uppercase tracking-wide">Remaining</th>
                                            <th class="text-center py-4 px-4 font-semibold text-gray-700 text-sm uppercase tracking-wide">Utilization</th>
                                            <th class="text-center py-4 px-4 font-semibold text-gray-700 text-sm uppercase tracking-wide">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($deptItems as $item)
                                            @php
                                                $itemUtil = $this->getBudgetItemUtilization($item);
                                            @endphp
                                            <tr class="hover:bg-blue-50 transition-colors duration-150">
                                                <!-- Expense Category -->
                                                <td class="py-4 px-4">
                                                    <div class="flex items-center gap-2">
                                                        <div class="p-2 bg-blue-100 rounded-lg">
                                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <div class="font-semibold text-gray-900">{{ $item->expensecategory->name ?? 'Uncategorized' }}</div>
                                                            @if($item->sourceoffund)
                                                            <div class="text-xs text-green-600 mt-0.5">{{ Str::limit($item->sourceoffund->name, 20) }}</div>
                                                            @endif
                        </div>
                    </div>
                                                </td>

                                                <!-- Activity -->
                                                <td class="py-4 px-4">
                                                    <div class="max-w-xs">
                                                        <p class="text-sm text-gray-900 font-medium">{{ $item->activity ?? 'N/A' }}</p>
                                                        @if($item->description)
                                                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($item->description, 50) }}</p>
                                                        @endif
                                                        @if($item->strategysubprogrammeoutput)
                                                        <div class="mt-1">
                                                            <span class="text-xs text-purple-600 bg-purple-50 px-2 py-0.5 rounded">{{ Str::limit($item->strategysubprogrammeoutput->name, 30) }}</span>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </td>

                                                <!-- Quantity × Price -->
                                                <td class="py-4 px-4 text-center">
                                                    @if($item->quantity && $item->unitprice)
                                                    <div class="text-sm">
                                                        <div class="font-semibold text-gray-900">{{ $item->quantity }} × ${{ number_format($item->unitprice, 2) }}</div>
                                                    </div>
                                                    @else
                                                    <span class="text-sm text-gray-400">-</span>
                                                    @endif
                                                </td>

                                                <!-- Allocated -->
                                                <td class="py-4 px-4 text-right">
                                                    <div>
                                                        <div class="text-base font-bold text-blue-900">${{ number_format($item->total, 2) }}</div>
                                                        <div class="text-xs text-gray-500">{{ $item->currency->code ?? 'USD' }}</div>
                                                    </div>
                                                </td>

                                                <!-- Spent -->
                                                <td class="py-4 px-4 text-right">
                                                    <div>
                                                        <div class="text-base font-bold text-red-900">${{ number_format($itemUtil['spent'], 2) }}</div>
                                                        <div class="text-xs text-red-600">{{ $itemUtil['percentage'] }}%</div>
                                                    </div>
                                                </td>

                                                <!-- Remaining -->
                                                <td class="py-4 px-4 text-right">
                                                    <div>
                                                        <div class="text-base font-bold {{ $itemUtil['remaining'] < 0 ? 'text-red-900' : 'text-green-900' }}">
                                                            ${{ number_format($itemUtil['remaining'], 2) }}
                                                        </div>
                                                        @if($itemUtil['remaining'] < 0)
                                                        <div class="text-xs text-red-600 font-semibold">⚠️ Overspent</div>
                                                        @endif
                                                    </div>
                                                </td>

                                                <!-- Utilization Progress Bar -->
                                                <td class="py-4 px-4">
                                                    <div class="flex flex-col items-center gap-1 min-w-[120px]">
                                                        <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                                            <div 
                                                                class="h-2.5 rounded-full transition-all duration-500 {{ $itemUtil['status']['color'] == 'green' ? 'bg-gradient-to-r from-green-500 to-green-600' : ($itemUtil['status']['color'] == 'yellow' ? 'bg-gradient-to-r from-yellow-400 to-yellow-500' : 'bg-gradient-to-r from-red-500 to-red-600') }}" 
                                                                style="width: {{ min($itemUtil['percentage'], 100) }}%"
                                                            ></div>
                                                        </div>
                                                        <span class="text-xs font-bold {{ $itemUtil['status']['color'] == 'green' ? 'text-green-700' : ($itemUtil['status']['color'] == 'yellow' ? 'text-yellow-700' : 'text-red-700') }}">
                                                            {{ $itemUtil['percentage'] }}%
                                                        </span>
                                                    </div>
                                                </td>

                                                <!-- Status -->
                                                <td class="py-4 px-4 text-center">
                                                    <div class="flex flex-col gap-1 items-center">
                                                        @if($item->status)
                                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $item->status == 'approved' ? 'bg-green-100 text-green-800' : ($item->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                                            {{ ucfirst($item->status) }}
                                                        </span>
                                                        @endif
                                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $itemUtil['status']['color'] == 'green' ? 'bg-green-100 text-green-800' : ($itemUtil['status']['color'] == 'yellow' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                            {{ $itemUtil['status']['label'] }}
                                                        </span>
                        </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gradient-to-r from-blue-50 to-indigo-50 border-t-2 border-blue-200">
                                            <td colspan="3" class="py-4 px-4 text-right font-bold text-gray-900 text-base">
                                                Department Total:
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                <div class="text-lg font-bold text-blue-900">${{ number_format($deptItems->sum('total'), 2) }}</div>
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                @php
                                                    $deptTotalSpent = 0;
                                                    foreach($deptItems as $item) {
                                                        $deptTotalSpent += $this->getBudgetItemSpent($item->id);
                                                    }
                                                @endphp
                                                <div class="text-lg font-bold text-red-900">${{ number_format($deptTotalSpent, 2) }}</div>
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                <div class="text-lg font-bold text-green-900">${{ number_format($deptItems->sum('total') - $deptTotalSpent, 2) }}</div>
                                            </td>
                                            <td colspan="2" class="py-4 px-4 text-center">
                                                @php
                                                    $deptPercentage = $deptItems->sum('total') > 0 ? round(($deptTotalSpent / $deptItems->sum('total')) * 100, 1) : 0;
                                                @endphp
                                                <span class="text-lg font-bold text-gray-900">{{ $deptPercentage }}%</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="bg-gray-50 rounded-2xl p-12 border border-gray-200 inline-block">
                            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">No Department Budgets</h3>
                            <p class="text-gray-600">No budget allocations have been assigned to departments yet.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        @else
            <div class="text-center py-16">
                <div class="bg-blue-50 rounded-2xl p-12 border border-blue-200 inline-block">
                    <svg class="w-20 h-20 mx-auto text-blue-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No Budget Selected</h3>
                    <p class="text-gray-600">Please select a budget from the dropdown above to view department allocations.</p>
                </div>
            </div>
        @endif
    </div>

</div>
