@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Order Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ route('order.estimate', $order['order_id']) }}" class="btn btn-success">Estimate Material</a>
                <a href="{{ route('order.status', $order['order_id']) }}" class="btn btn-success">Order Status</a>
                <a href="{{ route('delivery.add', $order['order_id']) }}" class="btn btn-success">Deliver</a>
              </div>
              <div class="btn-group">
                <div class="dropdown">
                  <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-print"></i> Print
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('order.print', $order['order_id']) }}" target="_blank">
                      <i class="fas fa-file-alt"></i> Order Details
                    </a>
                  </div>
                </div>
              </div>
              <div class="btn-group">
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#invoiceModal">
                  <i class="fas fa-file-invoice"></i> Generate Invoice
                </button>
                <a href="{{ route('order') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('order.edit', $order['order_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-7">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Customer No:</b> {{$order['customer_no']}}</td></tr>
                    <tr><td><b>Customer Name:</b> {{$order['fname']}} {{$order['lname']}}</td></tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-5">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Voucher No:</b> TXN-{{ date('Y') }}-{{ str_pad($order['order_id'], 4, '0', STR_PAD_LEFT) }}</td></tr>
                    <tr><td><b>Order No</b> {{$order['order_no']}}</td></tr>
                    <tr><td><b>Job No:</b> {{$order['job_no']}}</td></tr>
                    <tr><td><b>Date:</b> {{$order['order_date']}}</td></tr>
                    @if($order['due_date'])
                    <tr><td><b>Due Date:</b> {{$order['due_date']}}</td></tr>
                    @endif
                    @if($order['payment_terms'])
                    <tr><td><b>Payment Terms:</b> {{$order['payment_terms']}}</td></tr>
                    @endif
                    {{-- @if($order['expected_delivery_date'])
                    <tr><td><b>Expected Delivery:</b> {{$order['expected_delivery_date']}}</td></tr>
                    @endif --}}
                    <tr><td><b>Order Status:</b>
                      @if($order['order_status'] == 1) <span class="badge badge-warning">Pending</span>
                      @elseif($order['order_status'] == 2) <span class="badge badge-success">Processing</span>
                      @elseif($order['order_status'] == 3) <span class="badge badge-warning">On Hold</span>
                      @elseif($order['order_status'] == 4) <span class="badge badge-success">Partially Delivered</span>
                      @elseif($order['order_status'] == 5) <span class="badge badge-success">Delivered</span>
                      @elseif($order['order_status'] == 6) <span class="badge badge-success">Completed</span>
                      @elseif($order['order_status'] == 7) <span class="badge badge-danger">Canceled</span>
                      @elseif($order['order_status'] == 8) <span class="badge badge-danger">Returned</span>
                      @elseif($order['delivery_status'] == 9) <span class="badge badge-warning">Disputed</span>
                      @else @endif
                    </td></tr>
                  </tbody>
                </table>
              </div>
            </div>
            @if($order['so_origin'])
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label><strong>Statement of Origin:</strong></label>
                  <div class="border p-3 bg-light">
                    {!! nl2br(e($order['so_origin'])) !!}
                  </div>
                </div>
              </div>
            </div>
            @endif

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs" id="orderTabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="order-details-tab" data-toggle="tab" href="#order-details" role="tab" aria-controls="order-details" aria-selected="true">Order Details</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="packing-list-tab" data-toggle="tab" href="#packing-list" role="tab" aria-controls="packing-list" aria-selected="false">Packing List</a>
              </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="orderTabContent">
              <!-- Order Details Tab -->
              <div class="tab-pane fade show active" id="order-details" role="tabpanel" aria-labelledby="order-details-tab">
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Article No</th>
                          <th>Item / Product</th>
                          <th>Product Stage</th>
                          <th>Size</th>
                          <th>Unit</th>
                          <th>Quantity</th>
                          <th>Box Quantity</th>
                          <th>Price (Currency)</th>
                          <th>Exchange (Pkr)</th>
                          <th>Price (Pkr)</th>
                          <th>Total (Pkr)</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if($orderItem->count())
                          @php $product_id = 0; @endphp
                          @foreach($orderItem as $item)
                            <tr>
                              <td>{{$loop->index + 1}}</td>
                              @if($item->product_id == $product_id)
                                <td colspan="2"></td>
                              @else
                                <td>{{$item->article_no}}</td>
                                <td>{{$item->name}}</td>
                              @endif
                              <td>{{$item->sname}}</td>
                              <td>{{$item->hname}}</td>
                              <td>{{$item->uname}}</td>
                              <td>{{$item->quantity}}</td>
                              <td>{{ $item->box_quantity ? number_format($item->box_quantity) . ' boxes' : 'N/A' }}</td>
                              <td>{{$item->price2}} {{$item->cname}}</td>
                              <td>{{$item->exchange}}</td>
                              <td>{{$item->price}}</td>
                              <td>{{$item->quantity * $item->price}}</td>
                            </tr>
                          @php $product_id = $item->product_id; @endphp
                          @endforeach
                        @endif
                      </tbody>
                      <tfoot>
                        @php $total = $orderItem->sum(function($item) {
                          return $item->quantity * $item->price;
                        }); @endphp
                        <tr>
                          <th colspan="10"></th>
                          <th>Grand Total:</th>
                          <th>{{ number_format($total) }}</th>
                        </tr>
                        <tr>
                          <th colspan="10"></th>
                          <th>Amount in Words:</th>
                          <th>{{ numberToWordsWithCurrency($total) }}</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Packing List Tab -->
              <div class="tab-pane fade" id="packing-list" role="tabpanel" aria-labelledby="packing-list-tab">
                <div class="row">
                  <div class="col-md-12 mt-2">
                    <table class="table table-sm table-striped">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Product</th>
                          <th>Pieces/Boxes</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(isset($packingList['items']) && count($packingList['items']) > 0)
                          @php $index = 1; @endphp
                          @foreach($packingList['items'] as $product => $data)
                            <tr>
                              <td>{{ $index++ }}</td>
                              <td>{{ $product }}</td>
                              <td>{{ number_format($data['quantity']) }} pcs / {{ number_format($data['boxes'], 2) }} boxes</td>
                            </tr>
                          @endforeach
                        @else
                          <tr>
                            <td colspan="3" class="text-center">No packing data available</td>
                          </tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr>
                          <th colspan="2">Total:</th>
                          <th>{{ isset($packingList['totalQuantity']) ? number_format($packingList['totalQuantity']) : 0 }} pcs / {{ isset($packingList['totalBoxes']) ? number_format($packingList['totalBoxes']) : 0 }} boxes</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Invoice Generation Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="invoiceModalLabel">Generate Invoice</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="invoiceForm">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><strong>Invoice Type</strong></label>
                <div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="invoice_type" id="performa" value="performa" checked>
                    <label class="form-check-label" for="performa">
                      Proforma Invoice
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="invoice_type" id="production" value="production">
                    <label class="form-check-label" for="production">
                      Production Order
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><strong>Select Bank</strong></label>
                <select class="form-control" id="bank_select" name="bank_id" required>
                  <option value="">Select Bank</option>
                  @if(isset($banks) && $banks->count() > 0)
                    @foreach($banks as $bank)
                      <option value="{{ $bank->bank_id }}"
                              data-title="{{ $bank->account_title }}"
                              data-account="{{ $bank->account }}"
                              data-iban="{{ $bank->iban ?? '' }}"
                              data-address="{{ $bank->address ?? '' }}"
                              data-branch="{{ $bank->branch_code ?? '' }}"
                              data-swift="{{ $bank->swift_code ?? '' }}">
                        {{ $bank->account_title }} - {{ $bank->account }}
                      </option>
                    @endforeach
                  @endif
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="include_so" name="include_so" checked>
                  <label class="form-check-label" for="include_so">
                    Include Statement of Origin
                  </label>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="generateInvoice()">
          <i class="fas fa-print"></i> Generate Invoice
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Banks are now loaded directly from controller, no AJAX needed

