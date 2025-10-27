<?php

namespace App\Livewire\Admin\Customers;

use App\Interfaces\repositories\icustomerInterface;
use Illuminate\Support\Collection;
use Livewire\Component;
use Mary\Traits\Toast;

class Showlist extends Component
{
    use Toast;
    public $breadcrumbs = [];
    public $search = '';
    public  $modal = false;
    protected $customerrepo;
    public $name;
    public $regnumber;
    public $type;
    public $country;
    public $default_email;
    public $id;
    
    public function boot(icustomerInterface $customerrepo)
    {
        $this->customerrepo = $customerrepo;
    }
    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Customers']
        ];
    }

    public function headers():array{
        return [           
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'regnumber', 'label' => 'Reg Number'],
            ['key' => 'type', 'label' => 'Type'],
            ['key' => 'country', 'label' => 'Country'],
            ['key'=>'default_email', 'label'=>'Default Email']
        ];
    }

    public function rows(){
        if($this->search){
            return $this->customerrepo->search($this->search);
        }
        return new Collection();
    }
    public function edit($id){
        $customer = $this->customerrepo->getCustomerById($id);
        $this->id = $id;
        $this->name = $customer->name;
        $this->regnumber = $customer->regnumber;
        $this->type = $customer->type;
        $this->country = $customer->country;
        $this->default_email = $customer->default_email;
        $this->modal = true;
    }

    public function save(){
        if($this->id){
            $this->update();
        }else{
            $this->create();
        }
    }
        

    public function update(){
 $response = $this->customerrepo->update([
            'name'=>$this->name,
            'regnumber'=>$this->regnumber,
            'type'=>$this->type,
            'country'=>$this->country,
            'default_email'=>$this->default_email
        ],$this->id);
        if($response['status']=='success'){
             $this->success($response['message']);
             $this->modal = false;
            }else{
                $this->error($response['message']);
            }
    }
    public function delete($id){
        $response = $this->customerrepo->delete($id);
        if($response['status']=='success'){
             $this->success($response['message']);
            }else{
                $this->error($response['message']);
            }
    }
    public function  customertypelist():array{
        return [
            ['id'=>'BIDDER','name'=>'BIDDER'],
            ['id'=>'ENTITY','name'=>'ENTITY']
        ];
    }
    public function create(){
        $response = $this->customerrepo->create([
            'name'=>$this->name,
            'regnumber'=>$this->regnumber,
            'type'=>$this->type,
            'country'=>$this->country,
            'default_email'=>$this->default_email
        ]);
        if($response['status']=='success'){
             $this->success($response['message']);
             $this->modal = false;
             $this->search = $this->regnumber;
             $this->reset(['name','regnumber','type','country','default_email']);
            }else{
                $this->error($response['message']);
            }
    }
    public function render()
    {
        if($this->type){
        $this->regnumber = $this->customerrepo->retrieve_last_regnumber($this->type);
        }
        return view('livewire.admin.customers.showlist',[
            'customers'=>$this->rows(),
            'headers'=>$this->headers(),
            'customertypelist'=>$this->customertypelist()
        ]);
    }
}
