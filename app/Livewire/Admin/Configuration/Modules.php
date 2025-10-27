<?php

namespace App\Livewire\Admin\Configuration;

use App\Enums\ApiResponse;
use App\Interfaces\repositories\iaccounttypeInterface;
use App\Interfaces\services\ihttpInterface;
use App\Interfaces\repositories\imoduleInterface;
use Illuminate\Support\Str;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Mary\Traits\Toast;

class Modules extends Component
{
    use Toast;

    /**
     * module parameters
     */

    #[Rule('required')]
    public $name;

    #[Rule('required')]
    public $accounttype;

    #[Rule('required')]
    public $icon;

    #[Rule('required')]
    public $default_permission;

    public $id;

    public $modal = false;


    /**
     * sub module parameters
     */
    public $subname;

    public $subicon;

    public $suburl;

    public $subid;

    public $subdefault_permission;
    public $submodal = false;
    public $adsubmodal = false;

    public $error = null;

    public $module;
    protected $repo;
    protected $atyperepo;
    public $breadcrumbs = [];

    public function boot(imoduleInterface $repo, iaccounttypeInterface $atyperepo)
    {
        $this->repo = $repo;
        $this->atyperepo = $atyperepo;
    }
    public function mount()
    {
        $this->module = null;
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Modules']
        ];
    }
    public function getaccounttypes()
    {
        $response = $this->atyperepo->getaccounttypes();
        $response;
        return $response;
    }

    public  function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'icon', 'label' => 'Icon'],
            ['key' => 'default_permission', 'label' => 'Default Permission'],
        ];
    }

    public function  add($id)
    {
        $this->accounttype = $id;
        $this->modal = true;
    }

    public function save()
    {
        $this->validate();
        if ($this->id) {
            $this->updatemodule();
        } else {
            $this->createmodule();
        }
    }

    public function createmodule()
    {
        $this->validate();
        $response = $this->repo->createmodule([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'accounttype_id' => $this->accounttype,
            'icon' => $this->icon,
            'default_permission' => $this->default_permission
        ]);
        if ($response['status'] == "success") {
            $this->success('Module created successfully.');
            $this->reset(['name', 'icon', 'id', 'accounttype', 'default_permission']);
            $this->error = null;
        } elseif ($response['status'] == "error") {
            $this->error = $response['message'];
        } else {
            $this->error = $response['message'];
        }
    }

    public function edit($id)
    {
        $module = $this->repo->getmodule($id);
        if ($module == ApiResponse::NOT_FOUND->value) {
            $this->error('Module not found.');
            return;
        } elseif ($module == ApiResponse::DATABASE_ERROR->value) {
            $this->error('Database error.');
            return;
        } elseif ($module == null) {
            $this->error('Module not found.');
            return;
        }
        $this->id = $id;
        $this->name = $module->name;
        $this->icon = $module->icon;
        $this->accounttype = $module->accounttype_id;
        $this->default_permission = $module->default_permission;
        $this->modal = true;
    }

    public function updatemodule()
    {
        $this->validate();
        $response = $this->repo->updatemodule($this->id, [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'accounttype_id' => $this->accounttype,
            'icon' => $this->icon,
            'default_permission' => $this->default_permission
        ]);
        if ($response['status'] == "success") {
            $this->success($response['message']);
            $this->reset(['name', 'icon', 'id', 'accounttype', 'default_permission']);
            $this->modal = false;
        } elseif ($response['status'] == "error") {
            $this->error($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function delete($id)
    {
        $response = $this->repo->deletemodule($id);
        if ($response['status'] == "success") {
            $this->success($response['message']);
        } elseif ($response['status'] == "error") {
            $this->error($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.configuration.modules', ['accounttypes' => $this->getaccounttypes(), 'headers' => $this->headers()]);
    }
}
