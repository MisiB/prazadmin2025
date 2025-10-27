<?php

namespace App\Interfaces\repositories;

interface isubmoduleInterface
{
    public function getsubmodule(int $id);
    public function createsubmodule(array $submodule);
    public function updatesubmodule(int $id, array $submodule);
    public function deletesubmodule(int $id);
}
