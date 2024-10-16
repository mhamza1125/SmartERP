@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Issuance Group</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('igroup.update', $igroup['igroup_id']) }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Group Name</label>
                    <input type="text" class="form-control" name="igroup_no" value="{{$igroup['igroup_no']}}" required>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Group Name</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Date</label>
                    <input type="text" class="form-control datepicker" name="igroup_date" required value="{{$igroup['igroup_date']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Issuance For Orders</label>
                    <select class="form-control select2" name="order_id" id="order_id" required>
                      {{-- <option value="0" selected>Default Issuance</option> --}}
                      <option value="" selected disabled>Select Order</option>
                      @if($order->count())
                        @foreach($order as $item)
                          <option value="{{$item->order_id}}" {{ $igroup['order_id'] == $item->order_id ? 'selected' : '' }}>{{$item->job_no}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Products</label>
                    <select class="form-control select2" name="product_type_id" id="product_type_id">
                      <!-- Options will be dynamically added here via JavaScript -->
                      <option value="" disabled>Select Product</option>
                      <!-- You can keep this option or remove it, depending on your needs -->
                  </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="material_id" id="material_id">
                      <!-- Options will be dynamically added here via JavaScript -->
                      <option value="" disabled selected>Select Material</option>
                      <!-- You can keep this option or remove it, depending on your needs -->
                    </select>
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" step="0.0000000001" min="0" class="form-control" name="quantityMaterial" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
                    <button type="button" id="addBtnMaterial" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product</label>
                    <select class="form-control select2" name="stage_id" id="stage_id">
                      <!-- Options will be dynamically added here via JavaScript -->
                      <option value="" disabled>Select Product</option>
                      <!-- You can keep this option or remove it, depending on your needs -->
                    </select>
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" step="0.0000000001" min="0" class="form-control" name="quantityStage" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
                    <button type="button" id="addBtnStage" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <table class="table" id="items-table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Item / Product</th>
                        <th>Material / Stage</th>
                        <th>Quantity</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($igroupItem->count())
                        @foreach($igroupItem as $item)
                          <tr>
                            <td></td>
                            <td>{{$item->article_no}} - Size {{$item->sname}}
                              <input type="hidden" name="product_type_id[]" value="{{$item->product_type_id}}">
                              <input type="hidden" name="stage_id[]" value="{{$item->stage_id}}">
                            </td>
                            <td>@if($item->material_id){{$item->name}}
                              <input type="hidden" name="material_id[]" value="{{$item->material_id}}">
                              @else{{$item->stage}}<input type="hidden" name="material_id[]" value="0">@endif
                            </td>
                            <td>{{$item->quantity}}
                              <input type="hidden" name="quantity[]" value="{{$item->quantity}}"></td>
                            </td>
                            <td>@if($item->material_id)
                              <button class="deleteRow btn btn-danger">X</button>
                              @else <button class="deletepRow btn btn-danger">X</button>@endif</td>
                          </tr>
                        @endforeach
                      @endif
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Sr.</th>
                        <th>Item / Product</th>
                        <th>Material / Stage</th>
                        <th>Quantity</th>
                        <th>Action</th>
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
<script>
  var isIssuePage = false;
  var isIGroupPage = false;
  var ajaxIGUrl = "{{ route('ajaxIG') }}";
  var ajaxPTUrl = "{{ route('ajaxPT') }}";
  var ajaxPMUrl = "{{ route('ajaxPM') }}";
  var ajaxPSUrl = "{{ route('ajaxPS') }}";
  var ajaxMQtyUrl = "{{ route('ajaxMQty') }}";
  var ajaxAMQtyUrl = "{{ route('ajaxAMQty') }}";
  var ajaxATMQtyUrl = "{{ route('ajaxATMQty') }}";
</script>
@endsection