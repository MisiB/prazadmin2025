<?php

namespace App\Interfaces\repositories;

interface iIndicatorInterface
{
    public function getindicators($departmentoutput_id);
    public function getindicator($id);
    public function createindicator(array $data);
    public function updateindicator($id,array $data);
    public function deleteindicator($id);
    public function approveindicator($id);
    public function unapproveindicator($id);
    public function addtarget(array $data);
    public function updatetarget($id,array $data);
    public function deletetarget($id);
    public function gettarget($id);
    public function gettargetbyindicator($indicator_id);
}
