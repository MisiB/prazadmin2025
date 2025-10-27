<?php

namespace App\Livewire\Auth;

use App\Enums\ApiResponse;
use App\Interfaces\repositories\iaccountsettingInterface;
use App\Interfaces\repositories\iauthInterface;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Mary\Traits\Toast;

class Login extends Component
{
    use Toast;

    #[Rule('required|email')]
    public $email;
    
    #[Rule('required|min:8')]
    public $password;
    public $error;
    protected $auth;
    protected $accountsettingRepository;
    public $loginmodal = false;

    public function boot(iauthInterface $auth, iaccountsettingInterface $accountsettingRepository)
    {
        $this->auth = $auth;
        $this->accountsettingRepository = $accountsettingRepository;
    }

    public function mount(){
       $response= $this->auth->getprofile();
       if($response!=null){
       $this->redirectRoute('admin.home');
       }
    }

    public function getaccountsettings(){
        return $this->accountsettingRepository->getsettings();
    }

    public function login()
    {
        $this->validate([
            'email'=>'required',
            'password'=>'required'
        ]);
        $response = $this->auth->login([
            'email' => $this->email,
            'password' => $this->password
        ]);
        if($response){
            $this->redirectRoute('admin.home');
        }else{
            $this->error = ApiResponse::AUTH_FAILURE->getMessage();
        }
    }
    #[Layout('components.layouts.plain')]
    public function render()
    {
        return view('livewire.auth.login',[
            'accountsetting' => $this->getaccountsettings()
        ]);
    }
}
