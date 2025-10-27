<?php

namespace App\Interfaces\repositories;

interface itenderInterface
{
    public function createtendertype(array $data);

    public function gettendertypes();

    public function gettendertype($name);

    public function updatetendertype($id, array $data);

    public function deletetendertype($id);

    public function create(array $data);

    public function gettenders($search = null);

    public function gettendersbynumber($tendernumber);

    public function gettender($id);

    public function updatetender($id, array $data);

    public function deletetender($id);

    public function gettenderfee($id);

    public function createtenderfee(array $data);

    public function updatetenderfee($id, array $data);

    public function deletetenderfee($id);
}
