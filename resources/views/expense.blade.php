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
                    <th>Expsense Head</th>
                    <th>Payment Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($transaction->count())
                    @foreach($transaction as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->name}}</td>
                      <td>{{$item->bank_id ? 'Bank Payment':'Cash Payment'}}</td>
                      <td>{{number_format($item->debit ? $item->debit : $item->credit)}}</td>                  
                      <td>{{$item->transaction_date}}</td>
                      <td>
                        <a href="{{ route('transaction.showExpense', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('transaction.editExpense', $item->transaction_id) }}" class="btn btn-primary btn-sm">Edit</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Expsense Head</th>
                    <th>Payment Type</th>
                    <th>Amount</th>
                    <th>Date</th>
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