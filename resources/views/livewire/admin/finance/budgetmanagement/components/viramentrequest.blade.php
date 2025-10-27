<div>
    <x-tab name="musics-tab"  icon="o-currency-dollar">
        <x-slot:label>  
            Virament requests
            <x-badge value="{{ $budget->budgetvirements->where('status', 'PENDING')->count() }}" class="badge-error badge-sm" />
        </x-slot:label>
   <x-card title="Virament requests" separator class="mt-5">
    <table class="table table-zebra table-sm">
        <thead>
            <tr>
                <th>From Budget Item</th>
                <th>To Budget Item</th>
                <th>Reason</th>
                <th>Amount</th>
                <th>Created By</th>
                <th>Created At</th>                
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($budget->budgetvirements as $budgetvirement)
            <tr>
                <td>{{ $budgetvirement->from_budgetitem->activity }}</td>
                <td>{{ $budgetvirement->to_budgetitem->activity }}</td>
                <td>{{ $budgetvirement->description }}</td>
                <td>{{ $budgetvirement->amount }}</td>
                <td>{{ $budgetvirement->createdby->name }} {{ $budgetvirement->createdby->surname }}</td>
                <td>{{ $budgetvirement->created_at }}</td>                
                <td>
                    @if($budgetvirement->status == "PENDING")
                        <span class="badge badge-warning badge-sm">{{ $budgetvirement->status }}</span>
                    @elseif($budgetvirement->status == "APPROVED")
                        <span class="badge badge-success badge-sm">{{ $budgetvirement->status }}</span>
                    @else
                        <span class="badge badge-error badge-sm">{{ $budgetvirement->status }}</span>
                    @endif
                </td>
                <td class="flex gap-2">
                    @if ($budgetvirement->status=="PENDING")
                       <x-button class="btn-primary btn-sm" label="Approve" wire:confirm="Are you sure you want to approve this virament request?" wire:click="approve({{ $budgetvirement->id }})"/> 
                       <x-button class="btn-error btn-sm" label="Reject" wire:confirm="Are you sure you want to reject this virament request?" wire:click="openrejectionmodal({{ $budgetvirement->id }})"/>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">No virament requests found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
   </x-card>
    </x-tab>
   <x-modal wire:model="modal" title="Reject Virament Request">
    <x-slot:menu>
        <x-button label="Close" wire:click="closeModal" class="btn-error"/>
    </x-slot:menu>
       <x-form wire:submit="reject">
        <x-textarea label="Comment" wire:model="comment"/>
    
    <x-slot:actions>
        <x-button label="Close" wire:click="closeModal" class="btn-error"/>
        <x-button label="Reject" type="submit" class="btn-primary" spinner="reject"/>
    </x-slot:actions>
</x-form>
</x-modal>
</div>
