<?php

namespace App\Interfaces\repositories;

interface iissuetypeInterface
{
    public function getIssueTypes();

    public function getIssueType($id);

    public function create($data);

    public function update($id, $data);

    public function delete($id);
}















