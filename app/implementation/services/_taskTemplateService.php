<?php

namespace App\implementation\services;

use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\repositories\itaskTemplateInterface;
use App\Interfaces\services\itaskTemplateService;
use Illuminate\Support\Facades\Auth;

class _taskTemplateService implements itaskTemplateService
{
    protected $taskTemplateRepository;

    protected $taskRepository;

    public function __construct(itaskTemplateInterface $taskTemplateRepository, itaskInterface $taskRepository)
    {
        $this->taskTemplateRepository = $taskTemplateRepository;
        $this->taskRepository = $taskRepository;
    }

    public function getmytemplates($userId)
    {
        return $this->taskTemplateRepository->getmytemplates($userId);
    }

    public function gettemplate($id)
    {
        return $this->taskTemplateRepository->gettemplate($id);
    }

    public function createtemplate(array $data)
    {
        $data['user_id'] = Auth::user()->id;

        return $this->taskTemplateRepository->createtemplate($data);
    }

    public function updatetemplate($id, array $data)
    {
        return $this->taskTemplateRepository->updatetemplate($id, $data);
    }

    public function deletetemplate($id)
    {
        return $this->taskTemplateRepository->deletetemplate($id);
    }

    public function createtaskfromtemplate($templateId, $calendardayId)
    {
        $template = $this->taskTemplateRepository->gettemplate($templateId);

        if (! $template) {
            return ['status' => 'error', 'message' => 'Template not found'];
        }

        // Get calendar day to get dates
        $calendarday = \App\Models\Calendarday::find($calendardayId);
        if (! $calendarday) {
            return ['status' => 'error', 'message' => 'Calendar day not found'];
        }

        // Create task from template
        $taskData = [
            'title' => $template->title,
            'description' => $template->description,
            'priority' => $template->priority,
            'duration' => $template->duration,
            'uom' => $template->uom,
            'user_id' => Auth::user()->id,
            'calendarday_id' => $calendardayId,
            'individualworkplan_id' => $template->individualworkplan_id,
        ];

        return $this->taskRepository->createtask($taskData);
    }
}
