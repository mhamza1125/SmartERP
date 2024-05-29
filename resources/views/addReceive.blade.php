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
                    {{-- <input type="text" class="form-control datepicker" name="receive_date" required value="{{old('receive_date')}}"> --}}
                    <input type="date" class="form-control" name="receive_date" required value="{{date('Y-m-d')}}" min="{{$purchase['purchase_date']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Purchase / Processing No</label>
                    <input type="text" name="purchase_id" hidden value="{{ $purchase['purchase_id'] }}">
                    <input type="text" class="form-control" readonly value="{{ $purchase['purchase_no'] }}">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Purchase / Processing For Order</label>
                    <input type="text" class="form-control" readonly value="{{( $purchase['job_no'])? $purchase['job_no'] : 'Default Purchase / Processing' }}">
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
                        <th>Pending</th>
                        <th>Approved</th>
                        <th>Rejected</th>
                        <th>Remaining</th>
                        <th>Inspection Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($purchaseItem->count())
                        @foreach($purchaseItem as $item)
                        <tr>
                          <td>{{ $loop->index + 1 }}</td>
                          <td class="form-group">{{$item->material_no}} - {{$item->name}}
                            <input type="hidden" name="purchase_item_id[]" value="{{$item->purchase_item_id}}">
                          </td>
                          <td class="form-group">
                            {{-- <span class="received">{{$item->received}}</span> / <span class="total">{{$item->quantity}}</span> --}}
                            <span class="received">{{$item->received - $item->returned}}</span> / <span class="total">{{$item->quantity}}</span>
                          </td>
                          <td class="form-group">
                            <input type="number" class="receive-qty form-control" name="quantity[]" placeholder="0" style="width:100px">
                          </td>
                          <td class="form-group">
                            <input type="number" class="pending_qty form-control" name="pending_qty[]" placeholder="0" style="width:100px">
                          </td>
                          <td class="form-group">
                            <input type="number" class="approved_qty form-control" name="approved_qty[]" placeholder="0" style="width:100px">
                          </td>
                          <td class="form-group">
                            <input type="number" class="rejected_qty form-control" name="rejected_qty[]" placeholder="0" style="width:100px">
                          </td>
                          <td class="form-group">
                            {{-- <input type="text" class="remaining form-control" value="{{$item->quantity - $item->received}}" readonly> --}}
                            <input type="text" class="remaining form-control" value="{{$item->quantity - $item->received + $item->returned}}" style="width:100px" readonly>
                          </td>
                          <td>
                            <input type="text" class="form-control datepicker" name="inspection_date[]" required>
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
                        <th>Pending</th>
                        <th>Approved</th>
                        <th>Rejected</th>
                        <th>Remaining</th>
                        <th>Inspection Date</th>
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
<script> var isReceivePage = false; </script>
@endsection