<?php

namespace App\Livewire\Profile;

use App\Interfaces\repositories\iaccountsettingInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

class Accountsetting extends Component
{
    use Toast,WithFileUploads;
    public $name;
    public $email;
    public $phone;
    public $address;
    public $logo;
    protected $repository;
    public function boot(iaccountsettingInterface $repository){
        $this->repository = $repository;
    }
 
  

    public function mount(){
      $this->getaccountsettings();
    }

    public function getaccountsettings(){
        $response = $this->repository->getsettings();
        if($response != null){
            $this->name = $response->name;
            $this->email = $response->email;
            $this->phone = $response->phone;
            $this->address = $response->address;
            $this->logo = $response->logo;
        }
    }

    public function save(){
      $this->validate([
        'name' => 'required',
        'email' => 'required',
        'phone' => 'required',
        'address' => 'required',
        'logo' => 'required',
      ]);

      if ($this->logo) { 
        $url = $this->logo->store('logo', 'public');
        $this->logo = "/storage/$url";
      }
      $response = $this->repository->create([
        'name' => $this->name,
        'email' => $this->email,
        'phone' => $this->phone,
        'address' => $this->address,
        'logo' => $this->logo,
      ]);
      if($response['status']=="success"){
        $this->success('Account settings updated successfully');
      }
    }

    public function render()
    {
        return view('livewire.profile.accountsetting');
    }
}
