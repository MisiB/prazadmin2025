<div>
    <x-breadcrumbs :items="$breadcrumbs" 
        class="bg-base-300 p-3 mt-2 rounded-box"
        link-item-class="text-sm font-bold" />
    <x-card title="Reversed Transactions" separator class="mt-5 border-2 border-gray-200">
        <x-table :rows="$reversedTransactions" :headers="$headers" with-pagination>
            @scope('cell_invoice_number', $row)
                {{ $row->invoice_number }}
            @endscope
            @scope('cell_receipt_number', $row)
                {{ $row->receipt_number }}
            @endscope
            @scope('cell_amount', $row)
                @php
                    $currency = $row->suspense->currency ?? '';
                    $amount = is_numeric($row->amount) ? number_format((float)$row->amount, 2) : $row->amount;
                @endphp
                <span class="font-bold text-red-500">{{ $currency }}{{ $amount }}</span>
            @endscope
            @scope('cell_reversed_at', $row)
                {{ $row->reversed_at->format('Y-m-d H:i:s') }}
            @endscope
            @scope('cell_reversed_by', $row)
                {{ $row->reversedBy->name ?? 'N/A' }}
            @endscope

            <x-slot:empty>
                <x-alert class="alert-info" title="No reversed transactions found." />
            </x-slot:empty>
        </x-table>
    </x-card>
</div>
