<div>
    @if($latesttransactions->count()>0)
    @php
      $groupbyaccount = $latesttransactions->groupBy('accountnumber');
    @endphp
    <div class="overflow-x-auto">
        <table class="table table-striped">
            <thead>
                <tr>   
                    <th>Account number</th>
                    <th>Amount</th>
                    <th>Last update</th>
                    </tr>
            </thead>
            <tbody>
                @foreach($groupbyaccount as $accountnumber => $transactions)
                <tr>
                    <td>{{ $accountnumber }}</td>
                    <td>{{$transactions->first()->currency}} {{ $transactions->sum('amount') }}</td>
                    <td>{{ $transactions->last()->created_at->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-warning">
        No transactions found
    </div>
    @endif
    
</div>
