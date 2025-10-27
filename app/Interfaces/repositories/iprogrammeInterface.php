<?php

namespace App\Interfaces\repositories;

interface iprogrammeInterface
{
    public function getprogrammes($strategy_id);
    public function getprogramme($id);
    public function createprogramme(array $data);
    public function updateprogramme($id,array $data);
    public function deleteprogramme($id);
    public function approveprogramme($id);
    public function unapproveprogramme($id);
}
