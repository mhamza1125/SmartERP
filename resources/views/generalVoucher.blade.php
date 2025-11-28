@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>General Voucher Table</h4>
            <div class="card-header-action">
              <a href="{{ route('transaction.addGeneralVoucher') }}" class="btn btn-primary">Add General Voucher</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Voucher No</th>
                    <th>Date</th>
                    <th>Payee Type</th>
                    <th>Payee Name</th>
                    <th>Amount</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($transaction->count())
                  @php $index = 1 @endphp
                    @foreach($transaction as $item)
                      <tr>
                        <td>{{$index++}}</td>
                        <td>SSL-{{ date('Y') }}-{{ str_pad($item->transaction_id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>{{$item->transaction_date}}</td>
                        <td>
                          @if($item->transaction_to == 'vendor')
                            Vendor
                          @elseif($item->transaction_to == 'contractor')
                            Contractor
                          @elseif($item->transaction_to == 'employee')
                            Employee
                          @elseif($item->transaction_to == 'customer')
                            Customer
                          @else
                            {{ ucfirst($item->transaction_to) }}
                          @endif
                        </td>
                        <td>
                          @if($item->transaction_to == 'vendor' && $item->vendor_no)
                            {{$item->vendor_no}} - {{$item->vendor_name ?? $item->fname}}
                          @elseif($item->transaction_to == 'contractor' && $item->vendor_no)
                            {{$item->vendor_no}} - {{$item->vendor_name ?? $item->fname}}
                          @elseif($item->transaction_to == 'employee' && $item->employee_no)
                            {{$item->employee_no}} - {{$item->employee_name ?? $item->name}}
                          @elseif($item->transaction_to == 'customer' && $item->customer_no)
                            {{$item->customer_no}} - {{$item->customer_name ?? $item->fname}}
                          @else
                            N/A
                          @endif
                        </td>
                        <td>{{number_format($item->debit ? $item->debit : $item->credit)}}</td>
                        <td>
                          <a href="{{ route('transaction.showGeneralVoucher', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                          <a href="{{ route('transaction.editGeneralVoucher', $item->transaction_id) }}" class="btn btn-primary btn-sm">Edit</a>
                          <a href="{{ route('payment.print', $item->transaction_id) }}" class="btn btn-warning btn-sm" target="_blank">Print</a>
                        </td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Voucher No</th>
                    <th>Date</th>
                    <th>Payee Type</th>
                    <th>Payee Name</th>
                    <th>Amount</th>
                    <th>Action</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

