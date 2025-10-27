<?php

namespace App\Livewire\Components;

use App\Interfaces\repositories\iauthInterface;
use Dcblogdev\MsGraph\Facades\MsGraph;
use Livewire\Component;

class Topbar extends Component
{
    public $user;
    protected $auth;

    public function boot(iauthInterface $auth)
    {
        $this->auth = $auth;
       
    }

    public function mount(){
        $this->user = $this->auth->getprofile();
    }

    public function logout()
    {
        $this->auth->Logout();
       return MsGraph::disconnect();
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.components.topbar');
    }
}