function generateInvoice() {
    console.log('generateInvoice() called');

    var invoiceType = $('input[name="invoice_type"]:checked').val();
    var bankId = $('#bank_select').val();
    var includeSO = $('#include_so').is(':checked');

    console.log('Invoice Type:', invoiceType);
    console.log('Bank ID:', bankId);
    console.log('Include SO:', includeSO);

    // Validate invoice type selection
    if (!invoiceType) {
        alert('Please select an invoice type (Proforma or Production Order)');
        return;
    }

    // Validate bank selection (not required for production orders)
    if (!bankId && invoiceType !== 'production') {
        alert('Please select a bank');
        return;
    }

    // Get selected bank details (not needed for production orders)
    var bankDetails = null;
    if (invoiceType !== 'production') {
        var selectedOption = $('#bank_select option:selected');
        bankDetails = {
            title: selectedOption.data('title'),
            account: selectedOption.data('account'),
            iban: selectedOption.data('iban'),
            address: selectedOption.data('address'),
            branch: selectedOption.data('branch'),
            swift: selectedOption.data('swift')
        };

        // Validate bank details
        if (!bankDetails.title || !bankDetails.account) {
            alert('Selected bank is missing required information. Please select a different bank.');
            return;
        }
    }

    try {
        // Generate invoice based on type
        if (invoiceType === 'performa') {
            printPerformaInvoice(bankDetails, includeSO);
        } else if (invoiceType === 'production') {
            printProductionOrder();
        } else {
            alert('Invalid invoice type selected');
            return;
        }

        // Close modal only after successful invoice generation
        $('#invoiceModal').modal('hide');

    } catch (error) {
        console.error('Error generating invoice:', error);
        alert('Error generating invoice. Please try again.');
    }
}

