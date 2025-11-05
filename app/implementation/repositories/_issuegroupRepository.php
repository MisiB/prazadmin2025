<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iissuegroupInterface;
use App\Models\Issuegroup;

class _issuegroupRepository implements iissuegroupInterface
{
    protected $issueGroup;

    public function __construct(Issuegroup $issueGroup)
    {
        $this->issueGroup = $issueGroup;
    }

    public function getIssueGroups()
    {
        return $this->issueGroup->orderBy('name')->get();
    }

    public function getIssueGroup($id)
    {
        return $this->issueGroup->find($id);
    }

    public function create($data)
    {
        try {
            $exist = $this->issueGroup->where('name', $data['name'])->first();
            if ($exist) {
                return ['status' => 'error', 'message' => 'Issue group already exists'];
            }
            $this->issueGroup->create($data);

            return ['status' => 'success', 'message' => 'Issue group created successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function update($id, $data)
    {
        try {
            $exist = $this->issueGroup->where('name', $data['name'])->where('id', '!=', $id)->first();
            if ($exist) {
                return ['status' => 'error', 'message' => 'Issue group already exists'];
            }
            $this->issueGroup->find($id)->update($data);

            return ['status' => 'success', 'message' => 'Issue group updated successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function delete($id)
    {
        try {
            $this->issueGroup->find($id)->delete();

            return ['status' => 'success', 'message' => 'Issue group deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}















