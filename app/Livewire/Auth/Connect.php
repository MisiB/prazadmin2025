<?php

namespace App\Livewire\Auth;

use Dcblogdev\MsGraph\Facades\MsGraph;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Connect extends Component
{
    public function mount()
    {

        try {
            $var =  MsGraph::connect();
            return $var;
        } catch (\Exception $e) {
            dd($e->getCode(), $e->getMessage());
        }
    }
    #[Layout('components.layouts.plain')]
    public function render()
    {
        return view('livewire.auth.connect');
    }
}
