@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Expense Table</h4>
            <div class="card-header-action">
              <a href="{{ route('transaction.addExpense') }}" class="btn btn-primary">Add Expense</a>
            </div>
          </div>
          <div class="card-body">
            {{-- Filter Form --}}
            <form action="{{ route('expense.filter') }}" method="POST" class="needs-validation col-md-12 mb-3" novalidate="">
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
                  <label>Expense Head</label>
                  <select class="form-control select2" name="head_id">
                    <option value="">All Expense Heads</option>
                    @foreach($heads as $head)
                      <option value="{{ $head->head_id }}" {{ (isset($head_id) && $head_id == $head->head_id) ? 'selected' : '' }}>
                        {{ $head->name }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-2 mt-4">
                  <button class="btn btn-primary mt-2" type="submit">Filter</button>
                  @if(!empty($dfrom) || !empty($dto) || !empty($head_id))
                    <a href="{{ route('expense') }}" class="btn btn-secondary mt-2">Clear</a>
                  @endif
                </div>
              </div>
            </form>

            @if(!empty($dfrom) || !empty($dto) || !empty($head_id))
              <div class="alert alert-info">
                <strong>Filtered Results:</strong>
                @if(!empty($dfrom) && !empty($dto))
                  Showing expenses from {{ date("d F Y", strtotime($dfrom)) }} to {{ date("d F Y", strtotime($dto)) }}
                @endif
                @if(!empty($head_id))
                  @php
                    $selectedHead = $heads->firstWhere('head_id', $head_id);
                  @endphp
                  @if($selectedHead)
                    for expense head: {{ $selectedHead->name }}
                  @endif
                @endif
              </div>
            @endif

            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Voucher No</th>
                    <th>Description</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Balance</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                    $totalDebit = 0;
                    $totalCredit = 0;
                    $balance = 0;
                  @endphp
                  @if($transaction->count())
                    @foreach($transaction as $item)
                    @php
                      $debit = $item->debit ?? 0;
                      $credit = $item->credit ?? 0;
                      $totalDebit += $debit;
                      $totalCredit += $credit;
                      // Balance calculation: Balance = Previous Balance + Debit - Credit
                      $balance += $credit - $debit;
                    @endphp
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->transaction_date}}</td>
                      <td>SSL-{{ date('Y') }}-{{ str_pad($item->transaction_id, 4, '0', STR_PAD_LEFT) }}</td>
                      <td>{{$item->name}}</td>
                      <td class="text-right">{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                      <td class="text-right">{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                      <td class="text-right">{{ number_format($balance, 2) }}</td>
                      <td>
                        <a href="{{ route('transaction.showExpense', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('transaction.editExpense', $item->transaction_id) }}" class="btn btn-primary btn-sm">Edit</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr style="background-color: #f5f5f5; font-weight: bold;">
                    <th colspan="4" class="text-right">Total:</th>
                    <th class="text-right">{{ number_format($totalCredit, 2) }}</th>
                    <th class="text-right">{{ number_format($totalDebit, 2) }}</th>
                    <th class="text-right">{{ number_format($balance, 2) }}</th>
                    <th></th>
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