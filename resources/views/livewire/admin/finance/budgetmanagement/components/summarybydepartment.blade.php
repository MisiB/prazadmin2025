<div>
   <x-card title="Summary by department" subtitle="Budget Approval Status :{{ strtoupper($budget->status) }}" separator class="mt-5 border-2 border-gray-200">
    <table class="table table-zebra table-sm">
        <thead>
            <tr>
                <th>Department</th>
                <th>Total Budget</th>
                <th>Total Utilized</th>
                <th>Total Remaining</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($summarybydepartment as $department => $items)
                <tr>
                    <td>{{ $items->first()->department?->name ?? 'Unknown' }}</td>
                    <td class="text-blue-500">{{ $items->first()->currency?->name }} {{ number_format($items->sum('total'), 2) }}</td>
                    <td class="text-red-500">{{ $items->first()->currency?->name }} {{ number_format($items->sum('utilized'), 2) }}</td>
                    <td class="text-green-500">{{ $items->first()->currency?->name }} {{ number_format($items->sum('remaining'), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
   </x-card>
</div>
