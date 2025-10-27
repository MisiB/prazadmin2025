<?php

namespace App\Interfaces\repositories;

interface ileavetypeInterface
{
    public function getleavetypes();
    public function getLeavetypeByName($name);
    public function getleavetype($id);
    public function createleavetype($data);
    public function updateleavetype($id, $data);
    public function deleteleavetype($id);
}
