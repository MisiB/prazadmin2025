<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    <x-card class="mt-5 border-2 border-gray-200">
    <x-tabs wire:model="selectedTab">
        <x-tab name="currency-tab" label="Currency management" icon="o-currency-dollar">
          <livewire:admin.finance.currencies />
        </x-tab>
        <x-tab name="banks-tab" label="Banks management" icon="o-building-library">
            <livewire:admin.finance.banks />
        </x-tab>
        <x-tab name="paynow-tab" label="Paynow config" icon="o-credit-card">
            <livewire:admin.finance.paynowconfig />
        </x-tab>
        <x-tab name="inventoryitem-tab" label="Inventory items" icon="o-archive-box-arrow-down">
            <livewire:admin.finance.inventoryitems />
        </x-tab>
        <x-tab name="exchangerate-tab" label="Exchange rate" icon="o-credit-card">
            <livewire:admin.finance.exchangerate />
        </x-tab>
    </x-tabs>
    </x-card>
</div>
 