function printPerformaInvoice(bankDetails, includeSO) {
    console.log('printPerformaInvoice() called');
    var title = 'Proforma Invoice';
    var content = generateInvoiceContent(title, bankDetails, includeSO);
    console.log('Generated content length:', content.length);
    printInvoice(content, title);
}

function printProductionOrder() {
    console.log('printProductionOrder() called');
    var title = 'Production Order';
    var content = generateProductionOrderContent();
    console.log('Generated content length:', content.length);
    printInvoice(content, title);
}

function generateInvoiceContent(invoiceType, bankDetails, includeSO) {
    // Get order information
    var orderInfo = document.querySelector('.card-body');
    var orderTable = document.querySelector('.table.table-sm.table-striped');

    // Clone the order info to modify it
    var modifiedOrderInfo = orderInfo.cloneNode(true);

    // Remove duplicate statement of origin from the cloned content
    var duplicateSO = modifiedOrderInfo.querySelector('.border.p-3.bg-light');
    if (duplicateSO && duplicateSO.parentNode) {
        duplicateSO.parentNode.parentNode.remove(); // Remove the entire row containing SO
    }

    // Modify the table to remove unwanted columns for printing
    var table = modifiedOrderInfo.querySelector('.table.table-sm.table-striped');
    if (table) {
        // Remove orderStatus, productStage, unit, PKR price columns (indices 3, 4, 6, 7)
        var headerRow = table.querySelector('thead tr');
        var footerRow = table.querySelector('tfoot tr');

        if (headerRow) {
            // Remove headers: Product Stage (3), Unit (5), Exchange (Pkr) (9), Price (Pkr) (10)
            var headers = headerRow.querySelectorAll('th');
            if (headers[10]) headers[10].remove(); // Price (Pkr)
            if (headers[9]) headers[9].remove(); // Exchange (Pkr)
            if (headers[5]) headers[5].remove(); // Unit
            if (headers[3]) headers[3].remove(); // Product Stage
        }

        // Remove corresponding data cells from body rows
        var bodyRows = table.querySelectorAll('tbody tr');
        bodyRows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            if (cells[10]) cells[10].remove(); // Price (Pkr)
            if (cells[9]) cells[9].remove(); // Exchange (Pkr)
            if (cells[5]) cells[5].remove(); // Unit
            if (cells[3]) cells[3].remove(); // Product Stage
        });

        // Update footer colspan
        if (footerRow) {
            var footerCells = footerRow.querySelectorAll('th');
            if (footerCells[0]) {
                footerCells[0].setAttribute('colspan', '6'); // Adjust colspan after removing columns (was 10, now 6 after removing 4 columns)
            }
        }
    }

    // Build bank details section
    var bankSection = '<div class="bank-details" style="margin: 20px 0; padding: 15px; border: 1px solid #ddd; background-color: #f9f9f9;">';
    bankSection += '<h4>Bank Details</h4>';
    bankSection += '<p><strong>Account Title:</strong> ' + bankDetails.title + '</p>';
    bankSection += '<p><strong>Account Number:</strong> ' + bankDetails.account + '</p>';
    if (bankDetails.iban) {
        bankSection += '<p><strong>IBAN:</strong> ' + bankDetails.iban + '</p>';
    }
    if (bankDetails.swift) {
        bankSection += '<p><strong>SWIFT Code:</strong> ' + bankDetails.swift + '</p>';
    }
    if (bankDetails.branch) {
        bankSection += '<p><strong>Branch Code:</strong> ' + bankDetails.branch + '</p>';
    }
    if (bankDetails.address) {
        bankSection += '<p><strong>Bank Address:</strong> ' + bankDetails.address + '</p>';
    }
    bankSection += '</div>';

    // Get statement of origin if included (only at the end)
    var soSection = '';
    if (includeSO) {
        var soElement = document.querySelector('.border.p-3.bg-light');
        if (soElement) {
            soSection = '<div class="so-section" style="margin: 20px 0;"><h4>Statement of Origin</h4><div class="border p-3 bg-light">' + soElement.innerHTML + '</div></div>';
        }
    }

    // Combine all content
    var content = '<div class="invoice-header" style="text-align: center; margin-bottom: 30px;"><h2>' + invoiceType + '</h2></div>';
    content += modifiedOrderInfo.innerHTML;
    content += bankSection;
    content += soSection;

    return content;
}

