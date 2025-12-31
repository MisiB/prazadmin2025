<?php

namespace App\Interfaces\repositories;

interface itsallowanceconfigInterface
{
    public function getconfigs($search = null);

    public function getconfig($id);

    public function getactiveconfigs();

    public function getconfigsbycategory($category);

    public function getconfigsbygradeband($gradeBandId);

    public function getactiveconfigbycategoryandgrade($category, $gradeBandId);

    public function createconfig($data);

    public function updateconfig($id, $data);

    public function activateconfig($id, $data);

    public function deactivateconfig($id);

    public function submitforapproval($id, $data);

    public function approveconfig($id, $data);

    public function rejectconfig($id, $data);

    public function getconfigaudits($configId);

    public function getallgradebands();

    public function getgradeband($id);
}
