<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\istoresrequisitionInterface;
use App\Models\Departmentuser;
use App\Models\Storesrequisition;
use App\Models\User;

class _storesrequisitionRepository implements istoresrequisitionInterface
{
    protected $model, $deptmodel, $usermodel;
    public function __construct(Storesrequisition $model, Departmentuser $deptmodel)
    {
       $this->model=$model;
       $this->deptmodel=$deptmodel;
    }

    public function getstoresrequisitions()
    {
        return $this->model->all();
    }
    public function getmystoresrequisitions($userid,$status=null, $searchuuid=null)
    {
        if($status)
        {
            $requisitions=$searchuuid!=null?$this->model->where('initiator_id', $userid)->where('status',$status)->where('storesrequisition_uuid', $searchuuid)->get():$this->model->where('initiator_id', $userid)->where('status',$status)->get();
            return $requisitions;
        }
        $requisitions=$searchuuid!=null?$this->model->where('initiator_id', $userid)->where('storesrequisition_uuid', $searchuuid)->get():$this->model->where('initiator_id', $userid)->get();
        return $requisitions;
    }
    public function getdeptstoresrequisitions($status,$departmentid=null,$searchuuid=null,$statussearch=null)
    {
        $status = $statussearch!=null? $statussearch:$status;
        if(!$departmentid || $departmentid==0)
        {
           
            if(!is_array($status))
            {
                return $searchuuid!=null?$this->model->where('status',$status)->where('storesrequisition_uuid', $searchuuid)->get():$this->model->where('status',$status)->get();
            }
            return $searchuuid!=null?$this->model->whereIn('status',collect($status))->where('storesrequisition_uuid', $searchuuid)->get():$this->model->whereIn('status',collect($status))->get();
        }
        $deptmemberids=$this->deptmodel->where('department_id', $departmentid)->pluck('user_id')->toArray();
        
        $deptexists=$this->deptmodel->where('department_id',$departmentid)->first();
        if (!$deptexists) {
            return ['status' => 'error', 'message' => 'User has no access to any department. Visit ICT to be assigned to a department.'];
        }else
        {
            return $searchuuid!=null?$this->model->whereIn('initiator_id', $deptmemberids)->whereIn('status',collect($status))->where('storesrequisition_uuid', $searchuuid)->get():$this->model->whereIn('initiator_id', $deptmemberids)->whereIn('status',collect($status))->get();
        }
    }
    public function gethodstoresrequestssubmissions()
    {
        return $this->model->where('status','!=','P')->get();
    }
    public function getstoresrequisitionsByStatus($status)
    {
        return $this->model->where('status', $status)->get();
    }
    public function getstoresrequisitionsByDepartment($departmentid)
    {
        return $this->model->whereHas('initiator', function($query) use ($departmentid)
        {
            return $this->deptmodel->where('user_id', $query->id)->where('department_id', $departmentid);
        });
    }
    public function getstoresrequisition($storesrequisitionuuid)
    {
        return $this->model->where('storesrequisition_uuid', $storesrequisitionuuid)->first();
    }
    public function createstoresrequisition($data)
    {
        try
        {
            $check = $this->getstoresrequisition($data['storesrequisition_uuid']);
            if ($check) {
                return ['status' => "error", 'message' => 'Store requisition already exists'];
            }
            $this->model->create($data);
            return ['status' => "success", 'message' => 'Store requisition created successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function updatestoresrequisition($storesrequisition_uuid, $data)
    {
        try
        {
            $check = $this->getstoresrequisition($storesrequisition_uuid);
            if (!$check) {
                return ['status' => "error", 'message' => 'Store requisition not found'];
            }
            $check->update($data);
            return ['status' => "success", 'message' => 'Store requisition updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }
    public function deletestoresrequisition($id)
    {
        try
        {
            $check = $this->model->find($id);
            if (!$check) {
                return ['status' => "error", 'message' => 'Store requisition not found'];
            }
            $check->delete();
            return ['status' => "success", 'message' => 'Store requisition deleted successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
    }

    public function exportstoresrequisitionreport($status)
    {
        try {
            $requisitionsdata=[];
            $requisitionsdata[]=['Date','Stock item','Purpose','Quantity Required','Quantity Issued','Department  Requested'];

            $this->getstoresrequisitionsByStatus($status)->each(function ($item) use (&$requisitionsdata) {
                $departmentname = ($this->deptmodel->with('department')->where('user_id', $item->initiator_id)->first()->department->name) ?? 'N/A';
                $requisitionitems=json_decode($item->requisitionitems,true);
                foreach ($requisitionitems as $requisitionitem) {
                    $requisitionsdata[] = [
                        $item->updated_at->format('Y-m-d'),
                        $requisitionitem["itemdetail"] ?? '',      // child stock item
                        $item->purposeofrequisition,
                        $requisitionitem['requiredquantity'] ?? 0,
                        $requisitionitem['issuedquantity'] ?? 0, // issued per item
                        $departmentname
                    ];
                }
                
            });
            $response = [
                'status' => 'success',
                'data' => $requisitionsdata
            ];
            return $response;
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }

    }

}
