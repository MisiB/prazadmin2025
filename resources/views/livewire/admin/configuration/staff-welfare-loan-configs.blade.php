<div>
    <x-breadcrumbs :items="$breadcrumbs"
        class="bg-base-300 p-3 mt-2 rounded-box"
        link-item-class="text-sm font-bold" />

    <x-card title="Staff Welfare Loan Configuration" separator class="mt-5 border-2 border-gray-200">
        <x-slot:menu>
            <x-badge value="{{ $id ? 'Configuration Active' : 'No Configuration' }}" 
                class="{{ $id ? 'badge-success' : 'badge-warning' }}" />
        </x-slot:menu>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Configuration Form -->
            <div class="space-y-4">
                <h3 class="font-bold text-lg border-b pb-2">Loan Settings</h3>
                
                <x-input 
                    label="Annual Interest Rate (%)" 
                    wire:model.live="interest_rate" 
                    type="number" 
                    step="0.01" 
                    min="0" 
                    max="100"
                    hint="Interest rate applied to all new loans"
                    suffix="%"
                    :readonly="!auth()->user()->can('swl.config.manage')" />

                <x-input 
                    label="Maximum Repayment Period (Months)" 
                    wire:model="max_repayment_months" 
                    type="number" 
                    min="1" 
                    max="120"
                    hint="Maximum number of months allowed for loan repayment"
                    :readonly="!auth()->user()->can('swl.config.manage')" />

                <x-input 
                    label="Minimum Loan Amount (USD)" 
                    wire:model="min_loan_amount" 
                    type="number" 
                    step="0.01" 
                    min="0"
                    hint="Minimum amount that can be borrowed" 
                    prefix="$"
                    :readonly="!auth()->user()->can('swl.config.manage')" />

                <x-input 
                    label="Maximum Loan Amount (USD)" 
                    wire:model="max_loan_amount" 
                    type="number" 
                    step="0.01" 
                    min="0"
                    hint="Leave empty for no maximum limit" 
                    prefix="$"
                    :readonly="!auth()->user()->can('swl.config.manage')" />

                <x-textarea 
                    label="Notes" 
                    wire:model="notes" 
                    rows="3"
                    hint="Additional notes or policy information"
                    :readonly="!auth()->user()->can('swl.config.manage')" />

                @can('swl.config.manage')
                    <div class="pt-4">
                        <x-button label="Save Configuration" type="button" wire:click="save" class="btn-primary" icon="o-check" spinner="save" />
                    </div>
                @else
                    <div class="pt-4">
                        <x-alert title="View Only" icon="o-lock-closed" class="alert-warning">
                            <p class="text-sm">You do not have permission to modify loan configuration settings.</p>
                        </x-alert>
                    </div>
                @endcan
            </div>

            <!-- Preview Calculator -->
            <div class="space-y-4">
                <h3 class="font-bold text-lg border-b pb-2">Repayment Calculator Preview</h3>
                
                <div class="bg-base-200 p-4 rounded-lg space-y-4" x-data="{ 
                    previewPrincipal: 1000, 
                    previewMonths: 12,
                    getRate() {
                        return parseFloat($wire.interest_rate) || 0;
                    },
                    get calculation() {
                        const principal = parseFloat(this.previewPrincipal) || 0;
                        const months = parseInt(this.previewMonths) || 1;
                        const rate = this.getRate();
                        const timeInYears = months / 12;
                        const interest = principal * (rate / 100) * timeInYears;
                        const total = principal + interest;
                        const monthly = months > 0 ? total / months : 0;
                        return {
                            rate: rate.toFixed(2),
                            principal: principal.toFixed(2),
                            interest: interest.toFixed(2),
                            total: total.toFixed(2),
                            monthly: monthly.toFixed(2)
                        };
                    }
                }">
                    <x-input 
                        label="Loan Amount (USD)" 
                        x-model="previewPrincipal" 
                        type="number" 
                        step="0.01" 
                        min="0"
                        prefix="$" />

                    <x-input 
                        label="Repayment Period (Months)" 
                        x-model="previewMonths" 
                        type="number" 
                        min="1" 
                        max="120" />

                    <div class="divider">Calculation Results</div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="stat bg-base-100 rounded-lg p-3">
                            <div class="stat-title text-xs">Principal</div>
                            <div class="stat-value text-lg text-primary">$<span x-text="calculation.principal"></span></div>
                        </div>
                        <div class="stat bg-base-100 rounded-lg p-3">
                            <div class="stat-title text-xs">Interest (<span x-text="calculation.rate"></span>%)</div>
                            <div class="stat-value text-lg text-warning">$<span x-text="calculation.interest"></span></div>
                        </div>
                        <div class="stat bg-base-100 rounded-lg p-3">
                            <div class="stat-title text-xs">Total Repayment</div>
                            <div class="stat-value text-lg text-success">$<span x-text="calculation.total"></span></div>
                        </div>
                        <div class="stat bg-base-100 rounded-lg p-3">
                            <div class="stat-title text-xs">Monthly Deduction</div>
                            <div class="stat-value text-lg text-info">$<span x-text="calculation.monthly"></span></div>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <x-alert title="How Interest is Calculated" icon="o-information-circle" class="alert-info">
                    <p class="text-sm">
                        Simple Interest Formula:<br>
                        <code>Interest = Principal × Rate × Time (in years)</code><br><br>
                        The monthly deduction is calculated as:<br>
                        <code>Monthly = (Principal + Interest) ÷ Months</code>
                    </p>
                </x-alert>

                @if($id)
                    <x-alert title="Configuration Active" icon="o-check-circle" class="alert-success">
                        <p class="text-sm">
                            This configuration is currently active and will be applied to all new loans during HR data capture.
                            The salary will be hashed for privacy, and existing loan balance will be automatically calculated.
                        </p>
                    </x-alert>
                @endif
            </div>
        </div>
    </x-card>
</div>

