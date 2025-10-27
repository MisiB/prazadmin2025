<?php

namespace App\Livewire\Auth;

use App\Interfaces\repositories\iaccountsettingInterface;
use App\Interfaces\repositories\iauthInterface;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

class Resetpassword extends Component
{
 use Toast;
    public $email;
    public $password;
    public $password_confirmation;
    public $error ="";
    public $modal = false;
    public $status = false;
    public $message ="";
    public $token;
    protected $repo;
    protected $accountsettingRepository;
    public function boot(iauthInterface $repo, iaccountsettingInterface $accountsettingRepository)
    {
        $this->repo = $repo;
        $this->accountsettingRepository = $accountsettingRepository;
    }


    public function PasswordReset(){
        $this->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password'
        ]);
        $response = $this->repo->resetpassword([
            'email' => $this->email,
            'token' => $this->token,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation
        ]);
        if($response['status'] == 'success'){
            $this->success($response['message']);
            $this->reset(['email','password','password_confirmation']);
            $this->redirectRoute('login');
        }else{
            $this->error($response['message']);
        }
    }

    #[Layout('components.layouts.plain')]
    public function render()
    {
        return view('livewire.auth.resetpassword',[
            'accountsetting' => $this->getaccountsettings()
        ]);
    }
    public function getaccountsettings(){
        return $this->accountsettingRepository->getsettings();
    }
}
