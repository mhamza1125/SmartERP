<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Invoice - {{ $order->order_no }}</title>
    <style>
        @media print {
            @page {
                margin: 1in;
                size: A4;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
            margin: 0;
            padding: 20px;
            padding-bottom: 80px;
        }

        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .print-header img {
            max-height: 80px;
            margin-bottom: 10px;
        }

        .print-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .print-header p {
            margin: 5px 0;
            font-size: 12px;
        }

        .invoice-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .invoice-details .left,
        .invoice-details .right {
            width: 48%;
        }

        .invoice-details h3 {
            margin-bottom: 10px;
            font-size: 14px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .invoice-details p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-section {
            margin-top: 20px;
            text-align: right;
        }

        .total-section table {
            width: 300px;
            margin-left: auto;
        }

        .print-footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid #000;
            padding-top: 10px;
            background: white;
        }

        .no-print {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="print-header">
        <img src="{{ URL::asset('assets/print-logo.png') }}" alt="Company Logo">
        <h1>{{ $company->name ?? 'Sajjadson Lab Equipment' }}</h1>
        <p>{{ $company->address ?? 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan' }}</p>
        <p>Phone: {{ $company->phone ?? '+92 52 357 3727' }} || Email: {{ $company->email ?? 'info@sajjadsonlab.com' }} || Web: {{ $company->website ?? 'sajjadsonlab.com' }}</p>
    </div>

    <div class="invoice-title">Order Invoice</div>

    <div class="invoice-details">
        <div class="left">
            <h3>Order Information</h3>
            <p><strong>Order No:</strong> {{ $order->order_no }}</p>
            <p><strong>Job No:</strong> {{ $order->job_no }}</p>
            <p><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('d-m-Y') }}</p>
            @if($order->due_date)
            <p><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($order->due_date)->format('d-m-Y') }}</p>
            @endif
            @if($order->payment_terms)
            <p><strong>Payment Terms:</strong> {{ $order->payment_terms }}</p>
            @endif
        </div>
        <div class="right">
            <h3>Customer Information</h3>
            <p><strong>Customer:</strong> {{ $customer->fname }} {{ $customer->lname }}</p>
            <p><strong>Email:</strong> {{ $customer->email }}</p>
            <p><strong>Phone:</strong> {{ $customer->phone }}</p>
            @if($customer->address)
            <p><strong>Address:</strong> {{ $customer->address }}</p>
            @endif
            @if($customer->fi_no)
            <p><strong>FI No:</strong> {{ $customer->fi_no }}</p>
            @endif
            @if($customer->rex_no)
            <p><strong>REX No:</strong> {{ $customer->rex_no }}</p>
            @endif
            @if($customer->ntn)
            <p><strong>NTN:</strong> {{ $customer->ntn }}</p>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Article No</th>
                <th>Product Name</th>
                <th>Size</th>
                <th>Stage</th>
                <th class="text-center">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($orderItems) && $orderItems->count())
                @foreach($orderItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->article_no }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->hname }}</td>
                    <td>{{ $item->sname }}</td>
                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    @if(isset($orderItems) && $orderItems->count())
    <div class="total-section">
        <table>
            <tr>
                <td><strong>Total Amount:</strong></td>
                <td class="text-right"><strong>{{ number_format($orderItems->sum('total'), 2) }}</strong></td>
            </tr>
        </table>
    </div>
    @endif

    <div class="print-footer">
        <p>{!! $company->footer_text ?? 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan' !!}</p>
        <p>Phone: {{ $company->phone ?? '+92 52 357 3727' }} || Email: {{ $company->email ?? 'info@sajjadsonlab.com' }} || Web: {{ $company->website ?? 'sajjadsonlab.com' }}</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            };
        };
    </script>
</body>
</html>
