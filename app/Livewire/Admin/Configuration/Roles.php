<?php

namespace App\Livewire\Admin\Configuration;

use App\Interfaces\repositories\iaccounttypeInterface;
use App\Interfaces\services\ihttpInterface;
use App\Interfaces\repositories\imoduleInterface;
use App\Interfaces\repositories\iroleRepository;
use Illuminate\Support\Collection;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Mary\Traits\Toast;

class Roles extends Component
{
    use Toast;
    protected $repository;

    #[Rule('required|string')]
    public $name;

    public $modal;
    public $id;
    public $accounttype;
    public $permissionmodal = false;
    public array $expanded = [0];
    public $role;
    public $selectedpermissions = [];
    public $removepermissions = [];
    protected $repo;
    protected $rolerepo;
    protected $modulerepo;
    public $error = "";
    public $breadcrumbs = [];

    public function boot(iaccounttypeInterface $repo, iroleRepository $rolerepo, imoduleInterface $modulerepo)
    {
        $this->repo = $repo;
        $this->rolerepo = $rolerepo;
        $this->modulerepo = $modulerepo;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Roles']
        ];
    }


    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name']
        ];
    }

    public function getaccounttypes()
    {
        return $this->repo->getaccounttypes();
    }

    public function add($id)
    {
        $this->accounttype = $id;
        $this->modal = true;
    }

    public function edit($id)
    {
        $role = $this->rolerepo->getrole($id);
        $this->id = $id;
        $this->name = $role->name;
        $this->accounttype = $role->accounttype_id;
        $this->modal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->id) {
            $response = $this->rolerepo->updaterole($this->id, [
                'name' => $this->name
            ]);
        } else {
            $response = $this->rolerepo->createrole([
                'name' => $this->name,
                'accounttype_id' => $this->accounttype
            ]);
        }

        if ($response['status'] == 'success') {
            $this->success($this->id ? 'Role updated successfully.' : 'Role created successfully.');
            $this->reset(['name', 'id', 'accounttype']);
            $this->modal = false;
        } else {
            $this->error = $response['message'];
        }
        $this->reset(['name', 'accounttype']);
        $this->modal = false;
    }

    public function getrole(int $id)
    {
        $response = $this->rolerepo->getrole($id);
        if (!$response) {
            $this->error('Role not found.');
            return;
        }
        $role = $response;
        $modules = $this->getmodules();
        if (count($modules) > 0) {
            foreach ($modules as $module) {
                foreach ($module->submodules as $submodule) {
                    foreach ($submodule->permissions as $permission) {
                        dd($permission);
                        if ($role->hasPermissionTo($permission->name)) {
                            $this->selectedpermissions[] = $permission->name;
                        }
                    }
                }
            }
        }

        $this->name = $role->name;
        $this->id = $role->id;
        $this->accounttype = $role->accounttype_id;
        $this->modal = true;
    }

    public function delete($id)
    {
        $response = $this->rolerepo->deleterole($id);
        if ($response['status'] == 'success') {
            $this->success('Role deleted successfully.');
        } elseif ($response['status'] == 'error') {
            $this->error($response['message']);
        }
    }


    public function getpermissions(int $id)
    {
        $this->role = $id;
        $role = $this->rolerepo->getrole($id);
        $modules = $this->getmodules();
        if (count($modules) > 0) {
            foreach ($modules as $module) {
                foreach ($module->submodules as $submodule) {
                    foreach ($submodule->permissions as $permission) {

                        if ($role->hasPermissionTo($permission->name)) {
                            $this->selectedpermissions[] = $permission->id;
                        }
                    }
                }
            }
        }

        $this->permissionmodal = true;
    }

    public function getmodules()
    {
        $reponse = $this->modulerepo->getmodules();

        return $reponse;
    }

    public function addpermission($id)
    {
        $this->selectedpermissions[] = $id;
    }
    public function removepermission($id)
    {
        $this->selectedpermissions = array_filter($this->selectedpermissions, function ($permission) use ($id) {
            return $permission != $id;
        });
    }

    public function savepermissions()
    {
        $response = $this->rolerepo->assignpermissions($this->role, $this->selectedpermissions);
        if ($response['status'] == 'success') {
            $this->success('Permission added successfully.');
            $this->reset('selectedpermissions');
            $this->getpermissions($this->role);
        } elseif ($response['status'] == 'error') {
            $this->error($response['message']);
        }
    }

    public function saveremovepermission()
    {
        $response = $this->rolerepo->assignpermissions($this->role, $this->removepermissions);
        if ($response['status'] == 'success') {
            $this->success('Permission removed successfully.');
            $this->reset('removepermissions');
            $this->getpermissions($this->role);
        } elseif ($response['status'] == 'error') {
            $this->error($response['message']);
        }
    }



    public function  submodulesheaders()
    {

        return [
            ['key' => 'name', 'label' => 'Name'],

        ];
    }

    public function render()
    {
        return view('livewire.admin.configuration.roles', ['accounttypes' => $this->getaccounttypes(), "modules" => $this->getmodules(), 'headers' => $this->headers(), "submodulesheaders" => $this->submodulesheaders()]);
    }
}
