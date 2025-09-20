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
                    <tr><td><b>Order No</b> {{$order['order_no']}}</td></tr>
                    <tr><td><b>Job No:</b> {{$order['job_no']}}</td></tr>
                    <tr><td><b>Date:</b> {{$order['order_date']}}</td></tr>
                    @if($order['due_date'])
                    <tr><td><b>Due Date:</b> {{$order['due_date']}}</td></tr>
                    @endif
                    @if($order['payment_terms'])
                    <tr><td><b>Payment Terms:</b> {{$order['payment_terms']}}</td></tr>
                    @endif
                    @if($order['fi_no'])
                    <tr><td><b>FI No:</b> {{$order['fi_no']}}</td></tr>
                    @endif
                    @if($order['rex_no'])
                    <tr><td><b>REX No:</b> {{$order['rex_no']}}</td></tr>
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
                      <th colspan="9"></th>
                      <th>Grand Total:</th>
                      <th>{{ number_format($total) }}</th>
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
                    <input class="form-check-input" type="radio" name="invoice_type" id="commercial" value="commercial" checked>
                    <label class="form-check-label" for="commercial">
                      Commercial Invoice
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="invoice_type" id="performa" value="performa">
                    <label class="form-check-label" for="performa">
                      Performa Invoice
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
        alert('Please select an invoice type (Commercial or Performa)');
        return;
    }

    // Validate bank selection
    if (!bankId) {
        alert('Please select a bank');
        return;
    }

    // Get selected bank details
    var selectedOption = $('#bank_select option:selected');
    var bankDetails = {
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

    try {
        // Generate invoice based on type
        if (invoiceType === 'commercial') {
            printCommercialInvoice(bankDetails, includeSO);
        } else if (invoiceType === 'performa') {
            printPerformaInvoice(bankDetails, includeSO);
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

function printCommercialInvoice(bankDetails, includeSO) {
    console.log('printCommercialInvoice() called');
    var title = 'Commercial Invoice';
    var content = generateInvoiceContent(title, bankDetails, includeSO);
    console.log('Generated content length:', content.length);
    printInvoice(content, title);
}

function printPerformaInvoice(bankDetails, includeSO) {
    console.log('printPerformaInvoice() called');
    var title = 'Performa Invoice';
    var content = generateInvoiceContent(title, bankDetails, includeSO);
    console.log('Generated content length:', content.length);
    printInvoice(content, title);
}

function generateInvoiceContent(invoiceType, bankDetails, includeSO) {
    // Get order information
    var orderInfo = document.querySelector('.card-body');
    var orderTable = document.querySelector('.table.table-sm.table-striped');

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

    // Get statement of origin if included
    var soSection = '';
    if (includeSO) {
        var soElement = document.querySelector('.border.p-3.bg-light');
        if (soElement) {
            soSection = '<div class="so-section" style="margin: 20px 0;"><h4>Statement of Origin</h4><div class="border p-3 bg-light">' + soElement.innerHTML + '</div></div>';
        }
    }

    // Combine all content
    var content = '<div class="invoice-header" style="text-align: center; margin-bottom: 30px;"><h2>' + invoiceType + '</h2></div>';
    content += orderInfo.innerHTML;
    content += bankSection;
    content += soSection;

    return content;
}

function printInvoice(content, title) {
    console.log('printInvoice() called with title:', title);
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
                    body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
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
                    .print-header h1 { margin: 0; font-size: 24px; }
                    .print-header p { margin: 5px 0; font-size: 14px; }
                    .page-title { text-align: center; font-size: 20px; font-weight: bold; margin: 20px 0; }
                    .bank-details { margin: 20px 0; padding: 15px; border: 1px solid #ddd; background-color: #f9f9f9; }
                    .bank-details h4 { margin-top: 0; }
                    .so-section { margin: 20px 0; }
                    .so-section h4 { margin-bottom: 10px; }
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
                <h1>SmartERP</h1>
                <p>Near Sachi Sarkar Darbar, Opposite Qayyum Elahi Surgical, Harrar Sialkot, Pakistan</p>
                <p>Phone no. +92 52 357 3727 || E-mail: info@sajjadsonlab.com || Web: sajjadsonlab.com</p>
            </div>
            <div class="page-title">${title}</div>
            <div class="print-content">
                ${content}
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