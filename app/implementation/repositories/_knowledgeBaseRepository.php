<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iknowledgeBaseInterface;
use App\Models\KnowledgeBase;

class _knowledgeBaseRepository implements iknowledgeBaseInterface
{
    protected $knowledgeBase;

    public function __construct(KnowledgeBase $knowledgeBase)
    {
        $this->knowledgeBase = $knowledgeBase;
    }

    public function getAll($perPage = 15)
    {
        return $this->knowledgeBase
            ->with('author')
            ->latest()
            ->paginate($perPage);
    }

    public function getAllPublished($perPage = 15)
    {
        return $this->knowledgeBase
            ->with('author')
            ->published()
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function getById($id)
    {
        return $this->knowledgeBase->with('author')->findOrFail($id);
    }

    public function getBySlug($slug)
    {
        return $this->knowledgeBase
            ->with('author')
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();
    }

    public function create(array $data): array
    {
        try {
            $knowledgeBase = $this->knowledgeBase->create($data);

            return [
                'status' => 'success',
                'message' => 'Knowledge base article created successfully',
                'data' => $knowledgeBase,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to create article: '.$e->getMessage(),
            ];
        }
    }

    public function update($id, array $data): array
    {
        try {
            $knowledgeBase = $this->knowledgeBase->findOrFail($id);
            $knowledgeBase->update($data);

            return [
                'status' => 'success',
                'message' => 'Knowledge base article updated successfully',
                'data' => $knowledgeBase->fresh(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to update article: '.$e->getMessage(),
            ];
        }
    }

    public function delete($id): array
    {
        try {
            $knowledgeBase = $this->knowledgeBase->findOrFail($id);
            $knowledgeBase->delete();

            return [
                'status' => 'success',
                'message' => 'Knowledge base article deleted successfully',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to delete article: '.$e->getMessage(),
            ];
        }
    }

    public function search($query, $perPage = 15)
    {
        return $this->knowledgeBase
            ->with('author')
            ->published()
            ->search($query)
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function getByCategory($category, $perPage = 15)
    {
        return $this->knowledgeBase
            ->with('author')
            ->published()
            ->byCategory($category)
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function getFeatured($limit = 5)
    {
        return $this->knowledgeBase
            ->with('author')
            ->published()
            ->featured()
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function getCategories()
    {
        return $this->knowledgeBase
            ->where('status', 'published')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();
    }

    public function incrementViews($id): void
    {
        $knowledgeBase = $this->knowledgeBase->find($id);
        if ($knowledgeBase) {
            $knowledgeBase->incrementViews();
        }
    }
}