function generateProductionOrderContent() {
    // Get order information but exclude customer details, order number, and pricing
    var orderTable = document.querySelector('.table.table-sm.table-striped');

    // Create production order content without customer details and pricing
    var content = '<div class="invoice-header" style="text-align: center; margin-bottom: 30px;"><h2>Production Order</h2></div>';

    // Add basic order info (without order number and customer details)
    content += '<div style="margin: 20px 0;">';
    content += '<div style="display: flex; justify-content: space-between;">';
    content += '<div style="width: 48%;">';
    content += '<h3 style="margin-top: 0; margin-bottom: 10px; font-size: 14px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Production Information</h3>';
    content += '<p><strong>Job No:</strong> {{$order["job_no"]}}</p>';
    content += '<p><strong>Order Date:</strong> {{$order["order_date"]}}</p>';
    @if($order['due_date'])
    content += '<p><strong>Due Date:</strong> {{$order["due_date"]}}</p>';
    @endif
    content += '</div>';
    content += '<div style="width: 48%;">';
    content += '<h3 style="margin-top: 0; margin-bottom: 10px; font-size: 14px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Production Details</h3>';
    @if($order['description'])
    content += '<p><strong>Description:</strong></p>';
    content += '<div style="border: 1px solid #ddd; padding: 10px; margin-top: 5px;">@php echo $order["description"] @endphp</div>';
    @endif
    content += '</div>';
    content += '</div>';
    content += '</div>';

    // Add items table without pricing columns
    if (orderTable) {
        var clonedTable = orderTable.cloneNode(true);

        // Remove price-related columns from header
        var headerRow = clonedTable.querySelector('thead tr');
        if (headerRow) {
            var headers = headerRow.querySelectorAll('th');
            // Remove Unit Price and Total columns (typically last 2 columns)
            if (headers.length >= 2) {
                headers[headers.length - 1].remove(); // Total
                headers[headers.length - 2].remove(); // Unit Price
            }
        }

        // Remove price-related columns from body rows
        var bodyRows = clonedTable.querySelectorAll('tbody tr');
        bodyRows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            if (cells.length >= 2) {
                cells[cells.length - 1].remove(); // Total
                cells[cells.length - 2].remove(); // Unit Price
            }
        });

        content += clonedTable.outerHTML;
    }

    return content;
}

function printInvoice(content, title) {
    console.log('printInvoice() called with title:', title);

    // Fetch company data and generate print document
    fetch('/company/data')
        .then(response => response.json())
        .then(company => {
            generateOrderPrintDocument(content, title, company);
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
            generateOrderPrintDocument(content, title, defaultCompany);
        });
}

function generateOrderPrintDocument(content, title, company) {
    console.log('generateOrderPrintDocument() called with title:', title);
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
                    .btn, .card-header-action, .modal { display: none !important; }
                    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .badge { padding: 3px 6px; border-radius: 3px; font-size: 12px; }
                    .badge-warning { background-color: #ffc107; color: #212529; }
                    .badge-success { background-color: #28a745; color: white; }
                    .badge-danger { background-color: #dc3545; color: white; }
                    .print-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px; }
                    .print-header img { max-height: 80px; margin-bottom: 10px; }
                    .print-header h1 { margin: 0; font-size: 24px; display: none; }
                    .print-header p { margin: 5px 0; font-size: 14px; display: none; }
                    .page-title { text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0; }
                    .bank-details { margin: 20px 0; padding: 15px; border: 1px solid #ddd; background-color: #f9f9f9; }
                    .bank-details h4 { margin-top: 0; }
                    .so-section { margin: 20px 0; }
                    .so-section h4 { margin-bottom: 10px; }
                    .print-footer { position: fixed; bottom: 20px; left: 0; right: 0; text-align: center; font-size: 12px; border-top: 1px solid #000; padding-top: 10px; background: white; }
                }
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .badge { padding: 3px 6px; border-radius: 3px; font-size: 12px; }
                .badge-warning { background-color: #ffc107; color: #212529; }
                .badge-success { background-color: #28a745; color: white; }
                .badge-danger { background-color: #dc3545; color: white; }
                .print-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px; }
                .print-header h1 { margin: 0; font-size: 24px; }
                .print-header p { margin: 5px 0; font-size: 14px; }
                .page-title { text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0; }
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
    // <div class="page-title">${title}</div>
    
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