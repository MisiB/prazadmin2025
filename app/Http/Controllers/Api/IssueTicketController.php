<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddIssueCommentRequest;
use App\Http\Requests\Api\CreateIssueTicketRequest;
use App\Http\Requests\Api\UpdateIssueCommentRequest;
use App\Http\Resources\IssueTicketCollection;
use App\Http\Resources\IssueTicketResource;
use App\Interfaces\repositories\iissuegroupInterface;
use App\Interfaces\repositories\iissuelogInterface;
use App\Interfaces\repositories\iissuetypeInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssueTicketController extends Controller
{
    protected $issueRepository;

    protected $issueGroupRepository;

    protected $issueTypeRepository;

    public function __construct(
        iissuelogInterface $issueRepository,
        iissuegroupInterface $issueGroupRepository,
        iissuetypeInterface $issueTypeRepository
    ) {
        $this->issueRepository = $issueRepository;
        $this->issueGroupRepository = $issueGroupRepository;
        $this->issueTypeRepository = $issueTypeRepository;
    }

    /**
     * Get all tickets by email
     */
    public function index(string $email): IssueTicketCollection
    {
        $issues = $this->issueRepository->getissuelogsbyemail($email);

        return new IssueTicketCollection($issues);
    }

    /**
     * Create a new ticket
     */
    public function store(CreateIssueTicketRequest $request): JsonResponse
    {

        $response = $this->issueRepository->createissuelog($request->validated());

        if ($response['status'] === 'success') {
            return response()->json([
                'success' => true,
                'message' => $response['message'],
                'data' => $response['data'] ?? null,
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'],
        ], 422);
    }

    /**
     * Get a specific ticket by ID
     */
    public function show(int $id)
    {
        return $this->issueRepository->getissuelog($id);

    }

    /**
     * Get a specific ticket by ticket number
     */
    public function showByTicketNumber(string $ticketNumber): IssueTicketResource|JsonResponse
    {
        try {
            $issue = $this->issueRepository->getissuelogs()
                ->where('ticketnumber', $ticketNumber)
                ->firstOrFail();

            return new IssueTicketResource($issue);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
            ], 404);
        }
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $response = $this->issueRepository->updateissuestatus($id, $request->status);

        if ($response['status'] === 'success') {
            return response()->json([
                'success' => true,
                'message' => $response['message'],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'],
        ], 422);
    }

    /**
     * Track tickets by email
     */
    public function trackByEmail(Request $request): IssueTicketCollection
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $issues = $this->issueRepository->getissuelogs()
            ->where('email', $request->email);

        return new IssueTicketCollection($issues);
    }

    /**
     * Get available issue groups
     */
    public function getIssueGroups(): JsonResponse
    {
        $groups = $this->issueGroupRepository->getIssueGroups();

        return response()->json([
            'success' => true,
            'data' => $groups->map(fn ($group) => [
                'id' => $group->id,
                'name' => $group->name,
            ]),
        ]);
    }

    /**
     * Get available issue types
     */
    public function getIssueTypes(): JsonResponse
    {
        $types = $this->issueTypeRepository->getIssueTypes();

        return response()->json([
            'success' => true,
            'data' => $types->map(fn ($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'department_id' => $type->department_id,
            ]),
        ]);
    }

    /**
     * Get ticket statistics
     */
    public function statistics(): JsonResponse
    {
        $issues = $this->issueRepository->getissuelogs();

        $stats = [
            'total' => $issues->count(),
            'by_status' => [
                'open' => $issues->where('status', 'open')->count(),
                'in_progress' => $issues->where('status', 'in_progress')->count(),
                'resolved' => $issues->where('status', 'resolved')->count(),
                'closed' => $issues->where('status', 'closed')->count(),
            ],
            'by_priority' => [
                'low' => $issues->where('priority', 'Low')->count(),
                'medium' => $issues->where('priority', 'Medium')->count(),
                'high' => $issues->where('priority', 'High')->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get all comments for a specific issue
     */
    public function getComments(int $issueId): JsonResponse
    {
        try {
            $comments = $this->issueRepository->getissuecomments($issueId);

            return response()->json([
                'success' => true,
                'data' => $comments->map(fn ($comment) => [
                    'id' => $comment->id,
                    'issue_id' => $comment->issuelog_id,
                    'user_email' => $comment->user_email,
                    'comment' => $comment->comment,
                    'is_internal' => $comment->is_internal,
                    'created_at' => $comment->created_at->toIso8601String(),
                    'updated_at' => $comment->updated_at->toIso8601String(),
                    'created_at_human' => $comment->created_at->diffForHumans(),
                ]),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve comments: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add a new comment to an issue
     */
    public function addComment(AddIssueCommentRequest $request): JsonResponse
    {
        $response = $this->issueRepository->addcomment(
            $request->issue_id,
            $request->user_email,
            $request->comment,
            $request->is_internal ?? false
        );

        if ($response['status'] === 'success') {
            return response()->json([
                'success' => true,
                'message' => $response['message'],
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'],
        ], 422);
    }

    /**
     * Update an existing comment
     */
    public function updateComment(UpdateIssueCommentRequest $request, int $commentId): JsonResponse
    {
        $response = $this->issueRepository->updatecomment(
            $commentId,
            $request->user_email,
            $request->comment,
            $request->is_internal ?? false
        );

        if ($response['status'] === 'success') {
            return response()->json([
                'success' => true,
                'message' => $response['message'],
                'data' => [
                    'id' => $response['data']->id,
                    'comment' => $response['data']->comment,
                    'is_internal' => $response['data']->is_internal,
                    'updated_at' => $response['data']->updated_at->toIso8601String(),
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'],
        ], 422);
    }

    /**
     * Delete a comment
     */
    public function deleteComment(int $commentId): JsonResponse
    {
        $response = $this->issueRepository->deletecomment($commentId);

        if ($response['status'] === 'success') {
            return response()->json([
                'success' => true,
                'message' => $response['message'],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'],
        ], 422);
    }
}
