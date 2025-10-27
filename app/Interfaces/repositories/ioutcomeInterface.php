<?php

namespace App\Interfaces\repositories;

interface ioutcomeInterface
{
    public function getoutcomes($programme_id);
    public function getoutcome($id);
    public function createoutcome(array $data);
    public function updateoutcome($id,array $data);
    public function deleteoutcome($id);
    public function approveoutcome($id);
    public function unapproveoutcome($id);
}
