<div>
<x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    <x-card class="mt-5 border-2 border-gray-200">
        <div>  <x-slot:menu>
            <x-button label="Import Tenders" @click="$wire.importdrawer = true" responsive icon="o-arrow-up-tray" class="btn-success"/>
        </x-slot:menu></div>
        <x-tabs wire:model="selectedTab">
            <x-tab name="tenders-tab" label="Tender list" icon="o-currency-dollar">
                <livewire:admin.procurements.components.tenderlist />
            </x-tab>
            <x-tab name="createtender-tab" label="Create Tenders" icon="o-plus">
                <livewire:admin.procurements.components.createtender />
            </x-tab>
        </x-tabs>
    </x-card>
</div>
