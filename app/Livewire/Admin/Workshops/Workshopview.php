<?php

namespace App\Livewire\Admin\Workshops;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\WithFileUploads;
use App\Interfaces\services\iworkshopService;
use Livewire\Attributes\On;
class Workshopview extends Component
{
    use Toast;
    use WithFileUploads;

    protected $workshopService;
    public $workshop;
    public $breadcrumbs;
    public $id;
    public $selectedTab = 'awaiting-tab';
    public function boot(iworkshopService $workshopService)
    {
        $this->workshopService = $workshopService;
     
    }

    public function mount($id)
    {
        $this->id = $id;
        $this->workshop = $this->getworkshop();
        $this->breadcrumbs = [
            ['link' => route('admin.home'), 'label' => 'Home'],
            ['link' => route('admin.workshop.index'), 'label' => 'Workshops'],
            ['label' => $this->workshop->Title]
        ];
       
    }

    #[On('ordercreated')]
    public function getworkshop()
    {
        return $this->workshopService->getWorkshopById($this->id);
    }


    public function render()
    {
        return view('livewire.admin.workshops.workshopview');
    }


}
