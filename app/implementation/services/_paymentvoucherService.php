<?php

namespace App\implementation\services;

use App\Interfaces\repositories\ipaymentvoucherInterface;
use App\Interfaces\services\ipaymentvoucherService;
use Illuminate\Support\Facades\Auth;

class _paymentvoucherService implements ipaymentvoucherService
{
    protected $repository;

    public function __construct(ipaymentvoucherInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getvouchers($year, $search = null)
    {
        return $this->repository->getvouchers($year, $search);
    }

    public function getvoucher($id)
    {
        return $this->repository->getvoucher($id);
    }

    public function getvoucherbyuuid($uuid)
    {
        return $this->repository->getvoucherbyuuid($uuid);
    }

    public function getvoucherbystatus($year, $status)
    {
        return $this->repository->getvoucherbystatus($year, $status);
    }

    public function createvoucher($data)
    {
        // Validate permission
        if (! Auth::user()->can('payment.voucher.create')) {
            return ['status' => 'error', 'message' => 'You do not have permission to create payment vouchers'];
        }

        // Validate required fields
        if (empty($data['currency'])) {
            return ['status' => 'error', 'message' => 'Currency is required'];
        }

        if (empty($data['items']) || ! is_array($data['items']) || count($data['items']) === 0) {
            return ['status' => 'error', 'message' => 'At least one item is required'];
        }

        // Validate ZiG exchange rate
        if ($data['currency'] === 'ZiG' && empty($data['exchange_rate'])) {
            return ['status' => 'error', 'message' => 'Exchange rate is required for ZiG currency'];
        }

        return $this->repository->createvoucher($data);
    }

    public function updatevoucher($id, $data)
    {
        // Validate permission
        if (! Auth::user()->can('payment.voucher.edit')) {
            return ['status' => 'error', 'message' => 'You do not have permission to edit payment vouchers'];
        }

        return $this->repository->updatevoucher($id, $data);
    }

    public function deletevoucher($id)
    {
        // Validate permission
        if (! Auth::user()->can('payment.voucher.delete')) {
            return ['status' => 'error', 'message' => 'You do not have permission to delete payment vouchers'];
        }

        return $this->repository->deletevoucher($id);
    }

    public function submitvoucher($id)
    {
        // Validate permission
        if (! Auth::user()->can('payment.voucher.submit')) {
            return ['status' => 'error', 'message' => 'You do not have permission to submit payment vouchers'];
        }

        return $this->repository->submitvoucher($id);
    }

    public function geteligibleitems($year)
    {
        return $this->repository->geteligibleitems($year);
    }

    public function verify($id, $data)
    {
        // Validate permission
        if (! Auth::user()->can('payment.voucher.verify')) {
            return ['status' => 'error', 'message' => 'You do not have permission to verify payment vouchers'];
        }

        return $this->repository->verify($id, $data);
    }

    public function check($id, $data)
    {
        // Validate permission
        if (! Auth::user()->can('payment.voucher.check')) {
            return ['status' => 'error', 'message' => 'You do not have permission to check payment vouchers'];
        }

        return $this->repository->check($id, $data);
    }

    public function approverfinance($id, $data)
    {
        // Validate permission
        if (! Auth::user()->can('payment.voucher.approve.finance')) {
            return ['status' => 'error', 'message' => 'You do not have permission to approve payment vouchers for finance'];
        }

        return $this->repository->approverfinance($id, $data);
    }

    public function approveceo($id, $data)
    {
        // Validate permission
        if (! Auth::user()->can('payment.voucher.approve.ceo')) {
            return ['status' => 'error', 'message' => 'You do not have permission to approve payment vouchers as CEO'];
        }

        return $this->repository->approveceo($id, $data);
    }

    public function reject($id, $data)
    {
        // Validate permission
        if (! Auth::user()->can('payment.voucher.reject')) {
            return ['status' => 'error', 'message' => 'You do not have permission to reject payment vouchers'];
        }

        // Validate comment is provided
        if (empty($data['comment'])) {
            return ['status' => 'error', 'message' => 'Rejection reason is required'];
        }

        return $this->repository->reject($id, $data);
    }

    public function approve($id, $data)
    {
        // Get voucher to check current status and workflow
        $voucher = $this->repository->getvoucher($id);
        if (! $voucher || ! $voucher->workflow) {
            return ['status' => 'error', 'message' => 'Voucher or workflow not found'];
        }

        // Get current workflow parameter
        $workflowParameter = $voucher->workflow->workflowparameters
            ->where('status', $voucher->status)
            ->first();

        if (! $workflowParameter) {
            return ['status' => 'error', 'message' => 'Invalid workflow step'];
        }

        // Check permission based on workflow parameter
        if (! Auth::user()->can($workflowParameter->permission->name)) {
            return ['status' => 'error', 'message' => 'You do not have permission to approve at this stage'];
        }

        return $this->repository->approve($id, $data);
    }

    public function getallvouchers($year)
    {
        return $this->repository->getallvouchers($year);
    }

    public function getconfig($key)
    {
        return $this->repository->getconfig($key);
    }

    public function setconfig($key, $value, $description = null)
    {
        // Validate permission
        if (! Auth::user()->can('payment.voucher.config.manage')) {
            return ['status' => 'error', 'message' => 'You do not have permission to manage payment voucher configuration'];
        }

        return $this->repository->setconfig($key, $value, $description);
    }

    public function getallconfigs()
    {
        return $this->repository->getallconfigs();
    }
}
