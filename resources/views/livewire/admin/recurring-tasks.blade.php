<div>
    <x-breadcrumbs :items="$breadcrumbs" 
        class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
        link-item-class="text-base" />

    <x-card title="My Recurring Tasks" class="mt-2 border-2 border-gray-200" separator>
        <x-slot:menu>
            <x-button 
                icon="o-plus" 
                label="New Recurring Task" 
                class="btn-primary btn-sm" 
                wire:click="openModal"
            />
        </x-slot:menu>

        @if($recurringTasks->count() > 0)
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Frequency</th>
                        <th>Schedule</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Next Create</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recurringTasks as $recurringTask)
                    <tr>
                        <td class="font-semibold">{{ $recurringTask->title }}</td>
                        <td>
                            <x-badge 
                                value="{{ ucfirst($recurringTask->frequency) }}" 
                                class="badge-sm badge-info" 
                            />
                        </td>
                        <td>
                            @if($recurringTask->frequency === 'weekly')
                                @php
                                    $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
                                    echo $days[$recurringTask->day_of_week] ?? 'Monday';
                                @endphp
                            @elseif($recurringTask->frequency === 'monthly')
                                Day {{ $recurringTask->day_of_month }}
                            @else
                                Every weekday
                            @endif
                        </td>
                        <td>{{ $recurringTask->start_date->format('M d, Y') }}</td>
                        <td>{{ $recurringTask->end_date ? $recurringTask->end_date->format('M d, Y') : 'No end date' }}</td>
                        <td>{{ $recurringTask->next_create_date->format('M d, Y') }}</td>
                        <td>
                            @if($recurringTask->is_active)
                                <x-badge value="Active" class="badge-sm badge-success" />
                            @else
                                <x-badge value="Inactive" class="badge-sm badge-ghost" />
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <x-button 
                                    icon="o-pencil" 
                                    class="btn-xs btn-outline btn-info" 
                                    wire:click="openModal({{ $recurringTask->id }})"
                                />
                                <x-button 
                                    icon="{{ $recurringTask->is_active ? 'o-pause' : 'o-play' }}" 
                                    class="btn-xs btn-outline {{ $recurringTask->is_active ? 'btn-warning' : 'btn-success' }}" 
                                    wire:click="toggleActive({{ $recurringTask->id }})"
                                />
                                <x-button 
                                    icon="o-trash" 
                                    class="btn-xs btn-outline btn-error" 
                                    wire:click="delete({{ $recurringTask->id }})"
                                    wire:confirm="Are you sure you want to delete this recurring task?"
                                />
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <div class="text-gray-500 mb-4">No recurring tasks found. Create your first recurring task to automate task creation!</div>
            <x-button 
                icon="o-plus" 
                label="Create Recurring Task" 
                class="btn-primary" 
                wire:click="openModal"
            />
        </div>
        @endif
    </x-card>

    <!-- Recurring Task Modal -->
    <x-modal wire:model="showModal" :title="$editingId ? 'Edit Recurring Task' : 'Create Recurring Task'" box-class="max-w-3xl">
        <x-form wire:submit="save">
            <div class="space-y-4">
                <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-blue-500 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>
                        <span class="font-semibold text-blue-900">Task Details</span>
                    </div>
                    <p class="text-sm text-blue-700">You can use a template or create a custom recurring task.</p>
                </div>

                <x-select 
                    wire:model="task_template_id" 
                    label="Use Template (Optional)" 
                    :options="$templates"
                    option-label="title"
                    option-value="id"
                    placeholder="Select a template or create custom..."
                />

                <x-input 
                    wire:model="title" 
                    label="Title" 
                    placeholder="Enter task title"
                />

                <x-textarea 
                    wire:model="description" 
                    label="Description" 
                    placeholder="Enter task description"
                    rows="3"
                />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-select 
                        wire:model="priority" 
                        label="Priority" 
                        :options="[
                            ['id' => 'High', 'name' => 'High'],
                            ['id' => 'Medium', 'name' => 'Medium'],
                            ['id' => 'Low', 'name' => 'Low'],
                        ]"
                        option-label="name"
                        option-value="id"
                    />

                    <x-input 
                        wire:model="duration" 
                        type="number" 
                        label="Duration" 
                        placeholder="0"
                        step="0.5"
                    />

                    <x-input 
                        wire:model="uom" 
                        label="Unit of Measure" 
                        placeholder="hours"
                    />
                </div>

                <x-select 
                    wire:model="individualworkplan_id" 
                    label="Link to Work Plan (Optional)" 
                    :options="$workplans"
                    option-label="display_name"
                    option-value="id"
                    placeholder="Select work plan..."
                />

                <div class="border-t pt-4">
                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-200 mb-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-amber-500 rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="font-semibold text-amber-900">Recurrence Schedule</span>
                        </div>
                    </div>

                    <x-select 
                        wire:model="frequency" 
                        label="Frequency" 
                        :options="[
                            ['id' => 'daily', 'name' => 'Daily (Every weekday)'],
                            ['id' => 'weekly', 'name' => 'Weekly'],
                            ['id' => 'monthly', 'name' => 'Monthly'],
                        ]"
                        option-label="name"
                        option-value="id"
                    />

                    @if($frequency === 'weekly')
                    <x-select 
                        wire:model="day_of_week" 
                        label="Day of Week" 
                        :options="[
                            ['id' => 1, 'name' => 'Monday'],
                            ['id' => 2, 'name' => 'Tuesday'],
                            ['id' => 3, 'name' => 'Wednesday'],
                            ['id' => 4, 'name' => 'Thursday'],
                            ['id' => 5, 'name' => 'Friday'],
                        ]"
                        option-label="name"
                        option-value="id"
                    />
                    @endif

                    @if($frequency === 'monthly')
                    <x-input 
                        wire:model="day_of_month" 
                        type="number" 
                        label="Day of Month" 
                        placeholder="1-31"
                        min="1"
                        max="31"
                    />
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input 
                            wire:model="start_date" 
                            type="date" 
                            label="Start Date" 
                        />

                        <x-input 
                            wire:model="end_date" 
                            type="date" 
                            label="End Date (Optional)" 
                        />
                    </div>

                    <x-checkbox 
                        wire:model="is_active" 
                        label="Active" 
                        hint="Inactive recurring tasks will not create new tasks"
                    />
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.closeModal()" />
                <x-button label="{{ $editingId ? 'Update' : 'Create' }} Recurring Task" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
