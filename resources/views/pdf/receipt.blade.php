<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 2rem;
            background: #f8fafc;
        }

        h1, h2, h4 {
            margin: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.75rem;
            letter-spacing: 1px;
        }

        .section {
            margin-bottom: 1.75rem;
        }

        .box {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
        }

        .highlight-box {
            background: #e0f2fe;
            border-left: 4px solid #0284c7;
        }

        .amount-box {
            background: #dcfce7;
            text-align: center;
            padding: 1rem;
            border-radius: 0.5rem;
        }

        .amount-box h2 {
            color: #15803d;
            margin: 0;
            font-size: 1.5rem;
        }

        table {
            width: 100%;
            border-spacing: 0;
        }

        table.details {
            font-size: 0.9rem;
            border-collapse: collapse;
        }

        table.details tr {
            border-bottom: 1px solid #e5e7eb;
        }

        table.details td {
            padding: 0.6rem 0.4rem;
        }

        table.details td:first-child {
            font-weight: bold;
            width: 40%;
        }

        .footer {
            margin-top: 3rem;
            font-size: 0.85rem;
            text-align: center;
            padding: 1.25rem;
            background: #f1f5f9;
            border-radius: 0.5rem;
            color: #374151;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <h1>PAYMENT RECEIPT</h1>
        <p>Procurement Regulatory Authority of Zimbabwe</p>
    </div>

    <!-- Receipt Summary -->
    <div class="section box highlight-box">
        <table>
            <tr>
                <td>
                    <h4>Receipt Number</h4>
                    <strong>{{ $receipt->receiptnumber ?? 'N/A' }}</strong>
                </td>
                <td>
                    <h4>Date</h4>
                    <strong>{{ $receipt->created_at->format('d M Y') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <!-- Supplier & Invoice Details -->
    <div class="section">
        <table>
            <tr>
                <td class="box" style="width: 48%; vertical-align: top;">
                    <h4>Supplier Details</h4>
                    <div>{{ $customer->name ?? 'N/A' }}</div>
                    <div>Registration: {{ $customer->regnumber ?? 'N/A' }}</div>
                    @if($customer->email)
                        <div>Email: {{ $customer->email }}</div>
                    @endif
                </td>

                <td style="width: 4%;"></td>

                <td class="box" style="width: 48%; vertical-align: top;">
                    <h4>Invoice Details</h4>
                    <div>Invoice Number: {{ $invoice->invoicenumber }}</div>
                    <div>Invoice Date: {{ $invoice->created_at->format('d M Y') }}</div>
                    <div>Item: {{ $inventoryitem->name ?? 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Amount -->
    <div class="section amount-box">
        <h2>Amount Paid: {{ $currency->name ?? '' }} {{ number_format($invoice->amount, 2) }}</h2>
    </div>

    <!-- Payment Details -->
    <div class="section box">
        <table class="details">
            <tr>
                <td>Payment Status</td>
                <td><strong>{{ $invoice->status }}</strong></td>
            </tr>

            <tr>
                <td>Payment Method</td>
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
                <td>Transaction Reference</td>
                <td>{{ $receipt->suspense->banktransaction->sourcereference ?? 'N/A' }}</td>
            </tr>
            @endif

            @if($invoice->settled_at)
            <tr>
                <td>Settlement Date</td>
                <td>{{ $invoice->settled_at->format('d M Y') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>This is a computer-generated receipt. No signature required.</div>
        <div>&copy; Procurement Regulatory Authority of Zimbabwe</div>
        <div>Generated on {{ \Carbon\Carbon::now()->format('d M Y H:i') }}</div>
    </div>

</body>
</html>
