<?php

namespace App\Livewire\Auth;

use App\Interfaces\repositories\iaccountsettingInterface;
use App\Interfaces\repositories\iauthInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Forgot extends Component
{
    public $email;
    public $error;
    public bool $modal = false;
    public  $status = "";
    public  string $message ="";
    protected $repo;
    protected $accountsettingRepository;

    public function boot(iauthInterface $repo, iaccountsettingInterface $accountsettingRepository)
    {
        $this->repo = $repo;
        $this->accountsettingRepository = $accountsettingRepository;
    }
    public function forgot(){
        $this->validate([
            'email' => 'required|email|exists:users,email'
        ]);
        $response = $this->repo->forgotpassword([
            'email' => $this->email
        ]);
        $this->status = $response['status'];
        $this->message = $response['message'];
        $this->modal = true;
      
    }
    public function getaccountsettings(){
        return $this->accountsettingRepository->getsettings();
    }
    #[Layout('components.layouts.plain')]
    public function render()
    {
        return view('livewire.auth.forgot',[
            'accountsetting' => $this->getaccountsettings()
        ]);
    }
}
