<div>
    <x-card>
        <x-slot name="title">Suspense Reports</x-slot>
        <x-slot name="menu">
            <div class="flex items-center gap-4">
                <x-input icon="o-magnifying-glass"
                         wire:model.live="search"
                         placeholder="Search Suspense..."
                         class="max-w-sm" />
            </div>
        </x-slot>

        <x-card title="Suspense Data" separator>
            <x-table :headers="$headers" :rows="$rows" pagination>
                @scope('cell_amount', $row)
                    <span class="font-bold text-green-600">
                        {{ $row->currency }} {{ number_format($row->amount, 2) }}
                    </span>
                    <span class="text-gray-400">/</span>
                    <span class="font-bold text-red-500">
                        {{ $row->currency }} {{ number_format($row->total_utilized, 2) }}
                    </span>
                @endscope

                @scope('cell_balance', $row)
                    <span class="font-bold text-blue-600">
                        {{ $row->currency }} {{ number_format($row->balance, 2) }}
                    </span>
                @endscope

                <x-slot:empty>
                    <x-alert class="alert-error" title="No Suspense found." />
                </x-slot:empty>
            </x-table>

            <div class="mt-4">
                {{ $rows->links() }}
            </div>
        </x-card>
    </x-card>
</div>