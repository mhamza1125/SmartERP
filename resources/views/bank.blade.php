@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Bank Accounts Table</h4>
            <div class="card-header-action">
              <a href="{{ route('bank.add') }}" class="btn btn-primary">Add bank</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Bank Holder</th>
                    <th>Account Title</th>
                    <th>Account No</th>
                    <th>Bank Type</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($bank->count())
                    @foreach($bank as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>
                        @if($item->bank_holder == 'employee')
                          {{$item->employee_no}} - {{$item->name}}
                        @elseif($item->bank_holder == 'vendor')
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
                      <td><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal{{$item->bank_id}}">Edit</button></td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Bank Holder</th>
                    <th>Account Title</th>
                    <th>Account No</th>
                    <th>Bank Type</th>
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
@if($bank->count())
  @foreach($bank as $item)
    <div class="modal fade" id="exampleModal{{$item->bank_id}}" tabindex="-1" role="dialog" aria-labelledby="formModal"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
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
                <div class="form-group">
                  <label>Bank Types</label><br>
                  <select class="form-control select2" name="head_id" required>
                    <option value="" selected disabled>Select Bank Type</option>
                    @if($head->count())
                      @foreach($head as $item2)
                        <option value="{{$item2->head_id}}" {{ $item->head_id == $item2->head_id ? 'selected' : '' }}>{{$item2->name}}</option>
                      @endforeach
                    @endif
                  </select>
                  <div class="valid-feedback">Good job!</div>
                  <div class="invalid-feedback">Select Bank Type</div>
                </div>
                <div class="form-group">
                  <label>Bank Title</label>
                  <input type="text" class="form-control" name="account_title" value="{{$item->account_title}}" required>
                  <div class="valid-feedback">Good job!</div>
                  <div class="invalid-feedback">Enter Account Title</div>
                </div>
                <div class="form-group">
                  <label>Bank Account</label>
                  <input type="text" class="form-control" name="account" required value="{{$item->account}}">
                  <div class="valid-feedback">Good job!</div>
                  <div class="invalid-feedback">Enter Account No</div>
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