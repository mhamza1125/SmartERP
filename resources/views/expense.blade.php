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
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Voucher No</th>
                    <th>Date</th>
                    <th>Expense Head</th>
                    <th>Payment Type</th>
                    <th>Debit (Reversal)</th>
                    <th>Credit (Incurred)</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($transaction->count())
                    @php
                      $totalDebit = 0;
                      $totalCredit = 0;
                    @endphp
                    @foreach($transaction as $item)
                    @php
                      $debit = $item->debit ?? 0;
                      $credit = $item->credit ?? 0;
                      $totalDebit += $debit;
                      $totalCredit += $credit;
                    @endphp
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>SSL-{{ date('Y') }}-{{ str_pad($item->transaction_id, 4, '0', STR_PAD_LEFT) }}</td>
                      <td>{{$item->transaction_date}}</td>
                      <td>{{$item->name}}</td>
                      <td>{{$item->bank_id ? 'Bank Payment':'Cash Payment'}}</td>
                      <td class="text-right">{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                      <td class="text-right">{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
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
                    <th colspan="5" class="text-right">Total:</th>
                    <th class="text-right">{{ number_format($totalDebit, 2) }}</th>
                    <th class="text-right">{{ number_format($totalCredit, 2) }}</th>
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