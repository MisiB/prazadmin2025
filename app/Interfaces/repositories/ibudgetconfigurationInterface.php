<?php

namespace App\Interfaces\repositories;

interface ibudgetconfigurationInterface
{
    public function getexpensecategories();
    public function getexpensecategory($id);
    public function createexpensecategory($data);
    public function updateexpensecategory($id,$data);
    public function deleteexpensecategory($id);



    public function getsourceoffundtypes();
    public function getsourceoffundtype($id);
    public function createsourceoffundtype($data);
    public function updatesourceoffundtype($id,$data);
    public function deletesourceoffundtype($id);


    public function getsourceoffunds();
    public function getsourceoffund($id);
    public function createsourceoffund($data);
    public function updatesourceoffund($id,$data);
    public function deletesourceoffund($id);
}
