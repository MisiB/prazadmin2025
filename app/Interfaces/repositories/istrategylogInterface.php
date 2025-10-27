<?php

namespace App\Interfaces\repositories;

interface istrategylogInterface
{
    public function getstrategylogs($source_id,$source);
    public function createstrategylog(array $data);
}
