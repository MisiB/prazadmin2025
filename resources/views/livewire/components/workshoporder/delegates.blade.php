<div>
    <x-card title="Delegates" separator>
        <x-slot:menu>
            <x-button icon="o-plus" label="Export Delegates" wire:click="exportdelegates" spinner="exportdelegates" class="btn-success" />
        </x-slot:menu>
        <x-table :headers="$headers" :rows="$delegates">
            @scope('cell_name', $row)
                <div>Name{{ $row->name }} {{ $row->surname }}</div>
                <div>National ID: {{ $row->national_id }}</div>
                <div>Title: {{ $row->title }}</div>
                <div>Gender: {{ $row->gender }}</div>
                <div>Type: {{ $row->type }}</div>
                <div>Company: {{ $row->company }}</div>
            @endscope
            @scope('cell_actions', $row)
                <div class="flex items-center space-x-2">
                    <livewire:components.workshoporder.editelegate :delegate="$row" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
