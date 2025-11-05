<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Workshop Invoice</title>
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
        table.products {
            font-size: 0.875rem;
        }
        table.products tr {
            background-color: rgb(96 165 250);
        }
        table.products th {
            color: #ffffff;
            padding: 0.5rem;
            text-align: left;
        }
        table tr.items {
            background-color: rgb(241 245 249);
        }
        table tr.items td {
            padding: 0.5rem;
        }
        .total {
            text-align: right;
            margin-top: 1rem;
            font-size: 0.875rem;
        }
        .workshop-details {
            background-color: rgb(239 246 255);
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
    <table class="w-full">
        <tr>
            <td class="w-half">
                <img src="./img/praz_logo.png" alt="PRAZ Logo" width="100" />
            </td>
            <td class="w-half">
                <h2>Workshop Invoice</h2>
                <h5>Invoice number: <br/> {{ $data["invoice"]["InvoiceNumber"] ?? $data["ordernumber"] }}</h5>
                <h5>Order number: <br/> {{ $data["ordernumber"] }}</h5>
            </td>
        </tr>
    </table>
 
    <div class="margin-top">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <div><h4>To:</h4></div>
                    <div>{{ $data["account"]["name"] }}</div>
                    <div>{{ $data["account"]["regnumber"] }}</div>
                    <div>{{ $data["account"]["type"] }}</div>
                </td>
                <td class="w-half">
                    <div><h4>From:</h4></div>
                    <div>Procurement Regulatory Authority Of Zimbabwe</div>
                    <div>Workshop Registration</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="workshop-details">
        <h4>Workshop Information</h4>
        <div><strong>Title:</strong> {{ $data["workshop"]["title"] }}</div>
        {{-- <div><strong>Start Date:</strong> {{ isset($data["workshop"]["StartDate"]) ? \Carbon\Carbon::parse($data["workshop"]["StartDate"])->format('d M Y') : 'TBA' }}</div>
        <div><strong>End Date:</strong> {{ isset($data["workshop"]["EndDate"]) ? \Carbon\Carbon::parse($data["workshop"]["EndDate"])->format('d M Y') : 'TBA' }}</div> --}}
        <div><strong>Location:</strong> {{ $data["workshop"]["location"] ?? 'TBA' }}</div>
        <div><strong>Target:</strong> {{ $data["workshop"]["target"] ?? 'General' }}</div>
    </div>

    <div class="margin-top">
        <table class="products">
            <tr>
                <th>Contact Person</th>
                <th>Email</th>
                <th>Delegates</th>
                <th>Amount</th>
            </tr>
            <tr class="items">
                <td>{{ $data["name"] }} {{ $data["surname"] }}</td>
                <td>{{ $data["email"] }}</td>
                <td>{{ $data["delegates"] }}</td>
                <td>{{ $data["currency"]["name"] }} {{ number_format($data["amount"], 2) }}</td>
            </tr>
        </table>
    </div>
 
    <div class="total">
        <strong>Total: {{ $data["currency"]["name"] }} {{ number_format($data["amount"], 2) }}</strong>
    </div>

    <div class="margin-top">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <div><h4>Invoice Details:</h4></div>
                    <div><strong>Created:</strong> {{ \Carbon\Carbon::parse($data["created_at"])->format('d M Y H:i') }}</div>
                    <div><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($data["created_at"])->addDays(30)->format('d M Y') }}</div>
                </td>
                <td class="w-half">
                    <div><h4>Cost Breakdown:</h4></div>
                    <div><strong>Cost per Delegate:</strong> {{ $data["workshop"]["currency"]["name"] }} {{ number_format($data["workshop"]["cost"], 2) }}</div>
                    <div><strong>Number of Delegates:</strong> {{ $data["delegates"] }}</div>
                    <div><strong>Total Amount:</strong> {{ $data["currency"]["name"] }} {{ number_format($data["amount"], 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="margin-top">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <div><h4>USD Bank Details (Non Refunndable):</h4></div>
                    <div><strong>Bank Name: </strong>CBZ BANK LIMITED</div>
                    <div><strong>Branch Code: </strong>6101</div>
                    <div><strong>Swift Code: </strong>COBZZWHA</div>
                    <div><strong>Account Number: </strong>10721064850108</div>
                </td>

                <td class="w-half">
                    <div><h4>ZWG Bank Details (Non Refunndable):</h4></div>
                    <div><strong>Bank Name: </strong>CBZ BANK LIMITED</div>
                    <div><strong>Branch Code: </strong>6101</div>
                    <div><strong>Swift Code: </strong>COBZZWHA</div>
                    <div><strong>Account Number: </strong>10721064850020</div>
                </td>
            </tr>
        </table>
    </div>

 
    <div class="footer margin-top">
        <div>Thank you for choosing PRAZ workshops</div>
        <div>&copy; Procurement Regulatory Authority Of Zimbabwe</div>
        <div>Generated on {{ \Carbon\Carbon::now()->format('d M Y') }}</div>
    </div>
</body>
</html>