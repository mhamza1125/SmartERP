@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            {{-- Vendor / Contractor Payment Table --}}
            <h4>Vendor Payment Table</h4>
            <div class="card-header-action">
              <a href="{{ route('transaction.addVPayment') }}" class="btn btn-primary">Add Payment</a>
            </div>
          </div>
          <div class="card-body">
            {{-- Filter Form --}}
            <form action="{{ route('vPayment.filter') }}" method="POST" class="needs-validation col-md-12 mb-3" novalidate="">
              @csrf
              <div class="row">
                <div class="form-group col-md-3">
                  <label>Date From</label>
                  <input type="text" class="form-control datepicker" name="dfrom" value="{{$dfrom ?? ''}}">
                </div>
                <div class="form-group col-md-3">
                  <label>Date To</label>
                  <input type="text" class="form-control datepicker" name="dto" value="{{$dto ?? ''}}">
                </div>
                <div class="form-group col-md-4">
                  <label>Vendor</label>
                  <select class="form-control select2" name="vendor_id">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $vendor)
                      <option value="{{ $vendor->vendor_id }}" {{ (isset($vendor_id) && $vendor_id == $vendor->vendor_id) ? 'selected' : '' }}>
                        {{ $vendor->vendor_no }} - {{ $vendor->fname }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-2 mt-4">
                  <button class="btn btn-primary mt-2" type="submit">Filter</button>
                  @if(!empty($dfrom) || !empty($dto) || !empty($vendor_id))
                    <a href="{{ route('vPayment') }}" class="btn btn-secondary mt-2">Clear</a>
                  @endif
                </div>
              </div>
            </form>

            @if(!empty($dfrom) || !empty($dto) || !empty($vendor_id))
              <div class="alert alert-info">
                <strong>Filtered Results:</strong>
                @if(!empty($dfrom) && !empty($dto))
                  Showing payments from {{ date("d F Y", strtotime($dfrom)) }} to {{ date("d F Y", strtotime($dto)) }}
                @endif
                @if(!empty($vendor_id))
                  @php
                    $selectedVendor = $vendors->firstWhere('vendor_id', $vendor_id);
                  @endphp
                  @if($selectedVendor)
                    for vendor: {{ $selectedVendor->vendor_no }} - {{ $selectedVendor->fname }}
                  @endif
                @endif
              </div>
            @endif

            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Voucher No</th>
                    <th>Date</th>
                    <th>Vendor</th>
                    <th>Payment Type</th>
                    <th>Amount</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($transaction->count())
                  @php $index = 1 @endphp
                    @foreach($transaction as $item)
                      @unless($item->transaction_type == 'openingBalance')
                        <tr>
                          <td>{{$index++}}</td>
                          <td>TXN-{{ date('Y') }}-{{ str_pad($item->transaction_id, 4, '0', STR_PAD_LEFT) }}</td>
                          <td>{{$item->transaction_date}}</td>
                          <td>{{$item->vendor_no}} - {{$item->fname}}</td>
                          <td>{{ucfirst($item->transaction_type)}}</td>
                          <td>{{number_format($item->debit ? $item->debit : $item->credit)}}</td>
                          <td>
                            <a href="{{ route('transaction.showVPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('transaction.editVPayment', $item->transaction_id) }}" class="btn btn-primary btn-sm">Edit</a>
                          </td>
                        </tr>
                      @endunless
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Voucher No</th>
                    <th>Date</th>
                    <th>Vendor</th>
                    <th>Payment Type</th>
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