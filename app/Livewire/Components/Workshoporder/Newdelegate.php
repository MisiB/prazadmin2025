<?php

namespace App\Livewire\Components\Workshoporder;

use Livewire\Component;
use Mary\Traits\Toast;
use App\Interfaces\repositories\iworkshopInterface;
class Newdelegate extends Component
{
    use Toast;
    public $workshoporder_id;
    public $name;
    public $surname;
    public $email;
    public $phone;
    public $designation;
    public $national_id;
    public $title;
    public $gender;
    public $showNewDelegateModal = false;
    protected $workshopRepository;
    public function boot(iworkshopInterface $workshopRepository){
        $this->workshopRepository = $workshopRepository;
    }
    public function mount($workshoporder_id){
        $this->workshoporder_id = $workshoporder_id;
    }

    public function titlelist(){
        return [['id'=>'Mr','name'=>'Mr'],['id'=>'Mrs','name'=>'Mrs'],['id'=>'Ms','name'=>'Ms'],['id'=>'Dr','name'=>'Dr'],['id'=>'Prof','name'=>'Prof'],['id'=>'Other','name'=>'Other']];
    }
    public function genderlist(){
        return [['id'=>'M','name'=>'Male'],['id'=>'F','name'=>'Female']];
    }

    public function savedelegate(){
        $this->validate([
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'designation' => 'required',
            'national_id' => 'required',
            'title' => 'required',
            'gender' => 'required',
        ]);
        $result = $this->workshopRepository->adddelegate([
            'workshoporder_id' => $this->workshoporder_id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'phone' => $this->phone,
            'designation' => $this->designation,
            'national_id' => $this->national_id,
            'title' => $this->title,
            'gender' => $this->gender,
        ]);
        if($result['status'] == 'success'){
            $this->success('message', $result['message']);
            $this->reset(['name','surname','email','phone','designation','national_id','title','gender']);
        }else{
            $this->error('message', $result['message']);
        }
    }
    public function render()
    {
        return view('livewire.components.workshoporder.newdelegate',[
            'titlelist' => $this->titlelist(),
            'genderlist' => $this->genderlist()
        ]);
    } 
}
