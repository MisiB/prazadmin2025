<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
    <!-- Breadcrumbs -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-gray-200 px-4 py-3">
        <div class="max-w-7xl mx-auto">
            <x-breadcrumbs :items="$breadcrumbs" 
                class="bg-gray-50 p-3 rounded-xl overflow-x-auto whitespace-nowrap"
                link-item-class="text-base hover:text-blue-600 transition-colors" />
        </div>
    </div>

    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm shadow-sm border-b border-gray-200 mb-6 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-3 tracking-tight">Knowledge Base Management</h1>
                    <p class="text-gray-600">Create and manage help articles</p>
                </div>
                
<div>
                    <x-button 
                        icon="o-plus" 
                        label="Create Article" 
                        wire:click="openModal" 
                        class="btn-primary shadow-lg shadow-blue-500/30" 
                    />
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <!-- Articles Grid -->
        <div class="grid gap-4">
            @forelse($articles as $article)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $article->title }}</h3>
                                @if($article->excerpt)
                                <p class="text-sm text-gray-600">{{ Str::limit($article->excerpt, 100) }}</p>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $article->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($article->status) }}
                                </span>
                                @if($article->is_featured)
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    Featured
                                </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-4 text-sm text-gray-600 mb-4">
                            @if($article->category)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                {{ $article->category }}
                            </span>
                            @endif
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ $article->views_count }} views
                            </span>
                            @if($article->external_url)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                External Link
                            </span>
                            @endif
                            <span>{{ $article->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="flex gap-2">
                            <x-button 
                                icon="o-pencil" 
                                label="Edit" 
                                wire:click="edit({{ $article->id }})" 
                                class="btn-outline btn-sm"
                            />
                            <x-button 
                                icon="o-trash" 
                                label="Delete" 
                                wire:click="delete({{ $article->id }})" 
                                wire:confirm="Are you sure you want to delete this article?"
                                class="btn-outline btn-error btn-sm"
                            />
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <div class="bg-blue-50 rounded-2xl p-12 border border-blue-200 inline-block">
                        <svg class="w-20 h-20 mx-auto text-blue-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No Articles Found</h3>
                        <p class="text-gray-600 mb-4">Create your first knowledge base article</p>
                        <x-button 
                            icon="o-plus" 
                            label="Create Article" 
                            wire:click="openModal" 
                            class="btn-primary"
                        />
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $articles->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <x-modal wire:model="showModal" title="{{ $editingId ? 'Edit Article' : 'Create Article' }}" separator box-class="max-w-4xl">
        <x-form wire:submit="save">
            <div class="space-y-5">
                <x-input 
                    wire:model="title" 
                    label="Title" 
                    placeholder="Article title"
                    icon="o-document-text"
                />

                <x-input 
                    wire:model="slug" 
                    label="Slug (Optional)" 
                    placeholder="article-slug"
                    hint="Auto-generated if left empty"
                    icon="o-link"
                />

                <x-textarea 
                    wire:model="excerpt" 
                    label="Excerpt (Optional)" 
                    placeholder="Brief summary..."
                    rows="2"
                />

                <x-textarea 
                    wire:model="content" 
                    label="Content" 
                    placeholder="Article content..."
                    rows="10"
                />

                <x-input 
                    wire:model="external_url" 
                    label="External URL (Optional)" 
                    placeholder="https://example.com/resource"
                    hint="Link to external resource or documentation"
                    icon="o-globe-alt"
                    type="url"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        wire:model="category" 
                        label="Category (Optional)" 
                        placeholder="Category"
                        icon="o-tag"
                    />

                    <x-select 
                        wire:model="status" 
                        label="Status" 
                        :options="[
                            ['id' => 'draft', 'name' => 'Draft'],
                            ['id' => 'published', 'name' => 'Published']
                        ]" 
                        option-label="name" 
                        option-value="id"
                    />
                </div>

                <x-checkbox 
                    wire:model="is_featured" 
                    label="Featured Article" 
                />
            </div>

            <x-slot name="actions">
                <x-button 
                    label="Cancel" 
                    wire:click="closeModal" 
                    class="btn-outline" 
                />
                <x-button 
                    label="{{ $editingId ? 'Update' : 'Create' }}" 
                    type="submit" 
                    class="btn-primary shadow-lg shadow-blue-500/30" 
                    spinner="save"
                    icon="o-check"
                />
            </x-slot>
        </x-form>
    </x-modal>
</div>
