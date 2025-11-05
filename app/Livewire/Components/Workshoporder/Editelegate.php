<?php

namespace App\Livewire\Components\Workshoporder;

use Livewire\Component;
use Mary\Traits\Toast;
use App\Interfaces\repositories\iworkshopInterface;
class Editelegate extends Component
{
    use Toast;
    public $delegate;
    public $name;
    public $surname;
    public $email;
    public $phone;
    public $designation;
    public $national_id;
    public $title;
    public $gender;
    public $showEditDelegateModal = false;
    protected $workshopRepository;
    public function boot(iworkshopInterface $workshopRepository){
        $this->workshopRepository = $workshopRepository;
    }
    public function mount($delegate){
        $this->delegate = $delegate;
        $this->name = $delegate->name;
        $this->surname = $delegate->surname;
        $this->email = $delegate->email;
        $this->phone = $delegate->phone;
        $this->designation = $delegate->designation;
        $this->national_id = $delegate->national_id;
        $this->title = $delegate->title;
        $this->gender = $delegate->gender;
    }
    public function titlelist(){
        return [['id'=>'Mr','name'=>'Mr'],['id'=>'Mrs','name'=>'Mrs'],['id'=>'Ms','name'=>'Ms'],['id'=>'Dr','name'=>'Dr'],['id'=>'Prof','name'=>'Prof'],['id'=>'Other','name'=>'Other']];
    }
    public function genderlist(){
        return [['id'=>'Male','name'=>'Male'],['id'=>'Female','name'=>'Female']];
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
        $result = $this->workshopRepository->updatedelegate($this->delegate->id,[
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
        return view('livewire.components.workshoporder.editelegate',[
            'titlelist' => $this->titlelist(),
            'genderlist' => $this->genderlist()
        ]);
    }
}
