<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
    <!-- Modern Breadcrumbs -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-gray-200 px-4 py-3">
        <div class="max-w-7xl mx-auto">
            <x-breadcrumbs :items="$breadcrumbs" 
                class="bg-gray-50 p-3 rounded-xl overflow-x-auto whitespace-nowrap"
                link-item-class="text-base hover:text-blue-600 transition-colors" />
        </div>
    </div>

    <!-- Modern Header -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 shadow-xl border-b border-blue-700 mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-white mb-3 tracking-tight flex items-center gap-3">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        Weekly Task Review
                    </h1>
                    <p class="text-indigo-100 text-lg">
                        Review Period: {{ \Carbon\Carbon::parse($weekStartDate)->format('M d') }} - {{ \Carbon\Carbon::parse($weekEndDate)->format('M d, Y') }}
                    </p>
                </div>
                @if($isSubmitted)
                <div class="flex items-center gap-2 bg-green-500/20 backdrop-blur-sm px-5 py-3 rounded-xl border border-green-400">
                    <svg class="w-6 h-6 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-green-200 font-semibold">Review Submitted</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        @if(count($tasks) === 0)
            <!-- No Tasks Message -->
            <div class="text-center py-16">
                <div class="bg-gray-50 rounded-2xl p-12 border border-gray-200 inline-block">
                    <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">No Tasks to Review</h3>
                    <p class="text-gray-600">You had no tasks scheduled for the previous week</p>
                </div>
            </div>
        @else
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-gray-100 text-sm font-semibold uppercase tracking-wide">Total Tasks</p>
                        <p class="text-5xl font-bold">{{ $totalTasks }}</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-green-100 text-sm font-semibold uppercase tracking-wide">Completed</p>
                        <p class="text-5xl font-bold">{{ $completedTasks }}</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-orange-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-yellow-100 text-sm font-semibold uppercase tracking-wide">Incomplete</p>
                        <p class="text-5xl font-bold">{{ $incompleteTasks }}</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-blue-100 text-sm font-semibold uppercase tracking-wide">Completion Rate</p>
                        <p class="text-5xl font-bold">{{ number_format($completionRate, 1) }}%</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white transform hover:scale-105 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <p class="text-purple-100 text-sm font-semibold uppercase tracking-wide">Hours Completed</p>
                        <p class="text-5xl font-bold">{{ number_format($totalHoursCompleted, 1) }}</p>
                        <p class="text-purple-200 text-xs">of {{ number_format($totalHoursPlanned, 1) }} planned</p>
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-bold text-gray-900">Weekly Progress</h3>
                    <span class="text-2xl font-bold text-indigo-600">{{ number_format($completionRate, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-6 overflow-hidden">
                    <div class="h-6 bg-gradient-to-r from-green-500 via-blue-500 to-purple-600 rounded-full transition-all duration-500 flex items-center justify-end pr-2"
                         style="width: {{ $completionRate }}%">
                        @if($completionRate > 10)
                        <span class="text-xs font-bold text-white">{{ number_format($completionRate, 1) }}%</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tasks Review List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        Review Your Tasks
                    </h2>
                    <p class="text-indigo-100 mt-1">Mark tasks as completed and add comments for incomplete items</p>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($taskReviews as $index => $review)
                        <div class="bg-gradient-to-br from-gray-50 to-white rounded-xl border-2 {{ $review['was_completed'] ? 'border-green-300' : 'border-gray-200' }} overflow-hidden hover:shadow-md transition-all duration-300">
                            <div class="p-5">
                                <div class="flex items-start gap-4">
                                    <!-- Checkbox -->
                                    <div class="flex-shrink-0 mt-1">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" 
                                                   wire:click="toggleTaskCompletion({{ $index }})"
                                                   @if($isSubmitted) disabled @endif
                                                   @if($review['was_completed']) checked @endif
                                                   class="sr-only peer">
                                            <div class="w-7 h-7 border-2 rounded-lg {{ $review['was_completed'] ? 'bg-green-500 border-green-500' : 'bg-white border-gray-300' }} peer-focus:ring-4 peer-focus:ring-green-300 flex items-center justify-center transition-all">
                                                @if($review['was_completed'])
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                                @endif
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Task Details -->
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between mb-2">
                                            <h4 class="text-lg font-bold text-gray-900 {{ $review['was_completed'] ? 'line-through text-gray-500' : '' }}">
                                                {{ $review['task_name'] }}
                                            </h4>
                                            <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                                    {{ $review['day'] }}
                                                </span>
                                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-semibold">
                                                    {{ $review['hours'] }}h
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Comment Box for Incomplete Tasks -->
                                        @if(!$review['was_completed'])
                                        <div class="mt-3">
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                Why was this task not completed?
                                            </label>
                                            <x-textarea 
                                                wire:model.blur="taskReviews.{{ $index }}.completion_comment"
                                                placeholder="Add comment explaining why this task wasn't completed..."
                                                rows="2"
                                                @if($isSubmitted) disabled @endif
                                                class="w-full"
                                            />
                                        </div>
                                        @else
                                        <p class="text-sm text-green-600 font-semibold mt-2">✓ Marked as completed</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Overall Comment -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                    Overall Week Comment
                </h3>
                <x-textarea 
                    wire:model="overallComment"
                    placeholder="Add any overall comments about your week (optional)..."
                    rows="4"
                    @if($isSubmitted) disabled @endif
                    hint="Reflect on your week, challenges faced, achievements, etc."
                />
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-4">
                @if(!$isSubmitted)
                <x-button 
                    icon="o-document" 
                    label="Save Draft" 
                    wire:click="saveDraft" 
                    class="btn-outline btn-lg"
                    spinner="saveDraft"
                />
                <x-button 
                    icon="o-check-circle" 
                    label="Submit Review" 
                    wire:click="submitReview" 
                    class="btn-primary btn-lg shadow-lg shadow-indigo-500/30"
                    spinner="submitReview"
                    wire:confirm="Are you sure you want to submit this review? You won't be able to edit it after submission."
                />
                @else
                <div class="flex items-center gap-2 bg-green-50 px-6 py-3 rounded-xl border border-green-200">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-green-900 font-semibold">Review submitted successfully on {{ $existingReview->reviewed_at->format('M d, Y H:i') }}</span>
                </div>
                @endif
            </div>
        @endif
    </div>
</div>
