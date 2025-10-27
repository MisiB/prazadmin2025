<?php

namespace App\Livewire\Admin;

use App\Interfaces\repositories\iknowledgeBaseInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class KnowledgeBaseManagement extends Component
{
    use Toast, WithPagination;

    public $breadcrumbs = [];

    public $title;

    public $slug;

    public $excerpt;

    public $content;

    public $external_url;

    public $category;

    public $tags = [];

    public $status = 'draft';

    public $is_featured = false;

    public $showModal = false;

    public $editingId = null;

    public $search = '';

    public $filterStatus = '';

    public $filterCategory = '';

    protected $knowledgeBaseRepository;

    public function boot(iknowledgeBaseInterface $knowledgeBaseRepository)
    {
        $this->knowledgeBaseRepository = $knowledgeBaseRepository;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Knowledge Base'],
        ];
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'editingId',
            'title',
            'slug',
            'excerpt',
            'content',
            'external_url',
            'category',
            'tags',
            'status',
            'is_featured',
        ]);
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'external_url' => 'nullable|url|max:500',
            'excerpt' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published',
        ]);

        $data = [
            'title' => $this->title,
            'slug' => $this->slug ?: null,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'external_url' => $this->external_url,
            'category' => $this->category,
            'tags' => $this->tags,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'author_id' => Auth::id(),
        ];

        if ($this->status === 'published' && ! $this->editingId) {
            $data['published_at'] = now();
        }

        if ($this->editingId) {
            $response = $this->knowledgeBaseRepository->update($this->editingId, $data);
        } else {
            $response = $this->knowledgeBaseRepository->create($data);
        }

        if ($response['status'] === 'success') {
            $this->success($response['message']);
            $this->closeModal();
        } else {
            $this->error($response['message']);
        }
    }

    public function edit($id)
    {
        $article = $this->knowledgeBaseRepository->getById($id);

        $this->editingId = $article->id;
        $this->title = $article->title;
        $this->slug = $article->slug;
        $this->excerpt = $article->excerpt;
        $this->content = $article->content;
        $this->external_url = $article->external_url;
        $this->category = $article->category;
        $this->tags = $article->tags ?? [];
        $this->status = $article->status;
        $this->is_featured = $article->is_featured;

        $this->showModal = true;
    }

    public function delete($id)
    {
        $response = $this->knowledgeBaseRepository->delete($id);

        if ($response['status'] === 'success') {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function render()
    {
        $articles = $this->knowledgeBaseRepository->getAll(15);

        return view('livewire.admin.knowledge-base-management', [
            'articles' => $articles,
            'categories' => $this->knowledgeBaseRepository->getCategories(),
        ]);
    }
}
