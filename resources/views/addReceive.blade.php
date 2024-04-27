@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Receiving</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('receive.store', $purchase['purchase_id']) }}" method="POST" class="needs-validation" novalidate="" id="makeZero">
              @csrf
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Receive No</label>
                    <input type="text" class="form-control" name="receive_no" required value="{{$count}}-{{$purchase['purchase_no']}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Receive No</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Receiving Date</label>
                    <input type="text" class="form-control datepicker" name="receive_date" required value="{{old('receive_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Purchase No</label>
                    <input type="text" name="purchase_id" hidden value="{{ $purchase['purchase_id'] }}">
                    <input type="text" class="form-control" readonly value="{{ $purchase['purchase_no'] }}">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Purchase For Orders</label>
                    <input type="text" class="form-control" readonly value="{{( $purchase['job_no'])? $purchase['job_no'] : 'Default Purchase' }}">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Vendor</label>
                    <input type="text" class="form-control" readonly value="{{ $purchase['fname'] }}">
                  </div>
                </div>
              </div>

              <h6>Purchase Items</h6>
              <div class="row">
                <div class="col-md-12">
                  <table class="table" id="items-table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Item / Material</th>
                        <th>Received / Total</th>
                        <th>Receive</th>
                        <th>Remaining</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($purchaseItem->count())
                        @foreach($purchaseItem as $item)
                        <tr>
                            <td>{{ $loop->index + 1 }}</td>
                            <td class="form-group">{{$item->name}}
                              <input type="hidden" name="purchase_item_id[]" value="{{$item->purchase_item_id}}">
                            </td>
                            <td class="form-group">
                              <span class="received">{{$item->received}}</span> / <span class="total">{{$item->quantity}}</span>
                            </td>
                            <td class="form-group">
                              <input type="number" class="receive-qty form-control" name="quantity[]" placeholder="0">
                            </td>
                            <td class="form-group">
                              <input type="text" class="remaining form-control" value="{{$item->quantity - $item->received}}" readonly>
                            </td>
                            <td class="form-group">
                              <select class="form-control" name="inspection_status[]" required>
                                <option value="1" selected>Pending</option>
                                <option value="2">Approved</option>
                                <option value="3">Rejected</option>
                              </select>
                            </td>
                        </tr>
                        @endforeach
                      @endif
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Item / Material</th>
                        <th>Received / Total</th>
                        <th>Receive</th>
                        <th>Remaining</th>
                        <th>Status</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" name="description">{{old('description')}}</textarea>
                  </div>
                </div>
              </div>
              <div class="form-group row mb-4">
                <div class="col-md-12 text-right">
                  <button class="btn btn-primary" type="submit" onclick="return submits()">Submit</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection