@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Employee Payment Table</h4>
            <div class="card-header-action">
              <a href="{{ route('transaction.addEPayment') }}" class="btn btn-primary">Add Payment</a>
            </div>
          </div>
          <div class="card-body">
            {{-- Filter Form --}}
            <form action="{{ route('ePayment.filter') }}" method="POST" class="needs-validation col-md-12 mb-3" novalidate="">
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
                  <label>Employee</label>
                  <select class="form-control select2" name="employee_id">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                      <option value="{{ $emp->employee_id }}" {{ (isset($employee_id) && $employee_id == $emp->employee_id) ? 'selected' : '' }}>
                        {{ $emp->employee_no }} - {{ $emp->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-2 mt-4">
                  <button class="btn btn-primary mt-2" type="submit">Filter</button>
                  @if(!empty($dfrom) || !empty($dto) || !empty($employee_id))
                    <a href="{{ route('ePayment') }}" class="btn btn-secondary mt-2">Clear</a>
                  @endif
                </div>
              </div>
            </form>

            @if(!empty($dfrom) || !empty($dto) || !empty($employee_id))
              <div class="alert alert-info">
                <strong>Filtered Results:</strong>
                @if(!empty($dfrom) && !empty($dto))
                  Showing payments from {{ date("d F Y", strtotime($dfrom)) }} to {{ date("d F Y", strtotime($dto)) }}
                @endif
                @if(!empty($employee_id))
                  @php
                    $selectedEmp = $employees->firstWhere('employee_id', $employee_id);
                  @endphp
                  @if($selectedEmp)
                    for employee: {{ $selectedEmp->employee_no }} - {{ $selectedEmp->name }}
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
                    <th>Employee</th>
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
                          <td>{{$loop->index + 1}}</td>
                          <td>TXN-{{ date('Y') }}-{{ str_pad($item->transaction_id, 4, '0', STR_PAD_LEFT) }}</td>
                          <td>{{$item->transaction_date}}</td>
                          <td>{{$item->employee_no}} - {{$item->name}}</td>
                          <td>{{ucfirst($item->transaction_type)}}</td>
                          <td>{{number_format($item->debit ? $item->debit : $item->credit)}}</td>
                          <td>
                            <a href="{{ route('transaction.showEPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            <a href="{{ route('transaction.editEPayment', $item->transaction_id) }}" class="btn btn-primary btn-sm">Edit</a>
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
                    <th>Employee</th>
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