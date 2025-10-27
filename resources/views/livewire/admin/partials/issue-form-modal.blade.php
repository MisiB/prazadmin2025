<!-- Create/Edit Ticket Modal -->
<x-modal wire:model="showModal" title="{{ $editingId ? 'Edit Ticket' : 'Create New Ticket' }}" separator box-class="max-w-4xl">
   
    
    <x-form wire:submit="save">
        <div class="space-y-5">
            <!-- Ticket Information -->
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                <h3 class="text-sm font-bold text-blue-900 mb-3">Ticket Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-select 
                        wire:model="issuegroup_id" 
                        label="Issue Group" 
                        placeholder="Select issue group" 
                        :options="$issuegroups" 
                        option-label="name" 
                        option-value="id"
                        icon="o-rectangle-stack"
                    />
                    
                    <x-select 
                        wire:model="issuetype_id" 
                        label="Issue Type" 
                        placeholder="Select issue type" 
                        :options="$issuetypes" 
                        option-label="name" 
                        option-value="id"
                        icon="o-tag"
                    />
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Contact Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        wire:model="name" 
                        label="Full Name" 
                        placeholder="Enter your name"
                        icon="o-user"
                    />
                    
                    <x-input 
                        wire:model="email" 
                        label="Email Address" 
                        type="email"
                        placeholder="your@email.com"
                        icon="o-envelope"
                    />
                    
                    <x-input 
                        wire:model="phone" 
                        label="Phone Number" 
                        placeholder="+263..."
                        icon="o-phone"
                    />
                    
                    <x-input 
                        wire:model="regnumber" 
                        label="Registration Number (Optional)" 
                        placeholder="REG-XXXXX"
                        icon="o-identification"
                    />
                </div>
            </div>

            <!-- Issue Details -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Issue Details</h3>
                
                <div class="mb-4">
                    <x-input 
                        wire:model="title" 
                        label="Issue Title" 
                        placeholder="Brief description of the issue"
                        hint="Provide a clear, concise title"
                        icon="o-document-text"
                    />
                </div>

                <div class="mb-4">
                    <x-textarea 
                        wire:model="description" 
                        label="Detailed Description" 
                        placeholder="Describe the issue in detail..."
                        rows="5"
                        hint="Include all relevant information to help us resolve your issue quickly"
                    />
                </div>

                <x-select 
                    wire:model="priority" 
                    label="Priority Level" 
                    :options="[
                        ['id' => 'Low', 'name' => 'Low - Can wait'],
                        ['id' => 'Medium', 'name' => 'Medium - Normal priority'],
                        ['id' => 'High', 'name' => 'High - Urgent']
                    ]" 
                    option-label="name" 
                    option-value="id"
                    icon="o-flag"
                />
            </div>

            <!-- Attachments Section -->
            <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
                <h3 class="text-sm font-bold text-purple-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    Supporting Images (Optional)
                </h3>
                
                <div class="mb-3">
                    <input 
                        type="file" 
                        wire:model="attachments" 
                        multiple 
                        accept="image/*"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-500 file:text-white hover:file:bg-purple-600"
                    />
                    <p class="text-xs text-gray-500 mt-2">Upload images (PNG, JPG, GIF). Max 5MB per file.</p>
                </div>

                <!-- Loading indicator -->
                <div wire:loading wire:target="attachments" class="flex items-center gap-2 text-purple-600 text-sm">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Uploading...
                </div>

                <!-- Preview existing attachments -->
                @if($existingAttachments && count($existingAttachments) > 0)
                <div class="mt-4">
                    <h4 class="text-xs font-semibold text-gray-700 mb-2">Existing Attachments:</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($existingAttachments as $index => $attachment)
                            @if(is_array($attachment) && isset($attachment['path']))
                            <div class="relative group">
                                <img 
                                    src="{{ Storage::url($attachment['path']) }}" 
                                    alt="{{ $attachment['original_name'] ?? 'Attachment' }}" 
                                    class="w-full h-24 object-cover rounded-lg border border-gray-200"
                                />
                                <button 
                                    type="button"
                                    wire:click="removeAttachment({{ $index }})"
                                    class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            @elseif(is_string($attachment))
                            <div class="relative group">
                                <img 
                                    src="{{ Storage::url($attachment) }}" 
                                    alt="Attachment" 
                                    class="w-full h-24 object-cover rounded-lg border border-gray-200"
                                />
                                <button 
                                    type="button"
                                    wire:click="removeAttachment({{ $index }})"
                                    class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Preview new attachments -->
                @if($attachments && count($attachments) > 0)
                <div class="mt-4">
                    <h4 class="text-xs font-semibold text-gray-700 mb-2">New Attachments to Upload:</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($attachments as $attachment)
                        <div class="relative">
                            <img 
                                src="{{ $attachment->temporaryUrl() }}" 
                                alt="Preview" 
                                class="w-full h-24 object-cover rounded-lg border-2 border-purple-300"
                            />
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <x-slot name="actions">
            <x-button 
                label="Cancel" 
                wire:click="closeModal" 
                class="btn-outline" 
            />
            <x-button 
                label="{{ $editingId ? 'Update Ticket' : 'Create Ticket' }}" 
                type="submit" 
                class="btn-primary shadow-lg shadow-blue-500/30" 
                spinner="save"
                icon="o-check"
            />
        </x-slot>
    </x-form>
</x-modal>

