<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Payment Receipt</title>
    <style>
        h4 {
            margin: 0;
        }
        .w-full {
            width: 100%;
        }
        .w-half {
            width: 50%;
        }
        .margin-top {
            margin-top: 1.25rem;
        }
        .footer {
            font-size: 0.875rem;
            padding: 1rem;
            background-color: rgb(241 245 249);
        }
        table {
            width: 100%;
            border-spacing: 0;
        }
        table.details {
            font-size: 0.875rem;
        }
        table.details tr {
            border-bottom: 1px solid #ddd;
        }
        table.details td {
            padding: 0.5rem;
        }
        table.details td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .receipt-box {
            background-color: rgb(239 246 255);
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: 0.5rem;
            border: 2px solid rgb(96 165 250);
        }
        .amount-box {
            background-color: rgb(220 252 231);
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0.5rem;
            text-align: center;
        }
        .amount-box h2 {
            margin: 0;
            color: rgb(22 163 74);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PAYMENT RECEIPT</h1>
        <p>Procurement Regulatory Authority Of Zimbabwe</p>
    </div>

    <div class="receipt-box">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <div><h4>Receipt Number:</h4></div>
                    <div><strong>{{ $receipt->receiptnumber ?? 'N/A' }}</strong></div>
                </td>
                <td class="w-half">
                    <div><h4>Date:</h4></div>
                    <div><strong>{{ $receipt->created_at->format('d M Y') }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="margin-top">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <div><h4>Customer Details:</h4></div>
                    <div>{{ $customer->name ?? 'N/A' }}</div>
                    <div>Registration: {{ $customer->regnumber ?? 'N/A' }}</div>
                    @if($customer->email)
                    <div>Email: {{ $customer->email }}</div>
                    @endif
                </td>
                <td class="w-half">
                    <div><h4>Invoice Details:</h4></div>
                    <div>Invoice Number: {{ $invoice->invoicenumber }}</div>
                    <div>Invoice Date: {{ $invoice->created_at->format('d M Y') }}</div>
                    <div>Item: {{ $inventoryitem->name ?? 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="amount-box">
        <h2>Amount Paid: {{ $currency->name ?? '' }} {{ number_format($receipt->amount, 2) }}</h2>
    </div>

    <div class="margin-top">
        <table class="details">
            <tr>
                <td>Payment Status:</td>
                <td><strong>{{ $invoice->status }}</strong></td>
            </tr>
            <tr>
                <td>Payment Method:</td>
                <td>
                    @if($receipt->suspense && $receipt->suspense->onlinepayment)
                        Online Payment
                    @elseif($receipt->suspense && $receipt->suspense->banktransaction)
                        Bank Transfer
                    @elseif($receipt->suspense && $receipt->suspense->wallettopup)
                        Wallet
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            @if($receipt->suspense && $receipt->suspense->banktransaction)
            <tr>
                <td>Transaction Reference:</td>
                <td>{{ $receipt->suspense->banktransaction->sourcereference ?? 'N/A' }}</td>
            </tr>
            @endif
            @if($invoice->settled_at)
            <tr>
                <td>Settlement Date:</td>
                <td>{{ $invoice->settled_at->format('d M Y') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="footer margin-top">
        <div>This is a computer-generated receipt. No signature required.</div>
        <div>&copy; Procurement Regulatory Authority Of Zimbabwe</div>
        <div>Generated on {{ \Carbon\Carbon::now()->format('d M Y H:i') }}</div>
    </div>
</body>
</html>

