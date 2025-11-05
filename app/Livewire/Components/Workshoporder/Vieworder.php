<?php

namespace App\Livewire\Components\Workshoporder;

use Livewire\Component;
use App\Interfaces\services\iworkshopService;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Storage;
class Vieworder extends Component
{
    use WithFileUploads,Toast;
    public $orderid;
    public $order;
    public $showViewOrderModal = false;
    public $showAttachDocumentModal = false;
    public $showViewDocumentModal = false;
    public $document;
    public $currentdocument;
    protected $workshopService;
    public function boot(iworkshopService $workshopService)
    {
        $this->workshopService = $workshopService;
    }
    public function mount($orderid)
    {
        $this->orderid = $orderid;
        $this->order = null;
    }
    public function getorder()
    {
        $this->order = $this->workshopService->getorder($this->orderid);
        if($this->order){
            $this->showViewOrderModal = true;
        }else{
            $this->error('message','Order not found');
        }
    }

    public function attachdocument()
    {
       $filepath =   $this->document->store('documents','public');
       $data = [
        'order_id' => $this->orderid,
        'document_url' => $filepath
       ];
       $response = $this->workshopService->saveorderdocument($this->orderid,$data);
       if($response['status'] == 'success'){
        $this->success($response['message']);
        $this->showAttachDocumentModal = false;
       }else{
        $this->error($response['message']);
       }
    }
    public function viewdocument()
    {
        $this->currentdocument = Storage::url($this->order->documenturl);
        $this->showViewDocumentModal = true;
    }

    public function downloadorder()
    {
        try {
            // Load the order with all necessary relationships, including nested relationships
          
            $order = $this->order;
            
            $data = [
                // Order data
                'id' => $order->id,
                'ordernumber' => $order->ordernumber,
                'name' => $order->name,
                'surname' => $order->surname,
                'email' => $order->email,
                'delegates' => $order->delegates,
                'amount' => $order->amount,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                
                // Relationships
                'workshop' => $order->workshop ? [
                    'id' => $order->workshop->id,
                    'title' => $order->workshop->title,
                    'target' => $order->workshop->target,
                    'location' => $order->workshop->location,
                    // 'StartDate' => $order->workshop->StartDate,
                    // 'EndDate' => $order->workshop->EndDate,
                    'cost' => $order->amount,
                    'currency' => $order->currency ? [
                        'id' => $order->currency->id,
                        'name' => $order->currency->name,
                        'symbol' => $order->currency->name,
                    ] : null,
                ] : null,
                'account' => $order->customer ? $order->customer->toArray() : null,
                'currency' => $order->currency ? $order->currency->toArray() : null,
                'invoice' => $order->invoice ? $order->invoice->toArray() : null,
            ];
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.workshopinvoice', [
                'data' => $data
            ]);
            
            $filename = 'Invoice_' . $order->ordernumber . '_' . date('Y-m-d') . '.pdf';
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->stream();
            }, $filename);
            
        } catch (\Exception $e) {
            $this->error('Failed to generate invoice: ' . $e->getMessage());
            return;
        }
    }
    public function render()
    {
        return view('livewire.components.workshoporder.vieworder');
    }
}
