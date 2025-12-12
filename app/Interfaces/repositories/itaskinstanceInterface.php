<?php

namespace App\Interfaces\repositories;

interface itaskinstanceInterface
{
    public function getall();

    public function getbyid($id);

    public function getbytaskid($taskId);

    public function getinstancesbydate($date);

    public function getinstancesbydateanduser($date, $userId);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);
}
