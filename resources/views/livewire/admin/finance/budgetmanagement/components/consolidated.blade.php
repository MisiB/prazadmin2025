<div>
    <x-card title="Consolidated" subtitle="Budget Approval Status :{{ strtoupper($budget->status) }}" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-select :options="$departments" option-label="name" option-value="id" wire:model.live="department_id" placeholder="Select Department" />
        </x-slot:menu>
   
        <div class="grid grid-cols-3 gap-2">
            <div class="p-5 border-2 border-gray-200 text-center rounded-box">
                <div>Total Budget</div>
               <div class=" text-blue-500">
                {{ $totalbudget }}
               </div>
            </div>
            <div class="p-5 border-2 border-gray-200 text-center rounded-box">
                <div>Total Utilized</div>
               <div class=" text-red-500">
                {{ $totalutilized }}
               </div>
            </div>
            <div class="p-5 border-2 border-gray-200 text-center rounded-box">
                <div>Total Remaining</div>
               <div class=" text-green-500">
                {{ $totalremaining }}
               </div>
            </div>
        </div>
        <x-table :headers="$headers" :rows="$budgetitems" class="table-zebra table-sm">
            <x-slot:empty>
              <x-alert class="alert-error" title="No departmental budget found." />
            </x-slot:empty>
            @scope('cell_unitprice', $row)
             {{ $row->currency->name }} {{ $row->unitprice }}
            @endscope
            @scope('cell_total', $row)
            <span class="flex text-blue-500">
             {{ $row->currency->name }} {{ $row->total }}
            </span>
            @endscope
  
            @scope('cell_utilized', $row)
            <span class="flex text-red-500">
             {{ $row->currency->name }} {{ $row->utilized??0 }}
            </span>
            @endscope
            @scope('cell_remaining', $row)
            <span class="flex text-green-500">
             {{ $row->currency->name }} {{ $row->remaining??0 }}
            </span>
            @endscope
              
      </x-table>
        
    </x-card>
</div>
