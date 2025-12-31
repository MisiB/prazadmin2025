<?php

namespace App\implementation\services;

use App\Interfaces\repositories\itsallowanceconfigInterface;
use App\Interfaces\services\itsallowanceconfigService;
use Illuminate\Support\Facades\Auth;

class _tsallowanceconfigService implements itsallowanceconfigService
{
    protected $tsallowanceconfigrepo;

    public function __construct(itsallowanceconfigInterface $tsallowanceconfigrepo)
    {
        $this->tsallowanceconfigrepo = $tsallowanceconfigrepo;
    }

    public function getconfigs($search = null)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.config.view')) {
            return collect()->paginate(15);
        }

        return $this->tsallowanceconfigrepo->getconfigs($search);
    }

    public function getconfig($id)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.config.view')) {
            return null;
        }

        return $this->tsallowanceconfigrepo->getconfig($id);
    }

    public function getactiveconfigs()
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.calculate')) {
            return collect();
        }

        return $this->tsallowanceconfigrepo->getactiveconfigs();
    }

    public function getconfigsbycategory($category)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.config.view')) {
            return collect();
        }

        return $this->tsallowanceconfigrepo->getconfigsbycategory($category);
    }

    public function getconfigsbygradeband($gradeBandId)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.config.view')) {
            return collect();
        }

        return $this->tsallowanceconfigrepo->getconfigsbygradeband($gradeBandId);
    }

    public function getactiveconfigbycategoryandgrade($category, $gradeBandId)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.calculate')) {
            return null;
        }

        return $this->tsallowanceconfigrepo->getactiveconfigbycategoryandgrade($category, $gradeBandId);
    }

    public function createconfig($data)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.config.create')) {
            return ['status' => 'error', 'message' => 'You do not have permission to create allowance configurations'];
        }

        return $this->tsallowanceconfigrepo->createconfig($data);
    }

    public function updateconfig($id, $data)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.config.update')) {
            return ['status' => 'error', 'message' => 'You do not have permission to update allowance configurations'];
        }

        return $this->tsallowanceconfigrepo->updateconfig($id, $data);
    }

    public function activateconfig($id, $data)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.config.activate')) {
            return ['status' => 'error', 'message' => 'You do not have permission to activate allowance configurations'];
        }

        return $this->tsallowanceconfigrepo->activateconfig($id, $data);
    }

    public function deactivateconfig($id)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.config.deactivate')) {
            return ['status' => 'error', 'message' => 'You do not have permission to deactivate allowance configurations'];
        }

        return $this->tsallowanceconfigrepo->deactivateconfig($id);
    }

    public function submitforapproval($id, $data)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.config.submit')) {
            return ['status' => 'error', 'message' => 'You do not have permission to submit configurations for approval'];
        }

        return $this->tsallowanceconfigrepo->submitforapproval($id, $data);
    }

    public function approveconfig($id, $data)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.config.approve')) {
            return ['status' => 'error', 'message' => 'You do not have permission to approve configurations'];
        }

        return $this->tsallowanceconfigrepo->approveconfig($id, $data);
    }

    public function rejectconfig($id, $data)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.config.reject')) {
            return ['status' => 'error', 'message' => 'You do not have permission to reject configurations'];
        }

        // Comment is required for rejection
        if (empty($data['comment'])) {
            return ['status' => 'error', 'message' => 'Comment is required when rejecting'];
        }

        return $this->tsallowanceconfigrepo->rejectconfig($id, $data);
    }

    public function getconfigaudits($configId)
    {
        // Check permission
        if (! Auth::user()->can('tsa.allowance.config.audit')) {
            return collect();
        }

        return $this->tsallowanceconfigrepo->getconfigaudits($configId);
    }

    public function getallgradebands()
    {
        return $this->tsallowanceconfigrepo->getallgradebands();
    }

    public function getgradeband($id)
    {
        return $this->tsallowanceconfigrepo->getgradeband($id);
    }
}
