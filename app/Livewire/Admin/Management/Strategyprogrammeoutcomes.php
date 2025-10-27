<?php

namespace App\Livewire\Admin\Management;

use App\Interfaces\repositories\istrategyInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class Strategyprogrammeoutcomes extends Component
{
    use Toast;
    public $uuid;
    public $id;
    public $title;
    
    public $programme_id;
    public $programme =null;
    public  $breadcrumbs = [];
    protected $repo;
    public $modal = false;

    public function boot(istrategyInterface $repo)
    {
        $this->repo = $repo;
    }
    public function mount($uuid,$programme_id)
    {
        $this->uuid = $uuid;
        $this->programme_id = $programme_id;
        $this->getprogramme();
        $this->breadcrumbs = [
            [
                'label' => 'Home',
                'link' => route('admin.home'),
            ],
            [
                'label' => $this->programme?->strategy?->name,
                'link' => route('admin.management.strategies'),
            ],
            [
                'label' => $this->programme?->title,
                'link' => route('admin.management.strategydetail',[$this->uuid]),
            ],
            [
                'label' => 'Programme Outcomes',
            ],
        ];
    }

    public function getprogramme(){ 

        $prgramme = $this->repo->getprogrammebyuuid($this->uuid,$this->programme_id);
        if($prgramme){
            $this->programme = $prgramme;
        }   
        else{
         return redirect()->route('admin.home');
        }
    }
    public function getprogrammeoutcome($id){
        $this->id = $id;
        $programmeoutcome = $this->repo->getprogrammeoutcome($id);
        if($programmeoutcome){
            $this->title = $programmeoutcome->title;
        }
        $this->openModal();
    }
    public function openModal(){
        $this->modal = true;
    }
    public function closeModal(){
        $this->modal = false;
    }
    public function save(){
        $data = [
            'title'=>$this->title,
            "createdby"=>Auth::user()->id,
            'programme_id'=>$this->programme_id,
        ];
        if($this->id){
            $data['updatedby'] = Auth::user()->id;
           $response = $this->repo->updatestrategyprogrammeoutcome($this->id,$data);
           if($response['status']=='success'){
            $this->closeModal();
            $this->success($response['message']);
           }
           else{
            $this->error($response['message']);
           }
        }
        else{
            $response = $this->repo->createstrategyprogrammeoutcome($data);
            if($response['status']=='success'){
                $this->closeModal();
                $this->success($response['message']);
            }
            else{
                $this->error($response['message']);
            }
        }
        $this->reset([
            'title',
            'id'
            ]);
    }
    public function deleteprogrammeoutcome($id){
        $response = $this->repo->deletestrategyprogrammeoutcome($id);
        if($response['status']=='success'){
            $this->success($response['message']);
        }
        else{
            $this->error($response['message']);
        }
    }
    public function render()
    {
        return view('livewire.admin.management.strategyprogrammeoutcomes');
    }
}
