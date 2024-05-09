@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Transaction Table</h4>
            <div class="card-header-action">
              {{-- <a href="{{ route('transaction') }}" class="btn btn-primary">Add</a> --}}
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Transaction</th>
                    <th>Transaction Type</th>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Date</th>
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
                          <td>{{ucfirst($item->transaction_to)}}</td>
                          <td>{{ucfirst($item->transaction_type)}}</td>
                          <td>{{isset($item->debit) ? number_format($item->debit) : ''}}</td>
                          <td>{{isset($item->credit) ? number_format($item->credit) : ''}}</td>
                          <td>{{$item->transaction_date}}</td>
                          <td>
                            @if($item->transaction_to == 'employee')
                              <a href="{{ route('transaction.showEPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            @elseif($item->transaction_to == 'vendor')
                              <a href="{{ route('transaction.showVPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            @elseif($item->transaction_to == 'expense')
                              <a href="{{ route('transaction.showExpense', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            @elseif($item->transaction_to == 'customer')
                              <a href="{{ route('transaction.showOPayment', $item->transaction_id) }}" class="btn btn-info btn-sm">View</a>
                            @elseif($item->transaction_to == 'brs')
                              <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal{{$item->transaction_id}}">Edit</button>
                            @else
                              <a href="#" class="btn btn-info btn-sm">None</a>
                            @endif
                          </td>
                        </tr>
                      @endunless
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Transaction</th>
                    <th>Transaction Type</th>
                    <th>Debit</th>
                    <th>Credit</th>
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


@if($transaction->count())
@php $index = 1 @endphp
  @foreach($transaction as $item)
    @if($item->transaction_to == 'brs')
    <div class="modal fade" id="exampleModal{{$item->transaction_id}}" tabindex="-1" role="dialog" aria-labelledby="formModal"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="formModal">Edit BRS</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="{{ route('transaction.update', $item->transaction_id) }}" method="POST" class="needs-validation" novalidate=""> @csrf
              <div class="card-body">
                <div class="form-group">
                  <label>Amount</label>
                  @if($item->debit > 0)
                    <input type="number" min="0" class="form-control" name="debit" value="{{$item->debit}}" required>
                  @else
                    <input type="number" min="0" class="form-control" name="debit" value="{{$item->credit}}" required>
                  @endif
                  <div class="valid-feedback">Good job!</div>
                  <div class="invalid-feedback">Enter Amount</div>
                </div>
              </div>
              <div class="card-footer text-right">
                <button class="btn btn-primary">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    @endif
  @endforeach
@endif
@endsection