<?php

namespace App\Livewire\Admin\Configuration;

use App\Interfaces\repositories\ileavetypeInterface;
use Livewire\Component;
use Mary\Traits\Toast;

class Leavetypes extends Component
{ 
    use Toast;
    public $name, $accumulation, $ceiling, $rollover;
    public $id;
    public $modal = false;
    public $breadcrumbs = [];
    protected $leavetyperepo;

    public function boot(ileavetypeInterface $repo)
    {
        $this->leavetyperepo = $repo;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => "Leave Types"]
        ];
    }

    public function headers(): array
    {
        return [
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Accumulation', 'key' => 'accumulation'],
            ['label' => 'Ceiling', 'key' => 'ceiling'],
            ['label' => 'Rollover', 'key' => 'rollover']
        ];
    }

    public function getleavetypes()
    {
        return $this->leavetyperepo->getleavetypes();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required',
            'accumulation' => 'required|numeric',
            'ceiling' => 'required|numeric',
            'rollover' => 'required'
        ]);

        $data = [
            'name' => $this->name,
            'accumulation' => $this->accumulation,
            'ceiling' => $this->ceiling,
            'rollover' => $this->rollover
        ];

        if ($this->id) {
            $this->leavetyperepo->updateleavetype($this->id, $data);
            $this->toast('Leave type updated successfully', 'success');
        } else {
            $this->leavetyperepo->createleavetype($data);
            $this->toast('Leave type created successfully', 'success');
        }

        $this->modal = false;
        $this->reset(['name', 'accumulation', 'ceiling', 'rollover', 'id']);
    }

    public function edit($id)
    {
       $this->id=$id;
       $leavetype=$this->leavetyperepo->getleavetype($id);
       if (!$leavetype) 
       {
            $this->toast('Leave type not found.', 'error');
            return;
        }
        $this->name = $leavetype->name;
        $this->accumulation = $leavetype->accumulation;
        $this->ceiling = $leavetype->ceiling;
        $this->rollover = $leavetype->rollover;
        $this->modal=true;
    }

    public function delete($id)
    {
        $response = $this->leavetyperepo->deleteleavetype($id);
        if ($response['status'] == "success") {
            $this->toast($response['message'], 'success');
        } else {
            $this->toast($response['message'], 'error');
        }
    }

    public function render()
    {
        return view('livewire.admin.configuration.leavetypes', [
            'leavetypes' => $this->getleavetypes(),
            'headers' => $this->headers()
        ]);
    }
}
