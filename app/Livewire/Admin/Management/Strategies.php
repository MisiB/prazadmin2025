<?php

namespace App\Livewire\Admin\Management;

use App\Interfaces\repositories\istrategyInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Mary\Traits\Toast;

class Strategies extends Component
{
    use Toast;
    public $id;
    public $name;
    public $startyear;
    public $endyear;
    public $modal;
    protected $repository;
   public array $breadcrumbs =[];
     public function boot(istrategyInterface $repository)
     {
         $this->repository = $repository;
     }

     public function mount(){
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Strategies']
        ];
     }

     public function getstrategies()
     {
        return $this->repository->getstrategies();
     }
     public function getstrategy($id)
     {
        $payload=$this->repository->getstrategy($id);
        $this->id=$payload->id;
        $this->name=$payload->name;
        $this->startyear=$payload->startyear;
        $this->endyear=$payload->endyear;
        $this->openModal();
     }
     public function save(){
        $this->validate([
            'name'=>'required',
            'startyear'=>'required',
            'endyear'=>'required',
        ]);
        if($this->id){
            $response=$this->repository->updatestrategy($this->id,[
                'name'=>$this->name,
                'startyear'=>$this->startyear,
                'endyear'=>$this->endyear,
                'updatedby'=>Auth::user()->id,
            ]);
            if($response['status']=='success'){
                $this->closeModal();
                $this->success('Strategy updated successfully');
            }else{
                $this->error($response['message']);
            }
        }else{
            $uuid = Str::uuid()->toString();
            $response=$this->repository->createstrategy([
                'name'=>$this->name,
                'startyear'=>$this->startyear,
                'endyear'=>$this->endyear,
                'uuid'=>$uuid,
                'createdby'=>Auth::user()->id,
                'status'=>'Draft',
            ]);
            if($response['status']=='success'){
                $this->closeModal();
                $this->success('Strategy updated successfully');
            }else{
                $this->error($response['message']);
            }
        }
        $this->reset(['name','startyear','endyear','id']);
     }

     public function closeModal()
     {
        $this->modal=false;
     }
     public function openModal()
     {
        $this->modal=true;
     }  

     public function delete($id)
     {
        $response=$this->repository->deletestrategy($id);
        if($response['status']=='success'){
            $this->success('Strategy deleted successfully');
        }else{
            $this->error($response['message']);
        }
     }
     public function headers():array{
        return [
            ['key'=>'name','label'=>'Name'],
            ['key'=>'startyear','label'=>'Start Year'],
            ['key'=>'endyear','label'=>'End Year'],
            ['key'=>'created_by','label'=>'User'],
            ['key'=>'status','label'=>'Status'],
            ['key'=>'action','label'=>'']
        ];
     }
    public function render()
    {
        return view('livewire.admin.management.strategies',[
            'strategies'=>$this->getstrategies(),
            'headers'=>$this->headers()
        ]);
    }
}
