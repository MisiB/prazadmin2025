<?php

namespace App\Interfaces\repositories;

interface istoreitemInterface
{
    public function getstoreitems();
    public function getstoreitemsByUser($userid);
    public function getstoreitem($id);
    public function createstoreitem($data);
    public function updatestoreitem($id, $data);
    public function deletestoreitem($id);
}
