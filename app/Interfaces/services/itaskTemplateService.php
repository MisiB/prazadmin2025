<?php

namespace App\Interfaces\services;

interface itaskTemplateService
{
    public function getmytemplates($userId);

    public function gettemplate($id);

    public function createtemplate(array $data);

    public function updatetemplate($id, array $data);

    public function deletetemplate($id);

    public function createtaskfromtemplate($templateId, $calendardayId);
}
