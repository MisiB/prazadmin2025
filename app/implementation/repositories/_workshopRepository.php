<?php

namespace App\implementation\repositories;


use App\Interfaces\repositories\iworkshopInterface;
use App\Models\Workshop;
use App\Models\workshoporder;
use App\Models\WorkshopInvoice;
use App\Models\Customer;
use App\Models\WorkshopDelegate;
use Illuminate\Support\Facades\DB;


class _workshopRepository implements iworkshopInterface
{
    protected $model;
    protected $ordermodel;
    protected $invoicemodel;
    public  $customermodel;
    protected $delegatemodel;
 
    public function __construct(Workshop $model,workshoporder $ordermodel,WorkshopInvoice $invoicemodel,Customer $customermodel,WorkshopDelegate $delegatemodel)
    {
        $this->model = $model;
        $this->ordermodel = $ordermodel;
        $this->invoicemodel = $invoicemodel;
        $this->customermodel = $customermodel;
        $this->delegatemodel = $delegatemodel;
    }
    public function getallworkshops($search=null){
     
        return $this->model->orderBy('created_at','desc')->when($search, function($query) use ($search){
            $query->where('title','like','%'.$search.'%');
        })->paginate(10);

    }
    public function getworkshopinvoices($workshop_id,$status=null,$currency_id=null){
        $invoices= $this->invoicemodel->where('workshop_id',$workshop_id)->when($status, function($query) use ($status){
            $query->where('status',$status);
        })->when($currency_id, function($query) use ($currency_id){
            $query->where('currency_id',$currency_id);
        })->get();
        return $invoices;
    }
   public function getworkshopbyid($id){

        return $this->model->with('orders')->with('delegates')->with('invoices')->find($id);

   }
   public function updateworkshop($id,$data){
    try{
        if($data['document']){
            $document = $data['document']->store('workshop-documents','public');
            $data['document'] = $document;
        }
        unset($data['document']);
        $this->model->find($id)->update($data);
        return ['status'=>'success','message'=>'Workshop updated successfully'];
    }catch(\Exception $e){
        return ['status'=>'error','message'=>$e->getMessage()];
    }

   }
   public function createworkshop($data){
    try{
    $documenturl = $data['document']->store('workshop-documents','public');
      unset($data['document']);
            $data['document_url'] = $documenturl;
            $this->model->create($data);
            return ['status'=>'success','message'=>'Workshop created successfully'];
        }catch(\Exception $e){
            return ['status'=>'error','message'=>$e->getMessage()];
        }
   }
   public function deleteworkshop($id){
    try{
        $workshop = $this->model->with('orders')->first();
        if($workshop->orders->count() > 0){
            return ['status'=>'error','message'=>'Workshop has orders and cannot be deleted'];
        }
        $workshop->delete();
        return ['status'=>'success','message'=>'Workshop deleted successfully'];
    }catch(\Exception $e){
        return ['status'=>'error','message'=>$e->getMessage()];
    }

   }
   public function getopenworkshops(){
    return $this->model->with('currency')->where('status','PUBLISHED')->where('end_date','>=',now())->orderBy('start_date','asc')->get();

   }
   public function viewworkshop($id){
    return $this->model->with('orders')->find($id);
   }
   public function getworkshopinvoicebyid($id){
    return $this->invoicemodel->with('customer','currency','workshop','workshoporder')->find($id);
   }
   public function createorder($data){
    $check  = $this->ordermodel->where('customer_id',$data['customer_id'])->where('workshop_id',$data['workshop_id'])->first();
    if($check){
        return ['status'=>'error','message'=>'Order already exists'];
    }
    $ordernumber = "ORD-".date('Ymd')."-".rand(1000,9999)."-".$data['customer_id'];
    $invoicenumber = "INV-".date('Ymd')."-".rand(1000,9999)."-".$data['customer_id'];
    $customer = $this->customermodel->find($data['customer_id']);
    try{
    DB::beginTransaction();
    $order = $this->ordermodel->create([
        'customer_id' => $data['customer_id'],
        'workshop_id' => $data['workshop_id'],
        'currency_id' => $data['currency_id'],
        'exchangerate_id' => $data['exchangerate_id'],
        'delegates' => $data['delegates'],
        'name' => $data['name'],
        'surname' => $data['surname'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'amount' => $data['cost'],
        'ordernumber' => $ordernumber,
        'invoicenumber' => $invoicenumber,
        'status' => 'PENDING',
    ]);
   /* $invoice = $this->invoicemodel->create([
        'workshop_id' => $data['workshop_id'],
        'name' => $data['name'],
        'surname' => $data['surname'],
        'email' => $data['email'],
        'organisation' => $customer->name,
        'invoicenumber' => $invoicenumber,
        'delegates' => $data['delegates'],
        'cost' => $data['cost'],
        'currency_id' => $data['currency_id'],
        'account_type' => $customer->type,
        'customer_id' => $customer->id,
        'prnumber' => $customer->regnumber,
        'status' => 'PENDING',
    ]);*/
    DB::commit();
    return ['status'=>'success','message'=>'Order created successfully'];
    }catch(\Exception $e){
        DB::rollBack();
        return ['status'=>'error','message'=>$e->getMessage()];
    }
   }
   public function updateorder($id,$data){
    try{
        DB::beginTransaction();
        $order = $this->ordermodel->find($id);
        $order->update($data);
        DB::commit();
        return ['status'=>'success','message'=>'Order updated successfully'];
    }catch(\Exception $e){
        DB::rollBack();
        return ['status'=>'error','message'=>$e->getMessage()];
   }
   }
   public function deleteorder($id){
    try{
        DB::beginTransaction();
        $order = $this->ordermodel->find($id);
        $order->delete();
        DB::commit();
        return ['status'=>'success','message'=>'Order deleted successfully'];
    }catch(\Exception $e){
        DB::rollBack();
        return ['status'=>'error','message'=>$e->getMessage()];
    }
   }
   public function getorder($id){
    return $this->ordermodel->with('workshop','customer','currency','exchangerate','invoice','delegatelist')->find($id);
   }
   public function getorders($workshop_id){
    return $this->ordermodel->where('workshop_id',$workshop_id)->get();
   }
   public function saveorderdocument($order_id,$data){
    try{
        DB::beginTransaction();
        $order = $this->ordermodel->with('customer')->find($order_id);
        $order->documenturl = $data['document_url'];
        $order->save();
        $invoice = $this->invoicemodel->where('invoicenumber',$order->invoicenumber)->first();
        if($invoice ==null){
            $invoice = $this->invoicemodel->create([
        'workshop_id' => $order->workshop_id,
        'name' => $order->name,
        'surname' => $order->surname,
        'email' => $order->email,
        'organisation' => $order->customer->name,
        'invoicenumber' => $order->invoicenumber,
        'delegates' => $order->delegates,
        'cost' => $order->amount,
        'currency_id' => $order->currency_id,
        'account_type' => $order->customer->type,
        'customer_id' => $order->customer->id,
        'prnumber' => $order->customer->regnumber,
        'status' => 'AWAITING',
    ]);
    $order->update(['status' => 'AWAITING']);
        }
        DB::commit();
        return ['status'=>'success','message'=>'Document saved successfully'];
    }catch(\Exception $e){
        DB::rollBack();
        return ['status'=>'error','message'=>$e->getMessage()];
    }
   }
   public function getorderbyid($id){
    return $this->ordermodel->find($id);
   }

   public function adddelegate($data){
    try{
        DB::beginTransaction();
        $order = $this->ordermodel->with('delegatelist','customer')->find($data['workshoporder_id']);
        if($order->delegates <= $order->delegatelist->count()){
                return ['status'=>'error','message'=>'Maximum delegates reached'];
            }
        $delegate = $this->delegatemodel->create([
            'workshoporder_id' => $order->id,
            'workshop_id' => $order->workshop_id,
            'name' => $data['name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'designation' => $data['designation'],
            'national_id' => $data['national_id'],
            'title' => $data['title'],
            'gender' => $data['gender'],
            'type' => $order->customer->type,
            'company' => $order->customer->name,
        ]);
        DB::commit();
        return ['status'=>'success','message'=>'Delegate added successfully'];
    }catch(\Exception $e){
        DB::rollBack();
        return ['status'=>'error','message'=>$e->getMessage()];
    }
   }
   public function updatedelegate($id,$data){
    try{
        DB::beginTransaction();
        $delegate = $this->delegatemodel->find($id);
        $delegate->update([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'designation' => $data['designation'],
            'national_id' => $data['national_id'],
            'title' => $data['title'],
            'gender' => $data['gender'],
        ]);
        DB::commit();
        return ['status'=>'success','message'=>'Delegate updated successfully'];
    }catch(\Exception $e){
        DB::rollBack();
        return ['status'=>'error','message'=>$e->getMessage()];
    }
   }
   public function getordersbyregnumber($regnumber){
    $customer = $this->customermodel->where('regnumber',$regnumber)->first();
    if($customer == null){
        return ['status'=>'error','message'=>'Customer not found'];
    }
    $orders = $this->ordermodel->with('workshop','customer','currency','exchangerate','invoice','delegatelist')->where('customer_id',$customer->id)->get();
    return $orders;
   }
   
   public function deletedelegate($id){
    try{
        DB::beginTransaction();
        $delegate = $this->delegatemodel->find($id);
        $delegate->delete();
        DB::commit();
        return ['status'=>'success','message'=>'Delegate deleted successfully'];
    }catch(\Exception $e){
        DB::rollBack();
        return ['status'=>'error','message'=>$e->getMessage()];
    }
   }
}
