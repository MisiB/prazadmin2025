<div>
    <x-breadcrumbs :items="$breadcrumbs"     class="bg-base-300 p-3 mt-2 rounded-box"    link-item-class="text-sm font-bold" />
    <x-card title="Budget Configurations" separator class="mt-5 border-2 border-gray-200">
        <x-tabs wire:model="selectedTab">
            <x-tab name="expensecategory-tab" label="Expense Categories" icon="o-cog-6-tooth">
                <livewire:admin.finance.budgetconfigurations.components.expensecategories />
            </x-tab>
            <x-tab name="sourceoffund-tab" label="Source of funds" icon="o-cog-6-tooth">
                <livewire:admin.finance.budgetconfigurations.components.sourceoffunds />
            </x-tab>
        </x-tabs>
        
    </x-card>
</div>
