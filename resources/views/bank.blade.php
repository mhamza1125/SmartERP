@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Bank Management</h4>
            <div class="card-header-action">
              <div class="btn-group">
                @can('create', App\Models\Bank::class)<a href="{{ route('bank.add') }}" class="btn btn-primary">Add Bank</a>@endcan
                @can('create', App\Models\Transaction::class)<a href="{{ route('transaction.addBRS') }}" class="btn btn-primary">Balance Adjustment</a>@endcan
              </div>
            </div>
          </div>
          <div class="card-body">
            {{-- One list: account details, balance and ledger (own accounts) and edit --}}
            @php $balances = isset($bankBalance) ? $bankBalance->keyBy('bank_id') : collect(); @endphp
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Bank Holder</th>
                    <th>Account Title</th>
                    <th>Account No</th>
                    <th>Bank Type</th>
                    <th>Balance</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($bank as $item)
                    @php
                      $balanceRow = $balances->get($item->bank_id);
                      $ownAccount = $balanceRow || (string) ($item->banker_id ?? '') === '0'; // company's own account
                    @endphp
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>
                        @if($item->bank_holder == 'employee')
                          {{$item->employee_no}} - {{$item->name}}
                        @elseif($item->bank_holder == 'vendor' || $item->bank_holder == 'contractor')
                          {{$item->vendor_no}} - {{$item->fname}}
                        @elseif($item->bank_holder == 'customer')
                          {{$item->customer_no}} - {{$item->cname}} {{$item->lname}}
                        @else
                          Admin / Self
                        @endif
                      </td>
                      <td>{{$item->account_title}}</td>
                      <td>{{$item->account}}</td>
                      <td>{{$item->hname}}</td>
                      <td>{{ $ownAccount ? number_format($balanceRow ? $balanceRow->tdebit - $balanceRow->tcredit : 0) : '-' }}</td>
                      <td class="text-nowrap">
                        @if($ownAccount)
                          @if(auth()->user()->can('show', App\Models\Transaction::class))<a href="{{ route('transaction.showBBalance', $item->bank_id) }}" class="btn btn-success btn-sm">Ledger</a>@endif
                        @endif
                        @can('edit', App\Models\Bank::class)<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal{{$item->bank_id}}">Edit</button>@endcan
@can('delete', App\Models\Bank::class)@include('deleteButton', ['url' => route('bank.destroy', $item->bank_id), 'check' => route('bank.deleteCheck', $item->bank_id), 'label' => 'Bank account '.$item->account_title, 'class' => 'btn btn-danger btn-sm'])@endcan
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@if($bank->count())
  @foreach($bank as $item)
    <div class="modal fade" id="exampleModal{{$item->bank_id}}" tabindex="-1" role="dialog" aria-labelledby="formModal"
      aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="formModal">Edit Bank Info</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="{{ route('bank.update', $item->bank_id) }}" method="POST" class="needs-validation" novalidate=""> @csrf
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Bank Type</label>
                      <select class="form-control select2" name="head_id" required style="width:100%;">
                        <option value="" selected disabled>Select Bank Type</option>
                        @if($head->count())
                          @foreach($head as $item2)
                            <option value="{{$item2->head_id}}" {{ $item->head_id == $item2->head_id ? 'selected' : '' }}>{{$item2->name}}</option>
                          @endforeach
                        @endif
                      </select>
                      <div class="invalid-feedback">Select Bank Type</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Bank Title</label>
                      <input type="text" class="form-control" name="account_title" value="{{$item->account_title}}" required>
                      <div class="invalid-feedback">Enter Account Title</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Bank Account</label>
                      <input type="text" class="form-control" name="account" required value="{{$item->account}}">
                      <div class="invalid-feedback">Enter Account No</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>IBAN</label>
                      <input type="text" class="form-control" name="iban" placeholder="International Bank Account Number" value="{{$item->iban ?? ''}}">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Branch Code</label>
                      <input type="text" class="form-control" name="branch_code" placeholder="Branch Code" value="{{$item->branch_code ?? ''}}">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>SWIFT Code</label>
                      <input type="text" class="form-control" name="swift_code" placeholder="SWIFT/BIC Code" value="{{$item->swift_code ?? ''}}">
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>Bank Address</label>
                      <textarea class="form-control" name="address" rows="2" placeholder="Bank Address">{{$item->baddress ?? ''}}</textarea>
                    </div>
                  </div>
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
  @endforeach
@endif
<script> var isBankPage = false; </script>
@endsection