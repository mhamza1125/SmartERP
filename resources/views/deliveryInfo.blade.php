@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Delivery Info @if(isset($isMultiOrder) && $isMultiOrder) <span class="badge badge-secondary ml-2">Multi-Order</span> @endif</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <button type="button" class="btn {{ isset($isMultiOrder) && $isMultiOrder ? 'btn-light' : 'btn-info' }} dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fas fa-print"></i> Print
                </button>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="#" onclick="printPage('Delivery Information')">Delivery Info</a>
                  <a class="dropdown-item" href="#" onclick="printCommercialInvoice(true, true)">Commercial Invoice</a>
                  <a class="dropdown-item" href="#" onclick="printPackingList()">Packing List</a>
                </div>
                <a href="{{ route('delivery') }}" class="btn {{ isset($isMultiOrder) && $isMultiOrder ? 'btn-light' : 'btn-primary' }}">Back</a>
                @php
                  $deliveryId = is_array($delivery) ? $delivery['delivery_id'] : $delivery->delivery_id;
                  $customerId = is_array($delivery) ? $delivery['customer_id'] : $delivery->customer_id;
                @endphp
                @if(isset($isMultiOrder) && $isMultiOrder && isset($relatedOrders) && count($relatedOrders) > 1)
                  @php
                    $orderIds = collect($relatedOrders)->pluck('order_id')->implode(',');
                  @endphp
                  <a href="{{ route('delivery.add') }}?customer_id={{ $customerId }}&order_ids={{ $orderIds }}&edit_delivery_id={{ $deliveryId }}" class="btn btn-light">Edit</a>
                @else
                  <a href="{{ route('delivery.edit', $deliveryId) }}" class="btn btn-primary">Edit</a>
                @endif
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Customer No:</b> {{is_array($delivery) ? $delivery['customer_no'] : $delivery->customer_no}}</td></tr>
                    {{-- <tr><td><b>Customer Name:</b> {{is_array($delivery) ? $delivery['fname'] : $delivery->fname}} {{is_array($delivery) ? $delivery['lname'] : $delivery->lname}}</td></tr> --}}
                    @if(isset($isMultiOrder) && $isMultiOrder && isset($relatedOrders) && count($relatedOrders) > 1)
                    <tr><td><b>Primary Order:</b> {{is_array($delivery) ? $delivery['order_no'] : $delivery->order_no}}</td></tr>
                    <tr><td><b>Primary Job No:</b> {{is_array($delivery) ? $delivery['job_no'] : $delivery->job_no}}</td></tr>
                    <tr><td><b>All Orders:</b>
                      @foreach($relatedOrders as $index => $order)
                        @if($index < 5)
                        <span class="badge badge-secondary mr-1 mb-1">{{$order->order_no ?? 'N/A'}}</span>
                        @endif
                      @endforeach
                      @if(count($relatedOrders) > 5)
                      <span class="badge badge-light">+{{count($relatedOrders) - 5}} more</span>
                      @endif
                    </td></tr>
                    <tr><td><b>All Job Numbers:</b>
                      @foreach($relatedOrders as $index => $order)
                        @if($index < 5)
                        <span class="badge badge-secondary mr-1 mb-1">{{$order->job_no ?? 'N/A'}}</span>
                        @endif
                      @endforeach
                      @if(count($relatedOrders) > 5)
                      <span class="badge badge-light">+{{count($relatedOrders) - 5}} more</span>
                      @endif
                    </td></tr>
                    @else
                    <tr><td><b>Order No:</b> {{is_array($delivery) ? $delivery['order_no'] : $delivery->order_no}}</td></tr>
                    <tr><td><b>Job No:</b> {{is_array($delivery) ? $delivery['job_no'] : $delivery->job_no}}</td></tr>
                    @endif
                    <tr><td><b>Order Date:</b> {{is_array($delivery) ? $delivery['order_date'] : $delivery->order_date}}</td></tr>
                    @php $description = is_array($delivery) ? $delivery['description'] : $delivery->description; @endphp
                    @if($description)<tr><td><b>Detail:</b></td></tr>
                    <tr><td>@php echo $description @endphp</td></tr>@endif
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Delivery No:</b> {{$delivery['customer_no']}}</td></tr>
                    <tr><td><b>Shipping From:</b> {{$delivery['fshipping']}}</td></tr>
                    <tr><td><b>Port No:</b> {{$delivery['fport_no']}}</td></tr>
                    <tr><td><b>Shipping To:</b> {{$delivery['tshipping']}}</td></tr>
                    <tr><td><b>Port No:</b> {{$delivery['tport_no']}}</td></tr>
                    <tr><td><b>Delivery Method: </b> 
                      @if($delivery['delivery_method'] == 1) Sea Freight 
                      @elseif($delivery['delivery_method'] == 2) Air Freight 
                      @elseif($delivery['delivery_method'] == 3) Road Transport 
                      @else Unknown @endif</td></tr>
                    <tr><td><b>Delivery Status:</b> 
                      @if($delivery['delivery_status'] == 1) <span class="badge badge-warning">Pending</span>
                      @elseif($delivery['delivery_status'] == 2) <span class="badge badge-success">Delivered</span>
                      @elseif($delivery['delivery_status'] == 3) <span class="badge badge-danger">Returned</span>
                      @elseif($delivery['delivery_status'] == 4) <span class="badge badge-danger">Disputed</span>
                      @else @endif
                    </td></tr>
                    @if($delivery['fi_no'])
                    <tr><td><b>FI No:</b> {{$delivery['fi_no']}}</td></tr>
                    @endif
                    @if($delivery['rex_no'])
                    <tr><td><b>REX No:</b> {{$delivery['rex_no']}}</td></tr>
                    @endif
                    @if($delivery['ntn'])
                    <tr><td><b>NTN:</b> {{$delivery['ntn']}}</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            @if($delivery['so_origin'])
            <div class="row">
              <div class="col-md-12">
                <div class="card">
                  <div class="card-header">
                    <h6>Statement of Origin</h6>
                  </div>
                  <div class="card-body">
                    <p>{{$delivery['so_origin']}}</p>
                  </div>
                </div>
              </div>
            </div>
            @endif
            <div class="row">
              <div class="col-md-12 mt-2">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Article</th>
                      <th>Item / Product</th>
                      <th>Product Stage</th>
                      <th>Size</th>
                      <th>Unit</th>
                      <th>Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($deliveryItem->count())
                      @php $product_id = 0; $index = 1; @endphp
                      @foreach($deliveryItem as $item)
                        @unless($item->product_type_id == 0)
                          <tr>
                            <td>{{$index++}}</td>
                            @if($item->product_id == $product_id)
                              <td colspan="2"></td>
                            @else
                              <td>{{$item->article_no}}</td>
                              <td>{{$item->name}}</td>
                            @endif
                            <td>{{$item->sname}}</td>
                            <td>{{$item->hname}}</td>
                            <td>{{$item->puname}}</td>
                            <td>{{$item->quantity}}</td>
                          </tr>
                        @endunless
                      @php $product_id = $item->product_id; @endphp
                      @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Article</th>
                      <th>Item / Product</th>
                      <th>Product Stage</th>
                      <th>Size</th>
                      <th>Unit</th>
                      <th>Quantity</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <h5>Delivery to Container</h5>
            @if($deliveryBox->count())
            <div class="row">
              <div class="col-md-12 mt-2">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Vehicle No</th>
                      <th>Row 1</th>
                      <th>Row 2</th>
                      <th>Row 3</th>
                      <th>Row 4</th>
                      <th>Row 5</th>
                      <th>Row 6</th>
                      <th>Row 7</th>
                      <th>Row 8</th>
                      <th>Total Qty</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($deliveryBox->count())
                      @php $total = 0; @endphp
                      @foreach($deliveryBox as $item)
                        @php
                           $rowQtys = explode('|', $item->rowQty);
                           $total += $item->totalQty
                        @endphp
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->vehicle_no}}</td>
                          @foreach($rowQtys as $qty)
                            <td>{{$qty}}</td>
                          @endforeach
                          <td>{{$item->totalQty}}</td>
                        </tr>
                      @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th colspan="7"></th>
                      <th colspan="2">Grand Total:</th>
                      <th colspan="2">{{$total}} Boxes</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
            @else
              <blockquote> No Delivery to Container Record </blockquote>
            @endif

            <h5>Delivery Container / Vehicle</h5>
            @if($deliveryItem->where('product_type_id', 0)->count())
            <div class="row">
              <div class="col-md-12 mt-2">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Vehicle Type</th>
                      <th>Vehicle Name</th>
                      <th>Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($deliveryItem->count())
                      @php $index = 1; @endphp
                      @foreach($deliveryItem as $item)
                        @unless($item->product_type_id != 0)
                          <tr>
                            <td>{{$index++}}</td>
                            <td>{{$item->material_no}}</td>
                            <td>{{$item->mname}}</td>
                            <td>{{$item->quantity}}</td>
                          </tr>
                        @endunless
                      @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            @else
              <blockquote> No Delivery Conatiner / Vehicle </blockquote>
            @endif

            <h5>Delivery Expense</h5>
            @if($transaction->count())
            <div class="row">
              <div class="col-md-12 mt-2">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Head</th>
                      <th>Paid By</th>
                      <th>Amount</th>
                      <th>Detail</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($transaction->count())
                      @foreach($transaction as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->name}}</td>
                          <td>@if(isset($item->bname))
                                {{$item->bname}} - {{$item->account_title}} - {{$item->account}}
                              @else
                                Cash Payment
                              @endif</td>
                          <td>{{number_format($item->debit)}}</td>
                          <td>{{$item->description}}</td>
                        </tr>
                      @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            @else
              <blockquote> No Delivery Expense </blockquote>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// Commercial Invoice Print Functions for Delivery
