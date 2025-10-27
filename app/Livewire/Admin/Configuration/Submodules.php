<?php

namespace App\Livewire\Admin\Configuration;

use App\Interfaces\repositories\imoduleInterface;
use App\Interfaces\repositories\ipermissionInterface;
use App\Interfaces\repositories\isubmoduleInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Mary\Traits\Toast;

class Submodules extends Component
{
  use Toast;
  public $id;
  public $subname;
  public $subicon;
  public $subdefault_permission;
  public $suburl;
  public $submodal = false;
  public $module;
  public $subid;
  public $submodule;
  public $permissionmodal = false;
  public $permissions;
  public $permission;
  public $permissionid;
  protected $modulerepo;
  protected $submodulerepo;
  protected $permissionrepo;
  public $error = null;
  public $breadcrumbs = [];

  public function boot(imoduleInterface $repo, isubmoduleInterface $submodulerepo, ipermissionInterface $permissionrepo)
  {
    $this->modulerepo = $repo;
    $this->submodulerepo = $submodulerepo;
    $this->permissionrepo = $permissionrepo;
  }

  public function mount(int $id)
  {
    $this->id = $id;
    $this->module = null;
    $this->getmodule();
    $this->permissions = new Collection();
    $this->breadcrumbs = [
      ['label' => 'Home', 'link' => route('admin.home')],
      ['label' => "Modules", 'link' => route('admin.configuration.modules')],
      ['label' => "Submodules"]
    ];
  }
  public function getmodule()
  {
    $module = $this->modulerepo->getmodule($this->id);
    if (!$module) {
      return $this->error('Module not found.');
    }

    $this->module = $module;
  }

  public function getsubmodule($id)
  {

    $submodule = $this->submodulerepo->getsubmodule($id);

    if (!$submodule) {
      return $this->error('Submodule not found.');
    }

    $this->submodule = $submodule;
    $this->permissions = $submodule->permissions;
    $permissions = $this->submodule->permissions;

    if ($permissions->where('name', $this->submodule->default_permission)->first() == null) {
      $this->processpermission($this->submodule->default_permission);
    }
    if ($permissions->where('name', $this->submodule->module->default_permission)->first() == null) {
      $this->processpermission($this->submodule->module->default_permission);
    }
    $submodule = $this->submodulerepo->getsubmodule($id);
    $this->submodule = $submodule;
    $this->permissions = $submodule->permissions;
    $this->permissionmodal = true;
  }

  public function headers(): array
  {
    return [
      ['key' => 'name', 'label' => 'Name'],
      ['key' => 'icon', 'label' => 'Icon'],
      ['key' => 'default_permission', 'label' => 'Default Permission'],
      ['key' => 'url', 'label' => 'URL'],

    ];
  }

  public function savesubmodule()
  {
    if ($this->subid) {
      $this->updatesubmodule();
    } else {
      $this->addsubmodule();
    }
    $this->getmodule();
  }

  public function addsubmodule()
  {
    $this->validate([
      'subname' => 'required',
      'subicon' => 'required',
      'subdefault_permission' => 'required',
      'suburl' => 'required',
    ]);
    $response = $this->submodulerepo->createsubmodule([
      'name' => $this->subname,
      'slug' => Str::slug($this->subname),
      'icon' => $this->subicon,
      'module_id' => $this->module->id,
      'default_permission' => $this->subdefault_permission,
      'url' => $this->suburl
    ]);
    if ($response['status'] == "success") {
      $this->success($response['message']);
      $this->reset(['subname', 'subicon', 'subdefault_permission', 'suburl']);
      $this->submodal = false;
    } else {
      $this->error = $response['message'];
    }
  }

  public function edit(int $id)
  {
    $submodule = $this->submodulerepo->getsubmodule($id);
    if (!$submodule) {
      return $this->error('Submodule not found.');
    }
    $this->subid = $id;
    $this->subname = $submodule->name;
    $this->subicon = $submodule->icon;
    $this->subdefault_permission = $submodule->default_permission;
    $this->suburl = $submodule->url;
    $this->submodal = true;
  }

  public function updatesubmodule()
  {
    $this->validate([
      'subname' => 'required',
      'subicon' => 'required',
      'subdefault_permission' => 'required',
      'suburl' => 'required',
    ]);
    $response = $this->submodulerepo->updatesubmodule($this->subid, [
      'name' => $this->subname,
      'slug' => Str::slug($this->subname),
      'module_id' => $this->module->id,
      'icon' => $this->subicon,
      'default_permission' => $this->subdefault_permission,
      'url' => $this->suburl
    ]);
    if ($response['status'] == "success") {
      $this->success($response['message']);
      $this->reset(['subname', 'subicon', 'subdefault_permission', 'suburl']);
      $this->submodal = false;
    } elseif ($response['status'] == "error") {
      $this->error($response['message']);
    } else {
      $this->error($response['message']);
    }
  }

  public function delete($id)
  {
    $response = $this->submodulerepo->deletesubmodule($id);
    if ($response['status'] == "success") {
      $this->success($response['message']);
    } elseif ($response['status'] == "error") {
      $this->error($response['message']);
    } else {
      $this->error($response['message']);
    }
  }

  public function savepermission()
  {
    $this->validate([
      'permission' => 'required',
    ]);
    if ($this->permissionid) {
      $this->updatepermission();
    } else {

      $this->processpermission($this->permission);
    }
    $this->getsubmodule($this->submodule->id);
  }

  public function processpermission($name)
  {

    if ($name != '') {
      $response = $this->permissionrepo->createpermission(['submodule_id' => $this->submodule->id, 'name' => $name, "guard_name" => "web"]);
      if ($response != null) {
        if ($response['status'] == "success") {
          $this->success($response['message']);
          $this->reset('permission');
        } else {
          $this->error = $response['message'];
        }
      }
    }
  }

  public function editpermission($id)
  {
    $permission = $this->permissionrepo->getpermission($id);
    if (is_array($permission)) {
      $this->error($permission['message']);
      return;
    }
    $this->permissionid = $id;
    $this->permission = $permission->name;
  }

  public function updatepermission()
  {

    $response = $this->permissionrepo->updatepermission($this->permissionid, ["name" => $this->permission, "submodule_id" => $this->submodule->id, "guard_name" => "web"]);
    if ($response['status'] == "success") {
      $this->success('Permission updated successfully.');
      $this->reset('permission', 'permissionid');
      $this->permissionmodal = false;
    } elseif ($response['status'] == "error") {
      $this->error = $response['message'];
    }
  }

  public function deletepermission($id)
  {
    $response = $this->permissionrepo->deletepermission($id);
    if ($response['status'] == "success") {
      $this->success($response['message']);
      // $this->getsubmodule($this->submodule->id);
    } elseif ($response['status'] == "error") {
      $this->error = $response['message'];
    }
  }
  public function render()
  {
    return view('livewire.admin.configuration.submodules', ['headers' => $this->headers()]);
  }
}
