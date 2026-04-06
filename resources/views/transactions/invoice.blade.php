<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $transaction->receipt_number ?: $transaction->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 10px;
            line-height: 1.35;
            margin: 0;
            padding: 18px;
            background: #f8fafc;
        }

        .sheet {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 22px;
        }

        .row {
            width: 100%;
            margin-bottom: 18px;
        }

        .left,
        .right {
            display: inline-block;
            vertical-align: top;
        }

        .left {
            width: 54%;
        }

        .right {
            width: 45%;
            text-align: right;
        }

        h1,
        h2,
        p {
            margin: 0;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 8px;
        }

        h2 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
            color: #475569;
        }

        .muted {
            color: #64748b;
        }

        .meta {
            margin-top: 8px;
        }

        .meta div {
            margin-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            vertical-align: top;
            text-align: left;
            font-size: 9px;
        }

        th {
            background: #e2e8f0;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            width: 280px;
            margin-left: auto;
            margin-top: 14px;
        }

        .summary td:first-child {
            font-weight: 700;
            width: 55%;
        }

        .terms {
            margin-top: 20px;
            font-size: 9px;
        }

        .terms p {
            margin-bottom: 6px;
        }

        .thank-you {
            margin-top: 12px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    @php
        $customerName = $transaction->customer?->name ?? $transaction->customer_name ?? 'Walk-in Customer';
        $customerPhone = $transaction->customer?->phone ?? $transaction->customer_phone;
        $customerEmail = $transaction->customer?->email ?? $transaction->customer_email;
        $receiptType = match ($transaction->type) {
            'buy' => 'Buy Receipt',
            'repair' => 'Repair Receipt',
            default => 'Sell Receipt',
        };
        $amountPaid = (float) ($transaction->amount_paid ?? $transaction->total_amount);
        $balance = max(0, (float) $transaction->total_amount - $amountPaid);
    @endphp

    <div class="sheet">
        <div class="row">
            <div class="left">
                <h1>{{ $business['name'] }}</h1>
                @foreach($business['address_lines'] as $line)
                    <p>{{ $line }}</p>
                @endforeach
                <p>{{ $business['phone'] }}</p>
            </div>
            <div class="right">
                <div class="meta">
                    <div><strong>Date:</strong> {{ $transaction->created_at->format('d/m/Y') }}</div>
                    <div><strong>Receipt No:</strong> {{ $transaction->receipt_number ?: 'R-'.$transaction->id }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="left">
                <h2>Customer Details</h2>
                <p><strong>Name:</strong> {{ $customerName }}</p>
                <p><strong>Phone:</strong> {{ $customerPhone ?: '' }}</p>
                @if($customerEmail)
                    <p><strong>Email:</strong> {{ $customerEmail }}</p>
                @endif
            </div>
            <div class="right">
                <h2>{{ $receiptType }}</h2>
            </div>
        </div>

        <div class="row">
            <h2>Device Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Storage</th>
                        <th>Colour</th>
                        <th>IMEI 1</th>
                        <th>IMEI 2</th>
                        <th>Condition</th>
                        <th class="text-right">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->items as $item)
                        <tr>
                            <td>{{ $item->brand ?: 'N/A' }}</td>
                            <td>
                                {{ $item->model ?: ($item->product?->name ?: ($item->description ?: 'N/A')) }}
                                @if($item->description)
                                    <div class="muted">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td>{{ $item->storage ?: 'N/A' }}</td>
                            <td>{{ $item->color ?: 'N/A' }}</td>
                            <td>{{ $item->imei_1 ?: 'N/A' }}</td>
                            <td>{{ $item->imei_2 ?: 'N/A' }}</td>
                            <td>{{ $item->condition_grade ?: 'N/A' }}</td>
                            <td class="text-right">£{{ number_format((float) $item->price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <table class="summary">
            <tbody>
                <tr>
                    <td>Sale Price</td>
                    <td>£{{ number_format((float) $transaction->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Amount Paid</td>
                    <td>£{{ number_format($amountPaid, 2) }}</td>
                </tr>
                <tr>
                    <td>Balance</td>
                    <td>£{{ number_format($balance, 2) }}</td>
                </tr>
                <tr>
                    <td>Payment Method</td>
                    <td>{{ $transaction->payment_method ?: 'Cash' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="terms">
            <h2>Terms &amp; Conditions</h2>
            <p>All sales are subject to PhoneWorks terms and conditions. Please keep this receipt for warranty and future reference.</p>

            <p><strong>Returns &amp; Warranty:</strong> 14-day return or exchange on purchases (device must be in the same condition with all accessories). Phones include a 1-year hardware warranty; accessories include 6-month warranty. Warranty does not cover physical damage, liquid damage, battery wear, software issues, or lost/stolen devices.</p>

            <p><strong>Condition &amp; Inspection:</strong> Customers should inspect devices before leaving the shop. Once the device leaves the premises, PhoneWorks is not responsible for physical damage. Used/refurbished devices may show minor wear; battery health may vary.</p>

            <p><strong>Repairs:</strong> Repair quotes are estimates only. PhoneWorks is not responsible for hidden faults revealed during repair. Third-party repair attempts void any warranty.</p>

            <p><strong>Buy-Back / Trade-In:</strong> Seller confirms they legally own the device and may be asked for ID. Devices are checked for IMEI/activation lock status; failed checks cancel the transaction. All trade-ins/buy-backs are final once payment is made.</p>

            <p><strong>Data &amp; Privacy:</strong> Customers must back up and remove personal data before sale, return, or repair. PhoneWorks is not liable for any data loss or remaining data on devices.</p>

            <p><strong>Proof of Purchase:</strong> A valid receipt is required for returns, warranty claims, or service.</p>

            <p><strong>Payments:</strong> Card/bank payments may take time to process. Refunds are issued to the original payment method only.</p>

            <p><strong>Liability:</strong> PhoneWorks is not liable for indirect or consequential losses arising from device failure or repair.</p>

            <p class="thank-you">Thank you for choosing PhoneWorks</p>
        </div>
    </div>
</body>
</html>
