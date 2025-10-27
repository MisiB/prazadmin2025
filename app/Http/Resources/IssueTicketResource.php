<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticketnumber,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'issue_group' => [
                'id' => $this->issuegroup_id,
                'name' => $this->issuegroup?->name,
            ],
            'issue_type' => [
                'id' => $this->issuetype_id,
                'name' => $this->issuetype?->name,
            ],
            'department' => [
                'id' => $this->department_id,
                'name' => $this->department?->name,
            ],
            'reporter' => [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'registration_number' => $this->regnumber,
            ],
            'assignment' => [
                'assigned_to' => $this->assignedto ? [
                    'id' => $this->assignedto->id,
                    'name' => $this->assignedto->name.' '.$this->assignedto->surname,
                    'email' => $this->assignedto->email,
                ] : null,
                'assigned_by' => $this->assignedby ? [
                    'id' => $this->assignedby->id,
                    'name' => $this->assignedby->name.' '.$this->assignedby->surname,
                ] : null,
                'assigned_at' => $this->assigned_at?->toIso8601String(),
            ],
            'attachments' => $this->formatAttachments(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'created_by' => $this->createdby ? [
                'id' => $this->createdby->id,
                'name' => $this->createdby->name.' '.$this->createdby->surname,
            ] : null,
        ];
    }

    /**
     * Format attachments with full URLs
     */
    protected function formatAttachments(): array
    {
        if (empty($this->attachments)) {
            return [];
        }

        return collect($this->attachments)->map(function ($attachment) {
            return [
                'name' => $attachment['original_name'] ?? $attachment['name'] ?? 'unknown',
                'url' => isset($attachment['path']) ? asset('storage/'.$attachment['path']) : null,
                'mime_type' => $attachment['mime_type'] ?? 'application/octet-stream',
                'size' => $attachment['size'] ?? null,
                'uploaded_at' => $attachment['uploaded_at'] ?? null,
            ];
        })->toArray();
    }
}
