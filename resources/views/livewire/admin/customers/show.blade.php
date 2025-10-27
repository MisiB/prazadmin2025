<div>
    <x-breadcrumbs :items="$breadcrumbs" 
    class="bg-base-300 p-3 mt-2 rounded-box"
    link-item-class="text-sm font-bold" />
    
    <!-- Wallet Balances Section -->
    <x-card title="Wallet Balances" separator class="mt-5 border-2 border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($walletBalances as $key => $wallet)
            <div class="p-4 rounded-lg border-2 text-center border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                <x-icon name="o-currency-dollar" class="w-12 h-12 bg-green-700 text-white p-2 rounded-full mb-2" />
                <div class="text-sm">{{ $wallet['type'] }}</div>
                <div class="text-xl font-bold mt-2">{{ $wallet['currency'] }} {{$wallet['balance'] }}</div>
            </div>
            @endforeach
        </div>
    </x-card>
    
    <!-- Customer Details Section -->
    <x-card  class="mt-5 border border-green-200">
       <div class="grid grid-cols-4 gap-2">
        <a href="{{ route('admin.customers.showinvoices', $customer->id) }}" class="p-3 rounded-lg border-2 text-center border-green-200 hover:bg-green-300 cursor-pointer">
            <x-icon name="o-banknotes" class="w-12 h-12 bg-blue-700 text-white p-2 rounded-full" />
            <div >Invoices</div>
        </a>
        <a href="{{ route('admin.customers.showbanktransactions', $customer->id) }}" class="p-3 rounded-lg border-2 text-center border-green-200 hover:bg-green-300 cursor-pointer">
            <x-icon name="o-building-library" class="w-12 h-12 bg-blue-700 text-white p-2 rounded-full" />
            <div >Bank Transactions</div>
        </a>
         <a href="{{ route('admin.customers.showepayments', $customer->id) }}" class="p-3 rounded-lg border-2 text-center border-green-200 hover:bg-green-300 cursor-pointer">
            <x-icon name="o-envelope" class="w-12 h-12 bg-blue-700 text-white p-2 rounded-full" />
            <div >ePayments</div>
        </a>
        <a href="{{ route('admin.customers.showonlinepayments', $customer->id) }}" class="p-3 rounded-lg border-2 text-center border-green-200 hover:bg-green-300 cursor-pointer">
            <x-icon name="o-credit-card" class="w-12 h-12 bg-blue-700 text-white p-2 rounded-full" />
            <div >Online Payments</div>
        </a>
        <a href="{{ route('admin.customers.showwallettopups', $customer->id) }}" class="p-3 rounded-lg border-2 text-center border-green-200 hover:bg-green-300 cursor-pointer">
            <x-icon name="o-wallet" class="w-12 h-12 bg-blue-700 text-white p-2 rounded-full" />
            <div >Wallet Topups</div>
        </a>
        <a href="{{ route('admin.customers.showsuspensestatement', $customer->id) }}" class="p-3 rounded-lg border-2 text-center border-green-200 hover:bg-green-300 cursor-pointer">
            <x-icon name="o-squares-plus" class="w-12 h-12 bg-blue-700 text-white p-2 rounded-full" />
            <div >Suspense Statement</div>
        </a>
         
         
       </div> 
    </x-card>
</div>
