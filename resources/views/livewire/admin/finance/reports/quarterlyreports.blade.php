<div>
  <x-card title="Quarterly Report for {{ $year ?? date('Y') }}" separator>
    <x-slot:menu>
      <x-button class="btn-primary" @click="$wire.retrievemodal=true" label="Retrieve"/>
    </x-slot:menu>
    
    @if(session()->has('error'))
      <div class="alert alert-danger">
        {{ session('error') }}
      </div>
    @endif
    
    @if(count($processedData) > 0)
      <div class="mb-6">
        
      
        
        <!-- Data Tables by Inventory Item -->
        @php
          $groupedByInventory = $processedData->sortBy('inventory_name')->groupBy('inventory_name');
        @endphp
        
        @foreach($groupedByInventory as $inventoryName => $items)
          <x-card title="{{ $inventoryName }}" separator class="mb-4">
            <div class="overflow-x-auto">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Currency</th>
                    <th>Q1</th>
                    <th>Q1→Q2</th>
                    <th>Q2</th>
                    <th>Q2→Q3</th>
                    <th>Q3</th>
                    <th>Q3→Q4</th>
                    <th>Q4</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($items as $item)
                    <tr>
                      <td>{{ $item['currency_name'] }}</td>
                      <td>{{ number_format($item['q1_total'], 2) }}</td>
                      <td class="text-center">
                        @if($item['qoq_changes']['Q1_Q2']['trend'] == 'up')
                          <span class="text-success">
                            <x-icon name="o-arrow-trending-up" class="inline h-4 w-4" />
                            {{ $item['qoq_changes']['Q1_Q2']['percentage'] }}%
                          </span>
                        @elseif($item['qoq_changes']['Q1_Q2']['trend'] == 'down')
                          <span class="text-error">
                            <x-icon name="o-arrow-trending-down" class="inline h-4 w-4" />
                            {{ $item['qoq_changes']['Q1_Q2']['percentage'] }}%
                          </span>
                        @else
                          -
                        @endif
                      </td>
                      <td>{{ number_format($item['q2_total'], 2) }}</td>
                      <td class="text-center">
                        @if($item['qoq_changes']['Q2_Q3']['trend'] == 'up')
                          <span class="text-success">
                            <x-icon name="o-arrow-trending-up" class="inline h-4 w-4" />
                            {{ $item['qoq_changes']['Q2_Q3']['percentage'] }}%
                          </span>
                        @elseif($item['qoq_changes']['Q2_Q3']['trend'] == 'down')
                          <span class="text-error">
                            <x-icon name="o-arrow-trending-down" class="inline h-4 w-4" />
                            {{ $item['qoq_changes']['Q2_Q3']['percentage'] }}%
                          </span>
                        @else
                          -
                        @endif
                      </td>
                      <td>{{ number_format($item['q3_total'], 2) }}</td>
                      <td class="text-center">
                        @if($item['qoq_changes']['Q3_Q4']['trend'] == 'up')
                          <span class="text-success">
                            <x-icon name="o-arrow-trending-up" class="inline h-4 w-4" />
                            {{ $item['qoq_changes']['Q3_Q4']['percentage'] }}%
                          </span>
                        @elseif($item['qoq_changes']['Q3_Q4']['trend'] == 'down')
                          <span class="text-error">
                            <x-icon name="o-arrow-trending-down" class="inline h-4 w-4" />
                            {{ $item['qoq_changes']['Q3_Q4']['percentage'] }}%
                          </span>
                        @else
                          -
                        @endif
                      </td>
                      <td>{{ number_format($item['q4_total'], 2) }}</td>
                      <td class="font-semibold">{{ number_format($item['yearly_total'], 2) }}</td>
                    </tr>
                  @endforeach
                </tbody>

              </table>
            </div>
          </x-card>
        @endforeach
        
        <!-- Summary Table -->
        <x-card title="Yearly Summary" separator class="mt-6">
          <div class="overflow-x-auto">
            @php
              $groupedByInventory = $processedData->sortBy('inventory_name')->groupBy('inventory_name');
            @endphp
            
            @foreach($groupedByInventory as $inventoryName => $items)
              <div class="mb-4">
                <h3 class="text-lg font-semibold mb-2">{{ $inventoryName }}</h3>
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>Currency</th>
                      <th>Yearly Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($items->sortBy('currency_name') as $item)
                      <tr>
                        <td>{{ $item['currency_name'] }}</td>
                        <td class="font-semibold">{{ number_format($item['yearly_total'], 2) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endforeach
          </div>
        </x-card>
      </div>
      
    
    @else
      <div class="alert alert-info mt-4">
        <div class="flex-1">
          <x-icon name="o-information-circle" class="h-6 w-6 mr-2" />
          <span>Use the Retrieve button to generate a quarterly report</span>
        </div>
      </div>
    @endif
  </x-card>
  
  <x-modal wire:model="retrievemodal" title="Retrieve Report">
    <x-form wire:submit="getquarterlyreport">
      <div class="grid gap-2">
        <x-input label="Year" wire:model="year" type="number" min="2020" max="2030"/>
        <x-select label="Status" wire:model="status" placeholder="Select Status" :options="$statuslist" option-label="name" option-value="id"/>
        <x-choices label="Inventory Item" wire:model="inventoryitems" placeholder="Select Inventory Item" :options="$inventoryitemlist" option-label="name" option-value="id" multiple/>
        <x-choices label="Currency" wire:model="currencyitems" placeholder="Select Currency" :options="$currencyitemlist" option-label="name" option-value="id" multiple/>
      </div>
      <x-slot:actions>
        <x-button label="Cancel" @click="$wire.retrievemodal=false"/>
        <x-button label="Retrieve" type="submit" class="btn-primary" spinner="getquarterlyreport"/>
      </x-slot:actions>
    </x-form>
  </x-modal>
</div>
