<?php

namespace App\Livewire\Admin\Configuration;

use App\Enums\ApiResponse;
use App\Interfaces\repositories\iaccounttypeInterface;
use App\Interfaces\services\ihttpInterface;
use Illuminate\Support\Collection;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Mary\Traits\Toast;

class Accounttypes extends Component
{
    use Toast;

    #[Rule("required")]
    public $name;

    #[Rule("required")]
    public $description;

    #[Rule("required")]
    public $status;

    #[Rule("required")]
    public $icon;

    public $id;
    public $error = "";

    public $modal = false;
    protected  $repo;
    public bool $networkerror = false;
    public $breadcrumbs = [];
    public function boot(iaccounttypeInterface $repo)
    {
        $this->repo = $repo;
    }
    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Account types']
        ];
    }
    public function getaccounttypes()
    {
        return $this->repo->getaccounttypes();
    }

    public function save()
    {
        $this->validate();
        $response = $this->repo->createaccounttype([
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'status' => $this->status

        ]);
        if ($response["status"] == "success") {
            $this->success($response["message"]);
        } else {
            $this->error = $response["message"];
        }

        $this->reset(['name', 'description', 'status', 'icon']);
    }

    public function edit($id)
    {
        $accounttype = $this->repo->getaccounttype($id);

        if (!$accounttype) {
            $this->error('Account type not found.');
            return;
        }
        $this->name = $accounttype->name;
        $this->description = $accounttype->description;
        $this->status = $accounttype->status;
        $this->icon = $accounttype->icon;
        $this->id = $id;
        $this->modal = true;
    }

    public function update()
    {
        $this->validate();
        $response = $this->repo->updateaccounttype($this->id, [
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'icon' => $this->icon
        ]);
        if ($response["status"] == "success") {
            $this->success($response["message"]);
        } else {
            $this->error = $response["message"];
        }
        $this->reset(['name', 'description', 'status', 'icon','id']);
    }


    public function delete($id)
    {
        $response = $this->repo->deleteaccounttype($id);
        if ($response['status'] == "success") {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'description', 'label' => 'Description'],
            ['key' => 'icon', 'label' => 'Icon'],
            ['key' => 'status', 'label' => 'Status']

        ];
    }
    public function render()
    {
        return view('livewire.admin.configuration.accounttypes', [
            "accounttypes" => $this->getaccounttypes(),
            'headers' => $this->headers()
        ]);
    }
}
