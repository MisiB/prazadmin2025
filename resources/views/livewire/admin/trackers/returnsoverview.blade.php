<div class="relative">

    <!-- Ambient Gradient Backgrounds with enhanced floating and rotation -->
    <div class="pointer-events-none absolute -top-32 -right-32 h-96 w-96 rounded-full bg-gradient-to-br from-indigo-300/30 via-cyan-300/20 to-teal-300/30 blur-3xl animate-float-rotate"></div>
    <div class="pointer-events-none absolute top-1/2 -left-32 h-96 w-96 rounded-full bg-gradient-to-tr from-violet-300/30 via-indigo-300/20 to-sky-300/30 blur-3xl animate-float-rotate-reverse"></div>

    <!-- Breadcrumbs -->
    <nav class="relative flex items-center text-sm text-slate-400 animate-fadeIn animate-delay-100 mt-[20px] sticky top-5">
        <a href="{{ route('admin.trackers.returnsoverview') }}" class="hover:text-indigo-600 transition">Home</a>
        <span class="mx-2">/</span>
        <span class="font-medium text-slate-600">Returns overview</span>
    </nav>

    <!-- Page Header -->
    <div class="relative animate-fadeIn animate-delay-200 my-10">
        <h1 class="text-3xl font-semibold tracking-tight text-slate-900 animate-slideUp">Monthly Returns Overview</h1>
        <p class="mt-1 max-w-2xl text-slate-600 animate-slideUp animate-delay-100">
            Monitor monthly procurement return records in style transparently.
        </p>
    </div>
 
    @if($currentschool)
        <div class="w-full grid justify-items-start md:justify-items-end">
            <div>
                <x-button icon="o-document-arrow-up" 
                    onclick="window.print()" 
                    class="mb-4 rounded-xl bg-amber-300 
                        px-3 py-2 text-sm font-medium 
                        text-white hover:from-amber-300 hover:to-amber-400 
                        transition hover:scale-[1.05] fixed top-50 right-45 z-90"
                />
                <x-button icon="o-arrow-up-on-square" 
                    wire:click="exportexcelreport" 
                    class="mb-4 rounded-xl bg-blue-600 
                        px-3 py-2 text-sm font-medium 
                        text-white hover:from-indigo-400 hover:to-violet-400 
                        transition hover:scale-[1.05] fixed top-50 right-30 z-90"
                />
                <x-button icon="o-x-mark" 
                    wire:click="backtoschoolsearch" 
                    class="mb-4 rounded-xl bg-gray-600  opacity-40
                        px-3 py-2 text-sm font-medium 
                        text-white hover:from-indigo-400 hover:to-violet-400 
                        transition hover:scale-[1.05] fixed top-50 right-15 z-90"
                />
            </div>
        </div>
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 animate-fadeIn animate-delay-400">
            <div class="rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white shadow-lg transform transition-transform duration-700 hover:scale-[1.07] hover:shadow-2xl hover:shadow-indigo-300 animate-cardPop">
                <p class="text-lg font-medium">Pending Returns Due</p>
                <p class="mt-2 text-4xl font-bold">{{ $totalpendingreturns ?? 0 }}</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 p-6 text-white shadow-lg transform transition-transform duration-700 hover:scale-[1.07] hover:shadow-2xl hover:shadow-emerald-300 animate-cardPop animate-delay-100">
                    <p class="text-lg font-medium">Pending Expenditure</p>
                    @forelse ($totalpendingexpenditure as $total)
                            <p class="mt-2 text-2xl font-bold">{{$total["currency"]}} {{ number_format($total["value"] ?? 0, 2) }}</p>
                    @empty
                        <p class="mt-2 text-2xl font-bold">{{ number_format($totalexpenditure ?? 0, 2) }}</p>
                    @endforelse</div>
            <div class="rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white shadow-lg transform transition-transform duration-700 hover:scale-[1.07] hover:shadow-2xl hover:shadow-indigo-300 animate-cardPop">
                <p class="text-lg font-medium">Approved Returns Total</p>
                <p class="mt-2 text-4xl font-bold">{{ $totalapprovedreturns ?? 0 }}</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-green-500 to-green-600 p-6 text-white shadow-lg transform transition-transform duration-700 hover:scale-[1.07] hover:shadow-2xl hover:shadow-pink-300 animate-cardPop animate-delay-200">
                <p class="text-lg font-medium">Approved Expenditure</p>
                    @forelse ($totalapprovedexpenditure as $total)
                            <p class="mt-2 text-2xl font-bold">{{$total["currency"]}} {{ number_format($total["value"] ?? 0, 2) }}</p>
                    @empty
                        <p class="mt-2 text-2xl font-bold">{{ number_format($totalexpenditure ?? 0, 2) }}</p>
                    @endforelse
            </div>
        </div>

        <!--School Monthly Returns Details-->
        
        <x-card class="relative overflow-hidden rounded-3xl 
            border border-white/40 bg-white/70 backdrop-blur-xl 
            shadow-lg shadow-indigo-100/40 animate-fadeIn animate-delay-300 
            transform transition-transform 
            duration-700 hover:scale-[0.98] hover:shadow-2xl hover:shadow-indigo-200
            rounded-3xl border border-slate-200 bg-white shadow-sm animate-fadeIn animate-delay-700
            mt-20" 
            title="Returns for, {{ $currentschool->name }}" 
            subtitle="School ID: {{ $currentschool->school_number }}" separator >

            <!-- Filters / Actions -->
            <x-slot:menu>
                <div class="flex flex-wrap items-center gap-3">
                    <x-input placeholder="Year" wire:model.live="year" class="w-28"/>
                    <x-select placeholder="Month" :options="$monthlist" wire:model.live="month" class="w-40"/>
                </div>
            </x-slot:menu>

            <!-- Returns Table -->
            <x-table  wire:loading.remove :headers="$headers" :rows="$monthlyreturns" separator show-empty-text empty-text="No procurement records found">
                @scope('cell_data', $row)
                    <!-- View Expenditure Button -->
                    <x-button icon="o-eye"
                        wire:click="openviewexpendituremodal({{$row->id}})" 
                        class="mb-4 rounded-xl bg-gradient-to-r from-indigo-500 
                            to-violet-500 px-3 py-2 text-sm font-medium 
                            text-white hover:from-indigo-400 hover:to-violet-400 
                            transition hover:scale-[1.05]"
                    />
                @endscope
                          
                <x-slot:empty>
                    <x-alert class="alert-error" title="No monthly returns found" />
                </x-slot:empty>
            </x-table>

        </x-card> 
        
    @else
        <!-- Search -->
        <x-card class="rounded-3xl border border-white/40 bg-white/60 backdrop-blur-xl shadow-md shadow-slate-200/50 animate-fadeIn animate-delay-500 hover:scale-[1.02] transition-transform duration-500">
            <x-form wire:submit="searchschool" wire:loading.remove>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-input placeholder="School Name" wire:model="schoolname" class="focus:ring-2 focus:ring-indigo-400 transition"/>
                    <x-input placeholder="School ID" wire:model="schoolid" class="focus:ring-2 focus:ring-indigo-400 transition"/>
                    <!--<button type="submit" class="rounded-xl bg-gradient-to-r from-slate-900 to-slate-700 px-4 py-2.5 text-sm font-medium text-white hover:to-slate-600 transition hover:scale-[1.05] hover:shadow-lg">Search</button>-->
                    <button type="submit"
                        class="relative overflow-hidden rounded-xl bg-gradient-to-r from-indigo-600 via-violet-600 to-cyan-600 px-5 py-2.5 text-sm font-medium text-white shadow-lg shadow-indigo-300/50 transition-transform duration-500 hover:scale-105 hover:shadow-2xl focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2">
                        <span class="relative z-10">Search</span>
                        <!-- Shine animation -->
                        <span class="absolute inset-0 -translate-x-full bg-gradient-to-r from-white/50 via-white/30 to-white/0 transform skew-x-[-20deg] transition-transform duration-700 group-hover:translate-x-full"></span>
                    </button>
                </div>
            </x-form>
        </x-card>
    @endif

    <div class="grid justify-items-center mt-30">
        <div wire:loading>          
            <div class="pl-5">
                <svg class="w-5 h-5 animate-spin text-blue-600" viewBox="0 0 30 30">
                    <circle class="opacity-25" cx="15" cy="15" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
            </div>
            <div class="opacity-70 text-gray-700">Loading...</div>
        </div>
    </div>



    <!-- Modals (Livewire controlled) -->
    <x-modal wire:model="modal" title="{{ $this->record ? 'Edit School Return' : 'Add School Return' }}">
        <x-form wire:submit="save">
            <x-input placeholder="Year" wire:model="year"/>
            <x-select placeholder="Month" :options="$monthlist" wire:model="month"/>
            <x-select placeholder="Category" :options="$schoolexpensecategories" wire:model.live="schoolexpensecategory"/>
            @if($this->schoolexpensecategory == 10)
                <x-input placeholder="Specify expense category" wire:model="category"/>
            @endif
            <button class="w-full rounded-xl bg-gradient-to-r from-slate-900 to-slate-700 py-2.5 text-white hover:to-slate-600 transition hover:scale-[1.03]">Save</button>
        </x-form>
    </x-modal>

    <x-modal wire:model="datamodal" title="{{ $this->record ? 'Edit Expenditure' : 'Add Expenditure' }}">
        <x-form wire:submit="savedata">
            <x-select placeholder="Source of fund" :options="$sourceoffunds" wire:model="sourceoffund"/>
            <x-select :options="$currencies" option-value="id" option-label="name" wire:model="currency" placeholder="Select Currency"/>
            <x-input placeholder="Amount" wire:model="amount"/>
            <button class="ml-auto rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-600 px-4 py-2 text-sm font-medium text-white hover:from-indigo-500 hover:to-cyan-500 transition hover:scale-[1.05] w-full">{{ $this->record ? 'Update' : 'Save' }} Expenditure</button>
        </x-form>
    </x-modal>


    @if($currentmonthlyreturn)
    <x-modal wire:model="viewexpendituremodal" title="View of {{$currentmonthlyreturn->month}} {{$currentmonthlyreturn->year}} {{$currentmonthlyreturn->schoolexpensecategory->name}} Expenditure"  box-class="max-w-4xl">
        <div class="overflow-hidden rounded-2xl border border-slate-200 animate-fadeIn animate-delay-100">
            @if($monthlyreturndata)
                @forelse($monthlyreturndata as $returnsdata)
                <x-card class="bg-gray-200  mb-2" title="Expenditure Record #{{$returnsdata->id}}" subtitle="Recorded on {{$returnsdata->created_at->format('d M, Y')}}" separator>
                    <div class="grid grid-flow-cols grid-cols-2">
                        <div>Source of fund: {{$returnsdata->sourceoffund}}</div>
                        <div>Currency name: {{$returnsdata->currency->name}}</div>
                        <div>Amount: {{$returnsdata->amount}}</div>
                    </div>
                </x-card>
                @empty
                    <x-alert title="No returns expenditure" description="No expenditure records found" class="rounded-2xl bg-gradient-to-r from-rose-50 to-pink-50 text-rose-900 border border-rose-200 animate-fadeIn animate-delay-600 shadow-lg"/>
                @endforelse
            @endif
        </div>    
    </x-modal>
    @endif

    <style>
    /* Ambient gradient animations */
    @keyframes floatRotate { 0%,100%{transform:translate(0,0) rotate(0deg);}50%{transform:translate(15px,-15px) rotate(10deg);} }
    @keyframes floatRotateReverse { 0%,100%{transform:translate(0,0) rotate(0deg);}50%{transform:translate(-15px,15px) rotate(-10deg);} }
    @keyframes fadeIn { from{opacity:0; transform:translateY(10px);} to{opacity:1; transform:translateY(0);} }
    @keyframes slideUp { from{transform:translateY(20px);opacity:0;} to{transform:translateY(0);opacity:1;} }
    @keyframes gradientMove { 0%{background-position:0% 50%;}50%{background-position:100% 50%;}100%{background-position:0% 50%;} }
    @keyframes cardPop { 0%{transform:scale(0.95);opacity:0.5;}50%{transform:scale(1.02);}100%{transform:scale(1);opacity:1;} }

    .animate-fadeIn { animation: fadeIn 2.5s ease forwards; }
    .animate-slideUp { animation: slideUp 3.0s ease forwards; }
    .animate-cardPop { animation: cardPop 3.5s ease forwards; }
    .animate-gradientMove { background-size: 200% 200%; animation: gradientMove 8s ease infinite; }
    .animate-float-rotate { animation: floatRotate 8s ease-in-out infinite; }
    .animate-float-rotate-reverse { animation: floatRotateReverse 10s ease-in-out infinite; }
    .animate-delay-100 { animation-delay: 0.1s; }
    .animate-delay-200 { animation-delay: 0.2s; }
    .animate-delay-300 { animation-delay: 0.3s; }
    .animate-delay-400 { animation-delay: 0.4s; }
    .animate-delay-500 { animation-delay: 0.5s; }
    .animate-delay-600 { animation-delay: 0.6s; }
    .animate-delay-700 { animation-delay: 0.7s; }
    </style>
</div>
