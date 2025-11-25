<?php

namespace App\Livewire\Admin\Procurements;

use App\Interfaces\repositories\itenderInterface;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Tendertype extends Component
{
    use WithPagination, Toast;

    public $name;
    public $id;
    public $modal = false;
    public $breadcrumbs = [];
    protected $repo;

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Tender Types']
        ];
    }

    public function boot(itenderInterface $repo)
    {
        $this->repo = $repo;
    }

    public function gettypes()
    {
        return $this->repo->gettendertypes();
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name']
        ];
    }

    public function save()
    {
        $this->validate([
            'name' => 'required'
        ]);

        if ($this->id) {
            $this->update();
        } else {
            $this->create();
        }

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset('name', 'id');
        $this->modal = false;
    }

    public function create()
    {
        $response = $this->repo->createtendertype([
            'name' => $this->name
        ]);

        $this->toastResponse($response);
    }

    public function edit($id)
    {
        $this->id = $id;
        $type = $this->repo->gettendertype($id);

        if (!$type) {
            $this->error("Tender type not found.");
            return;
        }

        $this->name = $type->name;
        $this->modal = true;
    }

    public function update()
    {
        $response = $this->repo->updatetendertype($this->id, [
            'name' => $this->name
        ]);

        $this->toastResponse($response);
    }

    public function delete($id)
    {
        $response = $this->repo->deletetendertype($id);
        $this->toastResponse($response);
    }

    private function toastResponse($response)
    {
        if ($response['status'] === "success") {
            $this->success($response['message']);
        } else {
            $this->error($response['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.procurements.tendertype', [
            'types' => $this->gettypes(),
            'headers' => $this->headers()
        ]);
    }
}
