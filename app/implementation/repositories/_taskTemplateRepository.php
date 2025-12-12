<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\itaskTemplateInterface;
use App\Models\TaskTemplate;

class _taskTemplateRepository implements itaskTemplateInterface
{
    protected $taskTemplate;

    public function __construct(TaskTemplate $taskTemplate)
    {
        $this->taskTemplate = $taskTemplate;
    }

    public function getmytemplates($userId)
    {
        return $this->taskTemplate->where('user_id', $userId)->orderBy('created_at', 'desc')->get();
    }

    public function gettemplate($id)
    {
        return $this->taskTemplate->find($id);
    }

    public function createtemplate(array $data)
    {
        try {
            $template = $this->taskTemplate->create($data);

            return ['status' => 'success', 'message' => 'Template created successfully', 'data' => $template];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updatetemplate($id, array $data)
    {
        try {
            $template = $this->taskTemplate->find($id);

            if (! $template) {
                return ['status' => 'error', 'message' => 'Template not found'];
            }

            $template->update($data);

            return ['status' => 'success', 'message' => 'Template updated successfully', 'data' => $template];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deletetemplate($id)
    {
        try {
            $template = $this->taskTemplate->find($id);

            if (! $template) {
                return ['status' => 'error', 'message' => 'Template not found'];
            }

            // Check if template is used in any recurring tasks
            if ($template->recurringTasks()->count() > 0) {
                return ['status' => 'error', 'message' => 'Cannot delete template that is used in recurring tasks'];
            }

            $template->delete();

            return ['status' => 'success', 'message' => 'Template deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