function printCommercialInvoice(bankDetails, includeSO) {
    console.log('printCommercialInvoice() called for delivery');
    var title = 'Commercial Invoice';
    var content = generateDeliveryInvoiceContent(title, bankDetails, includeSO);
    console.log('Generated content length:', content.length);
    printInvoice(content, title);
}

function printPackingList() {
    console.log('printPackingList() called');
    var title = 'Packing List';
    var content = generatePackingListContent();
    console.log('Generated content length:', content.length);
    printInvoice(content, title);
}

function generateDeliveryInvoiceContent(title, bankDetails, includeSO) {
    var content = `
        <div class="invoice-title">${title}</div>
        <div class="invoice-details">
            <table style="width: 100%; margin-bottom: 20px;">
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <h4>Bill To:</h4>
                        <p><strong>{{$delivery['fname']}} {{$delivery['lname']}}</strong></p>
                        <p>{{$delivery['address']}}</p>
                        <p>{{$delivery['city']}}, {{$delivery['country']}}</p>
                        <p>Phone: {{$delivery['phone']}}</p>
                        <p>Email: {{$delivery['email']}}</p>
                        @if($delivery['fi_no'])
                        <p><strong>FI No:</strong> {{$delivery['fi_no']}}</p>
                        @endif
                        @if($delivery['rex_no'])
                        <p><strong>REX No:</strong> {{$delivery['rex_no']}}</p>
                        @endif
                        @if($delivery['ntn'])
                        <p><strong>NTN:</strong> {{$delivery['ntn']}}</p>
                        @endif
                    </td>
                    <td style="width: 50%; vertical-align: top; text-align: right;">
                        <h4>Delivery Details:</h4>
                        <p><strong>Delivery No:</strong> {{$delivery['customer_no']}}</p>
                        <p><strong>Order No:</strong> {{$delivery['order_no']}}</p>
                        <p><strong>Job No:</strong> {{$delivery['job_no']}}</p>
                        <p><strong>Order Date:</strong> {{$delivery['order_date']}}</p>
                        <p><strong>Shipping From:</strong> {{$delivery['fshipping']}}</p>
                        <p><strong>Shipping To:</strong> {{$delivery['tshipping']}}</p>
                    </td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #000; padding: 8px;">Sr.</th>
                        <th style="border: 1px solid #000; padding: 8px;">Description</th>
                        <th style="border: 1px solid #000; padding: 8px;">Stage</th>
                        <th style="border: 1px solid #000; padding: 8px;">Quantity</th>
                        <th style="border: 1px solid #000; padding: 8px;">Unit Price</th>
                        <th style="border: 1px solid #000; padding: 8px;">Total</th>
                    </tr>
                </thead>
                <tbody>`;

    @if($deliveryItem->count())
        @foreach($deliveryItem as $item)
            content += `
                <tr>
                    <td style="border: 1px solid #000; padding: 8px;">{{$loop->index + 1}}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{$item->article_no}} - {{$item->name}}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{$item->sname}}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{$item->quantity}}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{number_format($item->price, 2)}}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{number_format($item->total, 2)}}</td>
                </tr>`;
        @endforeach
    @endif

    content += `
                </tbody>
            </table>
        </div>`;

    if (bankDetails) {
        content += `
            <div class="bank-details">
                <h4>Bank Details</h4>
                <p><strong>Bank Name:</strong> [Bank Name]</p>
                <p><strong>Account Title:</strong> [Account Title]</p>
                <p><strong>Account Number:</strong> [Account Number]</p>
                <p><strong>IBAN:</strong> [IBAN]</p>
                <p><strong>Swift Code:</strong> [Swift Code]</p>
            </div>`;
    }

    if (includeSO && '{{$delivery["so_origin"]}}') {
        content += `
            <div class="so-section">
                <h4>Statement of Origin</h4>
                <p>{{$delivery['so_origin']}}</p>
            </div>`;
    }

    return content;
}

