<?php

namespace App\Interfaces\repositories;

interface imoduleInterface
{
       public function getmodules();
      public function getmodule(int $id);
      public function createmodule(array $module);
      public function updatemodule(int $id, array $module);
      public function deletemodule(int $id);
}
