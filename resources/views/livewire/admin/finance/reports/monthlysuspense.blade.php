<div>
    <x-card title="Monthly Suspense Report " wire:loading.class.delay="opacity-50" description="{{ $month ?? date('m') }} {{ $year ?? date('Y') }}" separator>
        <x-slot:menu>
           <x-select wire:model.live="month" :options="$months" option-label="name" option-value="id" placeholder="Select month" />
           <x-select wire:model.live="year" :options="$years" option-label="name" option-value="id" placeholder="Select year" />
        </x-slot:menu>
        
        @if(session()->has('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
      
        <x-table :headers="$headers" :rows="$monthlysuspensewallets">
            @scope('cell_total_amount',$row)
            <div>{{ $row->currency }}{{ number_format($row->total_amount,2) }}</div>
            @endscope
            @scope('cell_total_utilized',$row)
            <div>{{ $row->currency }}{{ number_format($row->total_utilized,2) }}</div>
            @endscope
            @scope('cell_total_balance',$row)
            <div>{{ $row->currency }}{{ number_format($row->total_balance,2) }}</div>
            @endscope
            <x-slot:empty>
                <x-alert class="alert-error" title="No Suspense found." />
            </x-slot:empty>
        </x-table>
       
    </x-card>
</div>
