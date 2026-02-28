<!DOCTYPE html>
<html>
<head>
    <title>Invoice #{{ $transaction->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 12px;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            line-height: 24px;
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }
        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }
        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }
        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }
        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }
        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }
        .invoice-box table tr.item.last td {
            border-bottom: none;
        }
        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                {{ $business['name'] }}
                            </td>
                            <td>
                                Invoice #: {{ $transaction->id }}<br>
                                Created: {{ $transaction->created_at->format('M d, Y') }}<br>
                                @if($transaction->customer)
                                    Customer ID: {{ $transaction->customer->id }}
                                @elseif($transaction->customer_name)
                                    Customer: {{ $transaction->customer_name }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                {{ $business['name'] }}<br>
                                {{ $business['address'] }}<br>
                                {{ $business['phone'] }}<br>
                                {{ $business['email'] }}
                            </td>
                            <td>
                                @if($transaction->customer)
                                    {{ $transaction->customer->name }}<br>
                                    @if($transaction->customer->email) {{ $transaction->customer->email }}<br> @endif
                                    @if($transaction->customer->phone) {{ $transaction->customer->phone }} @endif
                                @elseif($transaction->customer_name)
                                    {{ $transaction->customer_name }}<br>
                                    @if($transaction->customer_email) {{ $transaction->customer_email }}<br> @endif
                                    @if($transaction->customer_phone) {{ $transaction->customer_phone }} @endif
                                @else
                                    Walk-in Customer
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="heading">
                <td>Item</td>
                <td>Price</td>
            </tr>
            @foreach($transaction->items as $item)
            <tr class="item">
                <td>
                    {{ $item->description }}
                    @if($item->product)
                        ({{ $item->product->name }})
                    @endif
                    x {{ $item->quantity }}
                </td>
                <td>£{{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td></td>
                <td>Total: £{{ number_format($transaction->total_amount, 2) }}</td>
            </tr>
        </table>
        <div class="footer">
            All repairs are covered under a 6 month warranty besides accidental damage.
        </div>
    </div>
</body>
</html>
