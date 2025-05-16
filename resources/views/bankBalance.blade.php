@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Bank Balance Table</h4>
            <div class="card-header-action">
              <a href="{{ route('bank.add') }}" class="btn btn-primary">Add Bank</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Account Title</th>
                    <th>Account No</th>
                    <th>Bank Type</th>
                    <th>Amount</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($transaction->count())
                    @foreach($transaction as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->account_title}}</td>
                      <td>{{$item->account}}</td>
                      <td>{{$item->hname}}</td>           
                      <td>{{number_format($item->tcredit - $item->tdebit)}}</td>
                      <td><a href="{{ route('transaction.showBBalance', $item->bank_id) }}" class="btn btn-success btn-sm">Ledger</a></td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Account Title</th>
                    <th>Account No</th>
                    <th>Bank Type</th>
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