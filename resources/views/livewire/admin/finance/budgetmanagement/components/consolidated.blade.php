<div>
    <x-card title="Consolidated" subtitle="Budget Approval Status :{{ strtoupper($budget->status) }}" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <div class="flex gap-2">
                @if(strtoupper($budget->status) == 'PENDING')
                    <x-button wire:click="openImportModal" icon="o-arrow-up-tray" class="btn-sm btn-primary">
                        Import Budget
                    </x-button>
                @endif
                <x-select :options="$departments" option-label="name" option-value="id" wire:model.live="department_id" placeholder="Select Department" />
            </div>
        </x-slot:menu>
   
        <div class="grid grid-cols-3 gap-2">
            <div class="p-5 border-2 border-gray-200 text-center rounded-box">
                <div>Total Budget</div>
               <div class=" text-blue-500">
                {{ number_format($totalbudget, 2) }}
               </div>
            </div>
            <div class="p-5 border-2 border-gray-200 text-center rounded-box">
                <div>Total Utilized</div>
               <div class=" text-red-500">
                {{ number_format($totalutilized, 2) }}
               </div>
            </div>
            <div class="p-5 border-2 border-gray-200 text-center rounded-box">
                <div>Total Remaining</div>
               <div class=" text-green-500">
                {{ number_format($totalremaining, 2) }}
               </div>
            </div>
        </div>
        <x-table :headers="$headers" :rows="$budgetitems" class="table-zebra table-sm">
            <x-slot:empty>
              <x-alert class="alert-error" title="No departmental budget found." />
            </x-slot:empty>
            @scope('cell_unitprice', $row)
             {{ $row->currency?->name }} {{ number_format($row->unitprice, 2) }}
            @endscope
            @scope('cell_total', $row)
            <span class="flex text-blue-500">
             {{ $row->currency?->name }} {{ number_format($row->total, 2) }}
            </span>
            @endscope
  
            @scope('cell_utilized', $row)
            <span class="flex text-red-500">
             {{ $row->currency?->name }} {{ number_format($row->utilized ?? 0, 2) }}
            </span>
            @endscope
            @scope('cell_remaining', $row)
            <span class="flex text-green-500">
             {{ $row->currency?->name }} {{ number_format($row->remaining ?? 0, 2) }}
            </span>
            @endscope
              
      </x-table>
        
    </x-card>

    {{-- Import Budget Modal --}}
    <x-modal wire:model="importModal" title="Import Budget Items" separator>
        <div class="space-y-4">
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">Excel Template Columns</h4>
                <p class="text-sm text-blue-700 mb-2">Your Excel file must have these columns (use exact column names):</p>
                <ul class="text-sm text-blue-600 list-disc list-inside space-y-1">
                    <li><strong>activity</strong> - Activity name (required)</li>
                    <li><strong>description</strong> - Activity description (optional)</li>
                    <li><strong>department</strong> - Department name (required, must match existing)</li>
                    <li><strong>expense_category</strong> - CAPEX or OPEX (required, auto-created if new)</li>
                    <li><strong>programme_title</strong> - Programme title (optional, must match existing if provided)</li>
                    <li><strong>output</strong> - Strategy output (optional, must match existing if provided)</li>
                    <li><strong>source_of_fund</strong> - Source of fund name (required, auto-created if new)</li>
                    <li><strong>quantity</strong> - Number of units (required)</li>
                    <li><strong>unit_price</strong> - Price per unit (required)</li>
                    <li><strong>focus_date</strong> - Target date YYYY-MM-DD (optional)</li>
                </ul>
            </div>

            <x-file wire:model="importFile" label="Select Excel File" accept=".xlsx,.xls,.csv" hint="Supported formats: xlsx, xls, csv (max 10MB)" />

            @if(count($importErrors) > 0)
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg max-h-48 overflow-y-auto">
                    <h4 class="font-semibold text-red-800 mb-2">Import Errors</h4>
                    <ul class="text-sm text-red-600 list-disc list-inside space-y-1">
                        @foreach($importErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Cancel" wire:click="closeImportModal" />
            <x-button label="Download Template" icon="o-arrow-down-tray" wire:click="downloadTemplate" class="btn-outline btn-primary" />
            <x-button label="Import" icon="o-arrow-up-tray" wire:click="importBudget" class="btn-primary" spinner="importBudget" />
        </x-slot:actions>
    </x-modal>
</div>
