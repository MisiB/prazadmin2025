<?php

namespace App\Interfaces\repositories;

interface iissuegroupInterface
{
    public function getIssueGroups();

    public function getIssueGroup($id);

    public function create($data);

    public function update($id, $data);

    public function delete($id);
}















