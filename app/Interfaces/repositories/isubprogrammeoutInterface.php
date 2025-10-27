<?php

namespace App\Interfaces\repositories;

interface isubprogrammeoutInterface
{
     public function getsubprogrammeoutputs($strategy_id,$year);
     public function getsubprogrammeoutputbydepartment($strategy_id,$year,$department_id);
     public function createsubprogrammeoutput(array $data);
     public function updatesubprogrammeoutput($id,array $data);
     public function deletesubprogrammeoutput($id);
     public function getsubprogrammeoutput($id);
}
