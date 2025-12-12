<div>
    <x-breadcrumbs :items="$breadcrumbs" 
        class="bg-base-300 p-3 mt-2 rounded-box overflow-x-auto whitespace-nowrap"
        link-item-class="text-base" />

    <x-card title="My Task Templates" class="mt-2 border-2 border-gray-200" separator>
        <x-slot:menu>
            <x-button 
                icon="o-plus" 
                label="New Template" 
                class="btn-primary btn-sm" 
                wire:click="openModal"
            />
        </x-slot:menu>

        @if($templates->count() > 0)
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Priority</th>
                        <th>Duration</th>
                        <th>UOM</th>
                        <th>Linked</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                    <tr>
                        <td class="font-semibold">{{ $template->title }}</td>
                        <td class="max-w-xs truncate">{{ $template->description }}</td>
                        <td>
                            <x-badge 
                                value="{{ $template->priority }}" 
                                class="badge-sm {{ $template->priority == 'High' ? 'badge-error' : ($template->priority == 'Medium' ? 'badge-warning' : 'badge-success') }}" 
                            />
                        </td>
                        <td>{{ $template->duration }}</td>
                        <td>{{ $template->uom }}</td>
                        <td>
                            @if($template->individualworkplan_id)
                                <x-badge value="Yes" class="badge-sm badge-success" />
                            @else
                                <x-badge value="No" class="badge-sm badge-ghost" />
                            @endif
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <x-button 
                                    icon="o-pencil" 
                                    class="btn-xs btn-outline btn-info" 
                                    wire:click="openModal({{ $template->id }})"
                                />
                                <x-button 
                                    icon="o-trash" 
                                    class="btn-xs btn-outline btn-error" 
                                    wire:click="delete({{ $template->id }})"
                                    wire:confirm="Are you sure you want to delete this template?"
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
            <div class="text-gray-500 mb-4">No templates found. Create your first template to get started!</div>
            <x-button 
                icon="o-plus" 
                label="Create Template" 
                class="btn-primary" 
                wire:click="openModal"
            />
        </div>
        @endif
    </x-card>

    <!-- Template Modal -->
    <x-modal wire:model="showModal" :title="$editingId ? 'Edit Template' : 'Create Template'" box-class="max-w-2xl">
        <x-form wire:submit="save">
            <div class="space-y-4">
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
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.closeModal()" />
                <x-button label="{{ $editingId ? 'Update' : 'Create' }} Template" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
