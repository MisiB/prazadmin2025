<?php

namespace App\Interfaces\repositories;

interface itaskInterface
{
    public function getmytasks($year);
    public function gettask($id);
    public function createtask($data);
    public function updatetask($id,$data);
    public function deletetask($id);
    public function marktask($id,$status);
    public function approvetask(array $data);
}
