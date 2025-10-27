<?php

namespace App\Livewire\Profile;

use App\Interfaces\repositories\iauthInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class Settings extends Component
{
    use Toast;
    public $name;
    public $email;
    public $phonenumber;
    public $country;
    public $current_password;
    public $password;
    public $password_confirmation;
    protected $repository;
    public $profile = true;
    public $pword = false;
    public $showcode = false;
    public $approvalcode;
    

    public function boot(iauthInterface $repository)
    {
        $this->repository = $repository;
    }

     public function mount(){
        $this->getuser();
        $this->showProfile();
     }

     public function getuser(){
       return $this->repository->getprofile();
      
     }

     public function showProfile(){
        $this->profile = true;
        $this->pword = false;
        $user = $this->getuser();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phonenumber = $user->phonenumber;
        $this->country = $user->country;
     }

     public function updateProfile(){
        $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phonenumber' => 'required',
            'country' => 'required'
        ]);
        $response = $this->repository->updateprofile([
            'name' => $this->name,
            'email' => $this->email,    
            'phonenumber' => $this->phonenumber,
            'country' => $this->country
        ]);
        
        if($response['status']=="success"){
            $this->showProfile();
             $this->success($response['message']);
        

        }
        else{
            $this->error($response['message']);
        }
     }

     public function updatePassword(){
        $this->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed',
        ]);
        $response = $this->repository->updatepassword([
            'password' => $this->password,
            'current_password' => $this->current_password
        ]);
        if($response['status']=="success"){
            $this->success('Password updated successfully');
            $this->showPassword();
        }
        else{
            $this->error($response['message']);
        }
     }

     public function showPassword(){
        $this->profile = false;
        $this->pword = true;
     }

     public function showApprovalCode(){
        $this->profile = false;
        $this->pword = false;
        $this->showcode = true;
     }

     public function updateApprovalCode(){
        $this->validate([
            'approvalcode' => 'required',
        ]);
        $response = $this->repository->updateapprovalcode([
            'approvalcode' => $this->approvalcode
        ]);
        if($response['status']=="success"){
            $this->success('Approval code updated successfully');
            $this->showApprovalCode();
        }
        else{
            $this->error($response['message']);
        }
     }

    public function render()
    {
        return view('livewire.profile.settings',[
            'user'=>$this->getuser()
        ]);
    }
}
