<?php

namespace App\Livewire\Admin\Workshops;

use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\services\iworkshopService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Workshopindex extends Component
{
    use Toast;
    use WithFileUploads;
    use WithPagination;

    protected $workshopService;

    protected $currencyService;

    public $title;

    public $target;

    public $startDate;

    public $endDate;

    public $location;

    public $limit;

    public $status;

    public $search;

    public $currency;

    public $cost;

    public $document;

    public $editDocument;

    public $showCreateModal = false;

    public $showEditModal = false;

    public $showPreviewModal = false;

    public $previewUrl = null;

    public $editingWorkshop = null;

    public $breadcrumbs = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'target' => 'required|string',
        'startDate' => 'required|date',
        'endDate' => 'required|date|after:startDate',
        'location' => 'required|string',
        'limit' => 'required|integer|min:1',
        'status' => 'required',
        'cost' => 'required|numeric|min:0',
        'currency' => 'required',
        'document' => 'nullable|file|max:10240|mimes:pdf,doc,docx',
    ];

    public function boot(iworkshopService $workshopService, icurrencyInterface $currencyService)
    {
        $this->workshopService = $workshopService;
        $this->currencyService = $currencyService;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Workshops'],
        ];
    }

    public function headers(): array
    {
        return [
            ['key' => 'title', 'label' => 'Title'],
            ['key' => 'target', 'label' => 'Target'],
            ['key' => 'start_date', 'label' => 'Start Date'],
            ['key' => 'end_date', 'label' => 'End Date'],
            ['key' => 'location', 'label' => 'Location'],
            ['key' => 'limit', 'label' => 'Limit'],
            ['key' => 'Status', 'label' => 'Status'],
            ['key' => 'Cost', 'label' => 'Cost'],
            ['key' => 'action', 'label' => 'Action'],
        ];
    }

    public function workshops()
    {

        return $this->workshopService->getallworkshops($this->search);
    }

    public function currencies()
    {
        return $this->currencyService->getcurrencies()->where('status', 'ACTIVE');
    }

    public function statuslist(): array
    {
        return $this->workshopService->getStatusList();
    }

    public function targetlist(): array
    {
        return $this->workshopService->getTargetList();
    }

    public function createWorkshop()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'target' => $this->target,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'location' => $this->location,
            'currency_id' => $this->currency,
            'limit' => $this->limit,
            'status' => $this->status,
            'cost' => $this->cost,
            'created_by' => Auth::user()->id,
            'document' => $this->document,
        ];

        $result = $this->workshopService->createWorkshop($data);

        if ($result['status'] === 'success') {
            $this->reset(['title', 'target', 'startDate', 'endDate', 'location', 'currency', 'limit', 'status', 'cost', 'document']);
            $this->showCreateModal = false;
            $this->success('message', $result['message']);
        } else {
            $this->error('message', $result['message']);
        }
    }

    public function editWorkshop($id)
    {
        $workshop = $this->workshopService->getWorkshopById($id);
        $this->editingWorkshop = $workshop;
        $this->title = $workshop->title;
        $this->target = $workshop->target;
        $this->startDate = $workshop->start_date;
        $this->endDate = $workshop->end_date;
        $this->location = $workshop->location;
        $this->limit = $workshop->limit;
        $this->currency = $workshop->currency_id;
        $this->status = $workshop->status;
        $this->cost = $workshop->Cost;
        $this->editDocument = null;
        $this->showEditModal = true;
    }

    public function updateWorkshop()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'target' => 'required|string',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'location' => 'required|string',
            'limit' => 'required|integer|min:1',
            'status' => 'required',
            'cost' => 'required|numeric|min:0',
            'currency' => 'required',
            'document' => 'nullable|file|max:10240|mimes:pdf,doc,docx',
        ]);

        $data = [
            'title' => $this->title,
            'target' => $this->target,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'location' => $this->location,
            'currency_id' => $this->currency,
            'limit' => $this->limit,
            'status' => $this->status,
            'cost' => $this->cost,
            'document' => $this->document,
        ];

        $result = $this->workshopService->updateWorkshop($this->editingWorkshop->id, $data);

        if ($result['status'] === 'success') {
            $this->reset(['title', 'target', 'startDate', 'endDate', 'location', 'currency', 'limit', 'status', 'cost', 'editingWorkshop', 'document']);
            $this->showEditModal = false;
            $this->success('message', $result['message']);
        } else {
            $this->error('message', $result['message']);
        }
    }

    public function deleteWorkshop($id)
    {
        $result = $this->workshopService->deleteWorkshop($id);

        if ($result['status'] === 'success') {
            $this->success('message', $result['message']);
        } else {
            $this->error('message', $result['message']);
        }
    }

    public function previewDocument($documentUrl)
    {
        $this->previewUrl = $documentUrl;
        $this->showPreviewModal = true;
    }

    public function render()
    {
        return view('livewire.admin.workshops.workshopindex', [
            'headers' => $this->headers(),
            'workshops' => $this->workshops(),
            'currencies' => $this->currencies(),
            'statuslist' => $this->statuslist(),
            'targetlist' => $this->targetlist(),
        ]);
    }
}
