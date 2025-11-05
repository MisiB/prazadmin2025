<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\iissuetypeInterface;
use App\Models\Issuetype;

class _issuetypeRepository implements iissuetypeInterface
{
    protected $issueType;

    public function __construct(Issuetype $issueType)
    {
        $this->issueType = $issueType;
    }

    public function getIssueTypes()
    {
        return $this->issueType->with('department')->orderBy('name')->get();
    }

    public function getIssueType($id)
    {
        return $this->issueType->with('department')->find($id);
    }

    public function create($data)
    {
        try {
            $exist = $this->issueType->where('name', $data['name'])->first();
            if ($exist) {
                return ['status' => 'error', 'message' => 'Issue type already exists'];
            }
            $this->issueType->create($data);

            return ['status' => 'success', 'message' => 'Issue type created successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function update($id, $data)
    {
        try {
            $exist = $this->issueType->where('name', $data['name'])->where('id', '!=', $id)->first();
            if ($exist) {
                return ['status' => 'error', 'message' => 'Issue type already exists'];
            }
            $this->issueType->find($id)->update($data);

            return ['status' => 'success', 'message' => 'Issue type updated successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function delete($id)
    {
        try {
            $this->issueType->find($id)->delete();

            return ['status' => 'success', 'message' => 'Issue type deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}