function generatePackingListContent() {
    var content = `
        <div class="invoice-title">Packing List</div>
        <div class="invoice-details">
            <table style="width: 100%; margin-bottom: 20px;">
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <h4>Ship To:</h4>
                        <p><strong>{{$delivery['fname']}} {{$delivery['lname']}}</strong></p>
                        <p>{{$delivery['address']}}</p>
                        <p>{{$delivery['city']}}, {{$delivery['country']}}</p>
                    </td>
                    <td style="width: 50%; vertical-align: top; text-align: right;">
                        <h4>Shipping Details:</h4>
                        <p><strong>Delivery No:</strong> {{$delivery['customer_no']}}</p>
                        <p><strong>Order No:</strong> {{$delivery['order_no']}}</p>
                        <p><strong>Shipping From:</strong> {{$delivery['fshipping']}}</p>
                        <p><strong>Shipping To:</strong> {{$delivery['tshipping']}}</p>
                        <p><strong>Method:</strong>
                        @if($delivery['delivery_method'] == 1) Sea Freight
                        @elseif($delivery['delivery_method'] == 2) Air Freight
                        @elseif($delivery['delivery_method'] == 3) Road Transport
                        @endif</p>
                    </td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <thead>
                    <tr style="background-color: #f0f0f0;">
                        <th style="border: 1px solid #000; padding: 8px;">Sr.</th>
                        <th style="border: 1px solid #000; padding: 8px;">Description</th>
                        <th style="border: 1px solid #000; padding: 8px;">Quantity</th>
                        <th style="border: 1px solid #000; padding: 8px;">Boxes</th>
                    </tr>
                </thead>
                <tbody>`;

    @if($deliveryItem->count())
        @foreach($deliveryItem as $item)
            content += `
                <tr>
                    <td style="border: 1px solid #000; padding: 8px;">{{$loop->index + 1}}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{$item->article_no}} - {{$item->name}}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{$item->quantity}}</td>
                    <td style="border: 1px solid #000; padding: 8px;">{{number_format(ceil($item->quantity * ($item->bqty ?? 1)))}}</td>
                </tr>`;
        @endforeach
    @endif

    content += `
                </tbody>
            </table>
        </div>`;

    return content;
}

