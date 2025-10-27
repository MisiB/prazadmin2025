<div>
<x-breadcrumbs :items="$breadcrumbs"     class="bg-base-300 p-3 mt-2 rounded-box"    link-item-class="text-sm font-bold" />
<x-card class="mt-5 border-2 border-gray-200">
    <x-tabs wire:model="selectedTab">
        <x-tab name="latest-tab" label="Latest transactions" icon="o-currency-dollar">
            <livewire:admin.finance.latesttransactions :latesttransactions="$latesttransactions" />
        </x-tab>
        <x-tab name="search-tab" label="Search transactions" icon="o-magnifying-glass-circle">
            <livewire:admin.finance.searchtransactions />
        </x-tab>
        <x-tab name="report-tab" label="Transaction report" icon="o-chart-pie">
            <livewire:admin.finance.transactionreport />
        </x-tab>
        <x-tab name="reconcile-tab" label="Bank Reconciliation" icon="o-chart-pie">
          <livewire:admin.finance.components.bankrecouncilations />
        </x-tab>
     </x-tabs>
 </x-card>
</div>
 