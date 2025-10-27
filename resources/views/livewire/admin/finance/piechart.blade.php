<div>
    <x-card :title="$accounumber" class="border-2 border-gray-300 mt-2" separator>
    <div class="grid grid-cols-2 gap-5">
     
   <table class="table table-sm">
    <tbody>
        <tr>
            <td>Total Claimed</td>
            <td>{{ $totalclaimed }}</td>
        </tr>
        <tr>
            <td>Total Pending</td>
            <td>{{ $totalpending }}</td>
        </tr>
        <tr>
            <td>Total Blocked</td>
            <td>{{ $totalblocked }}</td>
        </tr>
        <tr>
            <td colspan="2">
                @php
                 $percentage = number_format(($totalpending / ($totalclaimed + $totalpending + $totalblocked)) * 100, 2);
                @endphp
                <span class="{{ $percentage > 50 ? 'text-red-500' : 'text-gray-500' }} text-xs"> {{ $percentage }}% of  are unclaimed
                   
                </span>
            </td>
        </tr>
        
    </tbody>
    </table>
    <x-chart wire:model="myChart" />
 </div>
 


</x-card>
</div>