function printInvoice(content, title) {
    console.log('printInvoice() called with title:', title);
    console.log('Fetching company data...');

    // Fetch company data and generate print document
    fetch('/company/data')
        .then(response => response.json())
        .then(company => {
            generateInvoicePrintDocument(content, title, company);
        })
        .catch(error => {
            console.error('Error fetching company data:', error);
            // Fallback to default company data
            const defaultCompany = {
                name: 'Sajjadson Lab Equipment',
                address: 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan',
                phone: '+92 52 357 3727',
                email: 'info@sajjadsonlab.com',
                website: 'sajjadsonlab.com',
                logo_path: 'assets/print-logo.png',
                footer_text: 'Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan'
            };
            generateInvoicePrintDocument(content, title, defaultCompany);
        });
}

function generateInvoicePrintDocument(content, title, company) {
    console.log('Opening print window...');

    var printWindow = window.open('', '_blank');

    if (!printWindow) {
        alert('Pop-up blocked! Please allow pop-ups for this site to generate invoices.');
        return;
    }

    var printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>${title}</title>
            <style>
                @media print {
                    body { margin: 0; padding: 20px; padding-bottom: 80px; font-family: Arial, sans-serif; }
                    .no-print { display: none !important; }
                }
                body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #000; background: white; margin: 0; padding: 20px; padding-bottom: 80px; }
                .print-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px; }
                .print-header img { max-height: 80px; margin-bottom: 10px; }
                .print-header h1 { margin: 0; font-size: 24px; font-weight: bold; }
                .print-header p { margin: 5px 0; font-size: 12px; }
                .invoice-title { text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0; text-decoration: underline; }
                .invoice-details { margin: 20px 0; }
                .invoice-details table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                .invoice-details th, .invoice-details td { border: 1px solid #000; padding: 8px; text-align: left; }
                .invoice-details th { background-color: #f0f0f0; font-weight: bold; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .total-section { margin: 20px 0; }
                .total-section table { width: 300px; margin-left: auto; }
                .total-section td { padding: 5px; border: 1px solid #000; }
                .print-footer { position: fixed; bottom: 20px; left: 0; right: 0; text-align: center; font-size: 12px; border-top: 1px solid #000; padding-top: 10px; background: white; }
                .no-print { display: none !important; }
                .bank-details { margin: 20px 0; padding: 15px; border: 1px solid #ddd; background-color: #f9f9f9; }
                .bank-details h4 { margin-top: 0; }
                .so-section { margin: 20px 0; }
                .so-section h4 { margin-bottom: 10px; }
            </style>
        </head>
        <body>
            <div class="print-header">
                <img src="${window.location.origin}/${company.logo_path}" alt="Company Logo">
                <h1>${company.name}</h1>
                <p>${company.address}</p>
                <p>Phone: ${company.phone} || Email: ${company.email} || Web: ${company.website}</p>
            </div>
            <div class="print-content">
                ${content}
            </div>
            <div class="print-footer">
                <p>${company.footer_text}</p>
                <p>Phone: ${company.phone} || Email: ${company.email} || Web: ${company.website}</p>
            </div>
        </body>
        </html>
    `;

    console.log('Writing content to print window...');
    printWindow.document.write(printContent);
    printWindow.document.close();

    console.log('Focusing and printing...');
    printWindow.focus();
    printWindow.print();

    // Close window after a short delay to allow printing
    setTimeout(function() {
        printWindow.close();
        console.log('Print window closed');
    }, 1000);
}
</script>

@endsection