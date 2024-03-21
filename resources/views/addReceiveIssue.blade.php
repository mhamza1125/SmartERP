@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Receive Issuance</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('stock.store', $issue['stock_id']) }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Receiving Issuance No</label>
                    <input type="hidden" name="stock_type" required value="1">
                    <input type="hidden" name="receive_issue_id" required value="{{$issue['stock_id']}}">
                    <input type="text" class="form-control" name="stock_no" required value="{{$issue['stock_no']}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Purchase For Orders</label>
                    <input type="hidden" name="order_id" required value="{{$issue['order_id']}}">
                    <input type="text" class="form-control" required value="{{$issue['job_no']}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Employee</label>
                    <input type="hidden" name="employee_id" required value="{{$issue['employee_id']}}">
                    <input type="text" class="form-control" required value="{{$issue['employee_no']}} - {{$issue['name']}}" readonly>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Receiving Date</label>
                    <input type="text" class="form-control datepicker" name="stock_date" required value="{{old('stock_date')}}">
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="material_id" id="material_id">
                      <option value="" disabled selected>Select Material</option>
                      @if($issueItem->count())
                          @foreach($issueItem as $item)
                              @if($item->material_id)
                                <option value="{{$item->material_id}}|{{$item->product_type_id}}">{{$item->name}} | {{$item->article_no}} - Size {{$item->sname}}</option>
                              @endif
                          @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-3">                  
                  <div class="form-group">
                    <label for="receiveable_stock">Receiveable Stock</label>
                    <input type="text" class="form-control" id="receiveable_stock" name="receiveable_stock" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantityMaterial" placeholder="0" id="quantityMaterial">
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
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Products</label>
                    <select class="form-control select2" name="product_type_id" id="product_type_id">
                      <option value="" disabled selected>Select Product</option>
                      @if($issueItem->count())
                        @php $issueItemUnique = $issueItemUnique->unique('product_type_id'); @endphp
                        @foreach($issueItemUnique as $item)
                        <option value="{{$item->product_type_id}}" {{ old('product_type_id') == $item->product_type_id ? 'selected' : '' }}>{{$item->article_no}} - Size {{$item->sname}}</option>
                        @endforeach
                      @endif
                  </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Product Stage</label>
                    <select class="form-control select2" name="sstage_id[]" id="stage_id">
                      <option value="" selected disabled>Select Product Stage</option>
                      @if($head->count())
                        @foreach($head as $item)
                          <option value="{{$item->head_id}}" {{ old('head_id') == $item->head_id ? 'selected' : '' }}>{{$item->name}}</option>
                        @endforeach
                      @endif
                    </select>
                    <div class="valid-feedback">Good job!</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantityProduct" placeholder="0" id="quantityProduct">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label> <br>
                    <button type="button" id="addBtnProduct" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">Receive Items</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="receive-tab" data-toggle="tab" href="#receive" role="tab" aria-controls="receive" aria-selected="false">Issued Items</a>
                    </li>
                  </ul>     
                  <div class="tab-content" id="myTabContent">
                    {{-- Receive Issuance --}}
                    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">      
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
                    {{-- Issuance --}}
                    <div class="tab-pane fade" id="receive" role="tabpanel" aria-labelledby="receive-tab">  
                      <table class="table table-sm table-striped">                    
                        <thead>
                          <tr>
                            <th>Sr.</th>
                            <th>Item / Product</th>
                            <th>Material / Stage</th>
                            <th>Quantity</th>
                          </tr>
                        </thead>
                        <tbody>
                          @if($issueItem->count())
                            @foreach($issueItem as $item)
                              <tr>
                                <td>{{$loop->index + 1}}</td>
                                <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                                <td>{{($item->name)? $item->name:$item->stage}}</td>
                                <td>{{$item->quantity}} {{($item->uname)? $item->uname:$item->puname}}</td>
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
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="summernote" name="description"></textarea>
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
<script>
  var isReceiveIssuePage = false;
  var issueItems = @json($issueItem);
</script>
@endsection