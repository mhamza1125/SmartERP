@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Return</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('return.store', $receive['receive_id']) }}" method="POST" class="needs-validation" novalidate="" id="makeZero">
              @csrf
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Return No</label>
                    <input type="text" class="form-control" name="return_no" required value="{{$count}}-{{$receive['receive_no']}}" placeholder="Return No">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Return No</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Return Date</label>
                    <input type="text" class="form-control datepicker" name="return_date" required value="{{old('return_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Purchase No</label>
                    <input type="text" class="form-control" readonly value="{{ $receive['purchase_no'] }}">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Receive No</label>
                    <input type="text" name="receive_id" hidden value="{{ $receive['receive_id'] }}">
                    <input type="text" class="form-control" readonly value="{{ $receive['receive_no'] }}">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Vendor</label>
                    <input type="text" class="form-control" readonly value="{{ $receive['fname'] }}">
                  </div>
                </div>
              </div>

              <h6>Received Items</h6>
              <div class="row">
                <div class="col-md-12">
                  <table class="table" id="items-table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Code</th>
                        <th>Material</th>
                        <th>Units</th>
                        <th>Returned / Receive Qty</th>
                        <th>Return Qty</th>
                        <th>Remarks</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($combined->count())
                        @foreach($combined as $item)
                        <tr>
                          <td>{{ $loop->index + 1 }}</td>
                          <td>{{$item->material_no}}</td>
                          <td>{{$item->name}}<input type="hidden" name="receive_material_id[]" value="{{$item->receive_material_id}}"></td>
                          <td>{{$item->hname}}</td>
                          <td>{{$item->rqty}} / {{$item->quantity}}</td>
                          <td>
                            <input type="number" class="form-control return-qty" name="quantity[]" placeholder="0">
                          </td>
                          <td><textarea class="form-control" name="remarks[]"></textarea></td>
                        </tr>
                        @endforeach
                      @endif
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Code</th>
                        <th>Material</th>
                        <th>Units</th>
                        <th>Returned / Receive Qty</th>
                        <th>Return Qty</th>
                        <th>Remarks</th>
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
                  <button class="btn btn-primary" type="submit">Submit</button>
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