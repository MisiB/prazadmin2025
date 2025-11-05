<div>
    <x-button icon="o-eye" class="btn-ghost btn-sm" wire:click="getorder" />

<x-modal wire:model="showViewOrderModal" title="View Order" box-class="h-screen w-screen max-w-full m-0 rounded-none">
  
    <x-card title="Order Details" separator>
        <table class="table table-zebra table-pin-cols">
           <thead>
            <tr>
                <th>Name</th>
                <th>Cost</th>
                <th>Delegates</th>
                <th>Status</th>
                <th>Attachement</th>
            </tr>
           </thead>
           <tbody>
            @if($order)
            <tr>
                <td>
                    <div>{{ $order->name }}</div>
                    <div>{{ $order->surname }}</div>
                    <div>{{ $order->customer->name }}</div>
                    <div>{{ $order->email }}</div>
                    <div>{{ $order->phone }}</div>
                </td>
                <td>
                    <div>{{  $order->currency->name }} {{ $order->amount }}</div>
                </td>
                <td>
                    <div>{{ $order->delegates }}</div>
                </td>
                <td>
                    <div>{{ $order->status }}</div>
                </td>
                <td>
                    @if($order->documenturl)
                    <div>
                        <x-button icon="o-eye" class="btn-ghost btn-sm" wire:click="viewdocument" />
                    </div>
                    @else
                    <div>No attachement</div>
                    @endif

                </td>
                </tr>
                @else
                <tr>
                    <td colspan="10" class="text-center">No order found</td>
                </tr>
                @endif
           </tbody>
        </table>
    </x-card>
    <x-card title="Delegates Details" separator>
        <x-slot:menu>
            @if($order?->status == 'PAID')
                @if($order?->delegates > count($order?->delegatelist))
                <livewire:components.workshoporder.newdelegate :workshoporder_id="$order->id" />
                @endif
            @endif
        </x-slot:menu>
        <table class="table table-zebra table-pin-cols">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Surname</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Designation</th>
                    <th>National ID</th>
                    <th>Title</th>
                    <th>Gender</th>
                    <th>Type</th>
                    <th>Company</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($order?->delegatelist ?? [] as $delegate)
                <tr>
                    <td>{{ $delegate->name }}</td>
                    <td>{{ $delegate->surname }}</td>
                    <td>{{ $delegate->email }}</td>
                    <td>{{ $delegate->phone }}</td>
                    <td>{{ $delegate->designation }}</td>
                    <td>{{ $delegate->national_id }}</td>
                    <td>{{ $delegate->title }}</td>
                    <td>{{ $delegate->gender }}</td>
                    <td>{{ $delegate->type }}</td>
                    <td>{{ $delegate->company }}</td>
                    <td>
                        <livewire:components.workshoporder.editelegate :delegate="$delegate" />
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center">No delegates found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
    <x-slot:actions>
        @if($order?->status == 'PAID')
        <x-button label="Attach Document" class="btn-primary btn-sm" wire:click="$set('showAttachDocumentModal', true)" />
        @endif
        <x-button label="Download Order" class="btn-primary btn-sm" wire:click="downloadorder"  spinner="downloadorder"/>
    </x-slot:actions>
</x-modal>

<x-modal wire:model="showAttachDocumentModal" title="Attach Document">
    <x-form wire:submit.prevent="saveDocument">
        <x-input label="Document" type="file" wire:model="document" />
    </x-form>
    <x-slot:actions>
        <x-button label="Attach" class="btn-primary btn-sm" wire:click="attachdocument" />
    </x-slot:actions>
</x-modal>

<x-modal wire:model="showViewDocumentModal" title="View Document">
    <div class="w-full h-screen overflow-hidden">
        @if($currentdocument)
        <iframe src="{{ $currentdocument }}" class="w-full h-full" frameborder="0"></iframe>
        @else
        <div class="text-center text-red-500">Document not found</div>
        @endif
    </div>
</x-modal>
</div>