<?php

namespace App\Livewire\Admin\Finance;

use Livewire\Component;

class Latesttransactions extends Component
{
    public $latesttransactions;
    public function mount($latesttransactions)
    {
        $this->latesttransactions = $latesttransactions;
    }
    public function render()
    {
        return view('livewire.admin.finance.latesttransactions');
    }
}
