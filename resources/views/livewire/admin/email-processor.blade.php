<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <x-card class="border-4 border-dashed border-gray-200 rounded-lg p-6" title="Email Processor" wire:poll.10s>
                <x-hr/>

                <!-- Authentication Status -->
                <div class="mb-6">
                    @if($isAuthenticated)
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded dark:bg-green-800 dark:border-green-600 dark:text-green-200">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $statusMessage }}
                            </div>
                        </div>
                    @else
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded dark:bg-red-800 dark:border-red-600 dark:text-red-200">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $statusMessage }}
                            </div>
                            <div class="mt-2">
                                <a href="{{ config('app.url') }}/connect" target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 underline dark:text-blue-400 dark:hover:text-blue-300">
                                    Click here to authenticate
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Configuration -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Configuration</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="supportEmail" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Support Email</label>
                            <input type="email" 
                                   wire:model="supportEmail" 
                                   id="supportEmail"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label for="emailLimit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Limit</label>
                            <input type="number" 
                                   wire:model="emailLimit" 
                                   id="emailLimit"
                                   min="1" max="50"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Actions</h2>
                    <div class="flex flex-wrap gap-4">
                        <button wire:click="checkAuthentication" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Check Authentication
                        </button>
                        
                        <button wire:click="fetchEmails" 
                                wire:loading.attr="disabled"
                                wire:target="fetchEmails"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                            <span wire:loading.remove wire:target="fetchEmails">Fetch Emails</span>
                            <span wire:loading wire:target="fetchEmails">Fetching...</span>
                        </button>
                        
                        <button wire:click="processAllEmails" 
                                wire:loading.attr="disabled"
                                wire:target="processAllEmails"
                                class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                            <span wire:loading.remove wire:target="processAllEmails">Process All</span>
                            <span wire:loading wire:target="processAllEmails">Processing...</span>
                        </button>
                        
                        <button wire:click="refreshToken" 
                                class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                            Refresh Token
                        </button>
                    </div>
                </div>

                <!-- Auto-Processing Controls -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Auto-Processing</h2>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       wire:model="autoProcessingEnabled" 
                                       wire:click="toggleAutoProcessing"
                                       id="autoProcessing"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="autoProcessing" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                    Enable automatic email processing
                                </label>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Checks every {{ $autoProcessingInterval }} minutes
                            </div>
                        </div>
                        @if($lastAutoProcessTime)
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Last processed: {{ $lastAutoProcessTime }}
                            </div>
                        @endif
                    </div>
                    @if($autoProcessingEnabled)
                        <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-md">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-blue-800 dark:text-blue-200">
                                    Auto-processing is active. Unclassifiable emails will be automatically classified as "General" tickets.
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Status Messages -->
                @if($statusMessage || $errorMessage)
                    <div class="mb-6">
                        @if($statusMessage)
                            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded dark:bg-blue-800 dark:border-blue-600 dark:text-blue-200 mb-2">
                                {{ $statusMessage }}
                            </div>
                        @endif
                        
                        @if($errorMessage)
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded dark:bg-red-800 dark:border-red-600 dark:text-red-200 mb-2">
                                {{ $errorMessage }}
                            </div>
                        @endif
                        
                        <button wire:click="clearMessages" 
                                class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            Clear Messages
                        </button>
                    </div>
                @endif

                <!-- Emails List -->
                @if(count($emails) > 0)
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Emails ({{ count($emails) }})
                        </h2>
                        
                        <div class="space-y-4">
                            @foreach($emails as $index => $email)
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 {{ $email['processed'] ? 'bg-green-50 dark:bg-green-900' : 'bg-gray-50 dark:bg-gray-700' }}">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                                {{ $email['subject'] }}
                                                @if($email['processed'])
                                                    <span class="ml-2 text-green-600 dark:text-green-400 text-sm">✓ Processed</span>
                                                @endif
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                From: {{ $email['sender_name'] }} ({{ $email['sender_email'] }})
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-500">
                                                Received: {{ \Carbon\Carbon::parse($email['received_at'])->format('M d, Y H:i') }}
                                            </p>
                                            @if($email['has_attachments'] && count($email['attachments']) > 0)
                                                <div class="mt-2">
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Attachments:</span>
                                                    <div class="flex flex-wrap gap-2 mt-1">
                                                        @foreach($email['attachments'] as $attachment)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                {{ $attachment['name'] }}
                                                                <span class="ml-1 text-gray-500">({{ number_format($attachment['size'] / 1024, 1) }}KB)</span>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                            <p class="text-sm text-gray-700 dark:text-gray-300 mt-2">
                                                {{ Str::limit(strip_tags(html_entity_decode($email['body'], ENT_QUOTES, 'UTF-8')), 200) }}
                                            </p>
                                        </div>
                                        
                                        <div class="flex space-x-2 ml-4">
                                            <button wire:click="analyzeEmail({{ $index }})" 
                                                    class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-1 px-3 rounded">
                                                Analyze
                                            </button>
                                            
                                            @if(!$email['processed'])
                                                <button wire:click="createTicket({{ $index }})" 
                                                        class="bg-green-500 hover:bg-green-700 text-white text-sm font-bold py-1 px-3 rounded">
                                                    Create Ticket
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Processed Tickets -->
                @if(count($processedEmails) > 0)
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Created Tickets ({{ count($processedEmails) }})
                        </h2>
                        
                        <div class="space-y-4">
                            @foreach($processedEmails as $processed)
                                <div class="border border-green-200 dark:border-green-600 rounded-lg p-4 bg-green-50 dark:bg-green-900">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                        Ticket: {{ $processed['ticket']->ticketnumber }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Priority: {{ ucfirst($processed['analysis']['priority']) }} | 
                                        Type: {{ $processed['analysis']['issue_type'] }} |
                                        Confidence: {{ $processed['analysis']['confidence_score'] }}%
                                    </p>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-2">
                                        {{ $processed['ticket']->title }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                </x-card>
        </div>
    </div>

    <!-- Analysis Modal -->
    @if($showAnalysis)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Email Analysis</h3>
                        <button wire:click="closeAnalysis" 
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    @if(count($analysisResult) > 0)
                        <div class="space-y-4">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white">Email Details</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Subject: {{ $selectedEmail['subject'] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">From: {{ $selectedEmail['sender_email'] }}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">Should Create Ticket:</span>
                                    <span class="ml-2 {{ $analysisResult['should_create_ticket'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $analysisResult['should_create_ticket'] ? 'Yes' : 'No' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">Priority:</span>
                                    <span class="ml-2 text-gray-600 dark:text-gray-400">{{ ucfirst($analysisResult['priority']) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">Issue Type:</span>
                                    <span class="ml-2 text-gray-600 dark:text-gray-400">{{ $analysisResult['issue_type'] }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">Confidence:</span>
                                    <span class="ml-2 text-gray-600 dark:text-gray-400">{{ $analysisResult['confidence_score'] }}%</span>
                                </div>
                            </div>
                            
                            <div>
                                <span class="font-medium text-gray-900 dark:text-white">Extracted Title:</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $analysisResult['extracted_title'] }}</p>
</div>

                            <div>
                                <span class="font-medium text-gray-900 dark:text-white">Extracted Description:</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $analysisResult['extracted_description'] }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('livewire:init', function () {
    let autoProcessingInterval = null;

    Livewire.on('start-auto-processing', (event) => {
        if (autoProcessingInterval) {
            clearInterval(autoProcessingInterval);
        }
        
        const interval = event.interval || 180000; // Default 3 minutes
        
        autoProcessingInterval = setInterval(() => {
            Livewire.dispatch('autoProcessEmails');
        }, interval);
        
        console.log('Auto-processing started with interval:', interval + 'ms');
    });

    Livewire.on('stop-auto-processing', () => {
        if (autoProcessingInterval) {
            clearInterval(autoProcessingInterval);
            autoProcessingInterval = null;
        }
        console.log('Auto-processing stopped');
    });

    // Clean up on page unload
    window.addEventListener('beforeunload', () => {
        if (autoProcessingInterval) {
            clearInterval(autoProcessingInterval);
        }
    });
});
</script>