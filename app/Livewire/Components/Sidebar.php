<?php

namespace App\Livewire\Components;

use App\Interfaces\repositories\iaccountsettingInterface;
use App\Interfaces\repositories\imoduleInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{

    protected $repository;
    protected  $accountsettingRepository;

    public function boot(imoduleInterface $repository, iaccountsettingInterface $accountsettingRepository)
    {
        $this->repository = $repository;
        $this->accountsettingRepository = $accountsettingRepository;
    }

 

   public function getuserpermissions(){

   $data =Auth::user()->getPermissionsViaRoles()->pluck('name')->toArray();
  
   return $data;
   }
   public function getaccountsettings(){
    return $this->accountsettingRepository->getsettings();
   }
   public function getmodules()
    {
        return $this->repository->getmodules();
     
    }
    public function render()
    {
        return view('livewire.components.sidebar',[
            "modules"=>$this->getmodules(),
            "permissions"=>$this->getuserpermissions(),
            "accountsetting"=>$this->getaccountsettings()
        ]);
    }
}
