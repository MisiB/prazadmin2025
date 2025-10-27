<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    <x-card class="mt-5 border-2 border-gray-200">
    <x-tabs wire:model="selectedTab">
        <x-tab name="revenue-tab" label="Invoices" icon="o-currency-dollar">
         <livewire:admin.finance.reports.invoicereport />
        </x-tab>
        <x-tab name="comparison-tab" label="Comparison" icon="o-building-library">
<livewire:admin.finance.reports.comparisonreports />
        </x-tab>
            <x-tab name="quarterly-tab" label="Quarterly " icon="o-credit-card">
          <livewire:admin.finance.reports.quarterlyreports />
        </x-tab>
        <x-tab name="daily-tab" label="Daily suspense" icon="o-wallet">
        <livewire:admin.finance.reports.suspensereports />
        </x-tab>
       
        <x-tab name="monthly-tab" label="Monthly suspense" icon="o-wallet">
            <livewire:admin.finance.reports.monthlysuspense />
        </x-tab>
    
    </x-tabs>
    </x-card>
</div>