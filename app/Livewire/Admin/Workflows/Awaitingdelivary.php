<?php

namespace App\Livewire\Admin\Workflows;

use App\Interfaces\repositories\ipurchaseerequisitionInterface;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Awaitingdelivary extends Component
{
    use Toast, WithFileUploads, WithPagination;

    public $search;

    public $year;

    public $breadcrumbs = [];

    protected $repository;

    public $purchaserequisition = null;

    public $modal = false;

    public $documents;

    public $documentmodal = false;

    public $purchaserequisitionaward_id = null;

    public $currentdocument = null;

    public $viewdocumentmodal = false;

    // Delivery tracking
    public $deliverymodal = false;

    public $selectedAwardId = null;

    public $quantity_delivered = 0;

    public $delivery_date;

    public $delivery_notes;

    public $deliveryNotesModal = false;

    public $invoice_file;

    public $delivery_note_file;

    public $tax_clearance_file;

    public function mount()
    {
        $this->year = date('Y');
        $this->documents = new Collection;
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Awaiting Delivery'],
        ];
    }

    public function boot(ipurchaseerequisitionInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getawaitingdelivary()
    {
        return $this->repository->getpurchaseerequisitionbystatus($this->year, 'AWAITING_DELIVERY');
    }

    public function headers(): array
    {
        return [
            ['key' => 'year', 'label' => 'Year'],
            ['key' => 'prnumber', 'label' => 'PR Number'],
            ['key' => 'department.name', 'label' => 'Department'],
            ['key' => 'budgetitem', 'label' => 'Budget Item'],
            ['key' => 'purpose', 'label' => 'Purpose'],
            ['key' => 'quantity', 'label' => 'Quantity'],
            ['key' => 'unitprice', 'label' => 'Unit Price'],
            ['key' => 'total', 'label' => 'Total'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'created_at', 'label' => 'Created At'],
            ['key' => 'updated_at', 'label' => 'Updated At'],
            ['key' => 'action', 'label' => ''],
        ];
    }

    public function getpurchaseerequisition($id)
    {
        $this->purchaserequisition = $this->repository->getpurchaseerequisition($id);
        $this->modal = true;
    }

    public function ViewDocument($id)
    {
        $document = $this->documents->where('id', $id)->first();
        $this->currentdocument = asset('storage/'.$document->filepath);
        $this->viewdocumentmodal = true;
    }

    public function getdocuments($id)
    {
        $this->documents = $this->repository->getawarddocuments($id);
        $this->documentmodal = true;
        $this->purchaserequisitionaward_id = $id;
    }

    public function openDeliveryModal($awardId)
    {
        $this->selectedAwardId = $awardId;
        $award = $this->repository->getaward($awardId);
        $this->quantity_delivered = 0;
        $this->delivery_date = now()->format('Y-m-d');
        $this->delivery_notes = null;
        $this->invoice_file = null;
        $this->delivery_note_file = null;
        $this->tax_clearance_file = null;
        $this->deliverymodal = true;
    }

    public function recordDelivery()
    {
        $this->validate([
            'quantity_delivered' => 'required|integer|min:1',
            'delivery_date' => 'required|date',
            'delivery_notes' => 'nullable|string|max:1000',
            'invoice_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'delivery_note_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'tax_clearance_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data = [
            'quantity_delivered' => $this->quantity_delivered,
            'delivery_date' => $this->delivery_date,
            'delivery_notes' => $this->delivery_notes,
        ];

        // Handle file uploads
        if ($this->invoice_file) {
            $data['invoice_filepath'] = $this->invoice_file->store('delivery-documents', 'public');
        }
        if ($this->delivery_note_file) {
            $data['delivery_note_filepath'] = $this->delivery_note_file->store('delivery-documents', 'public');
        }
        if ($this->tax_clearance_file) {
            $data['tax_clearance_filepath'] = $this->tax_clearance_file->store('delivery-documents', 'public');
        }

        $response = $this->repository->recorddelivery($this->selectedAwardId, $data);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            $this->reset(['quantity_delivered', 'delivery_date', 'delivery_notes', 'selectedAwardId', 'invoice_file', 'delivery_note_file', 'tax_clearance_file']);
            $this->deliverymodal = false;
            // Refresh the requisition data to show updated delivery history
            if ($this->purchaserequisition) {
                $this->purchaserequisition = $this->repository->getpurchaseerequisition($this->purchaserequisition->id);
            }
        } else {
            $this->error($response['message']);
        }
    }

    public function createPaymentRequisition($awardId): void
    {
        $response = $this->repository->createPaymentRequisitionForAward($awardId);

        if ($response['status'] == 'success') {
            $this->success($response['message']);
            // Refresh the requisition data to show updated information
            if ($this->purchaserequisition) {
                $this->purchaserequisition = $this->repository->getpurchaseerequisition($this->purchaserequisition->id);
            }
        } else {
            $this->error($response['message']);
        }
    }

    public function render()
    {
        return view('livewire.admin.workflows.awaitingdelivary', [
            'rows' => $this->getawaitingdelivary(),
            'headers' => $this->headers(),
        ]);
    }
}
