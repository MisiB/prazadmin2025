<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\repositories\iknowledgeBaseInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    protected $knowledgeBaseRepository;

    public function __construct(iknowledgeBaseInterface $knowledgeBaseRepository)
    {
        $this->knowledgeBaseRepository = $knowledgeBaseRepository;
    }

    /**
     * Get all published knowledge base articles
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $articles = $this->knowledgeBaseRepository->getAllPublished($perPage);

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }

    /**
     * Search knowledge base articles
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $request->get('q');
        $perPage = $request->get('per_page', 15);

        $articles = $this->knowledgeBaseRepository->search($query, $perPage);

        return response()->json([
            'success' => true,
            'query' => $query,
            'data' => $articles,
        ]);
    }

    /**
     * Get article by slug
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $article = $this->knowledgeBaseRepository->getBySlug($slug);

            // Increment view count
            $this->knowledgeBaseRepository->incrementViews($article->id);

            return response()->json([
                'success' => true,
                'data' => $article,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
            ], 404);
        }
    }

    /**
     * Get articles by category
     */
    public function byCategory(Request $request, string $category): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $articles = $this->knowledgeBaseRepository->getByCategory($category, $perPage);

        return response()->json([
            'success' => true,
            'category' => $category,
            'data' => $articles,
        ]);
    }

    /**
     * Get featured articles
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        $articles = $this->knowledgeBaseRepository->getFeatured($limit);

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }

    /**
     * Get all categories
     */
    public function categories(): JsonResponse
    {
        $categories = $this->knowledgeBaseRepository->getCategories();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
