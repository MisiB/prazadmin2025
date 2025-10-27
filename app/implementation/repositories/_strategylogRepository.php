<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\istrategylogInterface;
use App\Models\Strategylogs;

class _strategylogRepository implements istrategylogInterface
{
    /**
     * Create a new class instance.
     */
    protected $strategylogmodel;
    public function __construct(Strategylogs $strategylogmodel)
    {
        $this->strategylogmodel = $strategylogmodel;
    }
    public function getstrategylogs($source_id,$source)
    {
        return $this->strategylogmodel->where('source_id',$source_id)->where('source',$source)->get();
    }
    public function createstrategylog(array $data)
    {
        try {
            $this->strategylogmodel->create($data);
            return ["status"=>"success","message"=>"Strategy log created successfully"];
        } catch (\Exception $e) {
            return ["status"=>"error","message"=>$e->getMessage()];
        }
    }
}
