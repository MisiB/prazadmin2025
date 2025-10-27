<?php

namespace App\Livewire\Admin\Configuration;

use App\Interfaces\repositories\iuserInterface;
use App\Interfaces\repositories\iaccounttypeInterface;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mary\Traits\Toast;

class User extends Component
{
    use Toast;
    
    public $id;
    public $error;
    public $user;
    public $name;
    public $email;
    public $phonenumber;
    public $status;

    protected $http;
    protected $rolehttp;
    protected $accounttype;
    public $breadcrumbs =[];

    public function boot(iuserInterface $http, iaccounttypeInterface $accounttype)
    {
        $this->http = $http;
        $this->accounttype = $accounttype;
    }
    

    public function mount($id)
    {
        $this->id = $id;
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Users', 'link' => route('admin.configuration.users')],
            ['label' => 'User Details']
        ];
        $this->loadData();
    }

    public function loadData()
    {
        $this->user = $this->getuser();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phonenumber = $this->user->phone;
        $this->status = $this->user->status;
    }

    public function getuser()
    {
        $response = $this->http->getuser($this->id);
        return $response;
    }
    
    public function render()
    {
        return view('livewire.admin.configuration.user');
    }
}
