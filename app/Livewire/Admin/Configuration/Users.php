<?php

namespace App\Livewire\Admin\Configuration;

use App\Interfaces\repositories\iaccounttypeInterface;
use App\Interfaces\services\ihttpInterface;

use App\Interfaces\repositories\iuserInterface;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Users extends Component
{
    use Toast, WithPagination;
    public $name;
    public $email;
    public $country;
    public $status;
    public $modal = false;
    public $id;
    public $user;
    public $gender;
    protected $http;

    public $search;
    public $page = 1;
    public $error;
    protected $repo;
    protected $accounttype;
    public array $selectedaccounttypearray = [];
    public array $selectedroles = [];
    public  $roles;

    public $breadcrumbs;


    public function boot(iuserInterface $repo, iaccounttypeInterface $accounttype)
    {
        $this->repo = $repo;
        $this->accounttype = $accounttype;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Users']
        ];
        $this->getusers();
        $this->roles = new Collection();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getaccounttypes()
    {
        return $this->accounttype->getaccounttypes();
    }
    public function defaultheaders(): array
    {
        return [
            ['label' => 'Name', 'key' => 'name']
        ];
    }

    public function headers(): array
    {
        return [
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Email', 'key' => 'email'],
            ['label' => 'Gender', 'key' => 'gender'],
            ['label' => 'Country', 'key' => 'country'],
            ['label' => 'Status', 'key' => 'status'],
            ['label' => '', 'key' => 'actions'],
        ];
    }

    public  function getusers()
    {
        return $this->repo->getusers($this->search);
    }

    public function addUser()
    {
        $this->selectedroles = [];
        $this->modal = true;
    }

    public function edit($id)
    {
        $users = $this->getusers();
        $user = $users->where('user_id', $id)->first();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->gender = $user->gender;
        $this->country = $user->country;
        $this->status = $user->status;
        $this->id = $user->id;

        $accounttypes = $this->getaccounttypes();
        foreach ($accounttypes as $accounttype) {
            foreach ($accounttype->roles as $role) {
                if ($user->hasRole($role->name)) {
                    $this->selectedroles[] = $role->id;
                }
            }
        }
        $this->modal = true;
    }

    public function save()
    {
        if ($this->id) {
            $this->updaterecord();
        } else {
            $this->createrecord();
        }
    }

    public function addrole($roleid)
    {
        $this->selectedroles[] = $roleid;
    }

    public function deleterole($roleid)
    {
        $this->selectedroles = array_filter($this->selectedroles, fn($id) => $id !== $roleid);
    }

    public function createrecord()
    {
        $response = $this->repo->createuser([
            'name' => $this->name,
            'email' => $this->email,
            'gender' => $this->gender,
            'status' => $this->status
        ], $this->selectedroles);
        if ($response['status'] == "success") {
            $this->success('User created successfully.');
            $this->reset(['name', 'email', 'gender', 'status']);
            $this->modal = false;
        } else {
            $this->error = $response['message'];
        }
    }

    public function updaterecord()
    {

        $response = $this->repo->updateuser($this->id, [
            'name' => $this->name,
            'email' => $this->email,
            'gender' => $this->gender,
            'status' => $this->status
        ], $this->selectedroles);
        if ($response['status'] == "success") {
            $this->success('User updated successfully.');
            $this->reset(['name', 'email', 'gender', 'status']);
            $this->modal = false;
        } else {
            $this->error = $response['message'];
        }
    }


    public function delete($id)
    {
        $response = $this->repo->deleteuser($id);
        if ($response['status'] == "success") {
            $this->success('User deleted successfully.');
            $this->modal = false;
            $this->reset(['name', 'email', 'phonenumber', 'country', 'status']);
            $this->selectedroles = [];
            $this->id = null;
        } else {
            $this->error = $response['message'];
        }
    }
    public function render()
    {
        return view('livewire.admin.configuration.users', [
            'users' => $this->getusers(),
            'headers' => $this->headers(),
            'breadcrumbs' => $this->breadcrumbs,
            "accounttypes" => $this->getaccounttypes(),
            'defaultheaders' => $this->defaultheaders()
        ]);
    }
}
