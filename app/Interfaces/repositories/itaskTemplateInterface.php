<?php

namespace App\Interfaces\repositories;

interface itaskTemplateInterface
{
    public function getmytemplates($userId);

    public function gettemplate($id);

    public function createtemplate(array $data);

    public function updatetemplate($id, array $data);

    public function deletetemplate($id);
}
