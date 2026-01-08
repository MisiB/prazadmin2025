<div>
    <x-card :title="$accounumber" class="border-2 border-gray-300 mt-2" separator>
        <div class="grid grid-cols-2 gap-5">
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td class="text-success font-semibold">Claimed</td>
                        <td class="text-right">{{ number_format($totalclaimed, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-warning font-semibold">Pending</td>
                        <td class="text-right">{{ number_format($totalpending, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-error font-semibold">Blocked</td>
                        <td class="text-right">{{ number_format($totalblocked, 2) }}</td>
                    </tr>
                    <tr class="border-t">
                        <td class="font-bold">Total</td>
                        <td class="text-right font-bold">{{ number_format($totalclaimed + $totalpending + $totalblocked, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            @php
                                $total = $totalclaimed + $totalpending + $totalblocked;
                                $percentage = $total > 0 ? number_format(($totalpending / $total) * 100, 2) : 0;
                            @endphp
                            <span class="{{ $percentage > 50 ? 'text-red-500' : 'text-gray-500' }} text-xs">
                                {{ $percentage }}% pending/unclaimed
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <x-chart wire:model="myChart" />
        </div>
    </x-card>
</div>
