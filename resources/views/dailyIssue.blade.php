@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Daily Issuance</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('stock.filter') }}" method="POST" class="needs-validation col-md-12" novalidate="">@csrf
              <div class="row">
                <div class="col-md-3 form-group">
                  <label>Issuance For Orders</label>
                  <select class="form-control select2" name="order_id" required>
                    <option value="0" selected>All Orders</option>
                    @if($order->count())
                      @foreach($order as $item)
                        <option value="{{$item->order_id}}" {{ $oid == $item->order_id ? 'selected' : '' }}>{{$item->job_no}}</option>
                      @endforeach
                    @endif
                  </select>
                  <div class="valid-feedback">Good job!</div>
                  <div class="invalid-feedback">Select Order</div>
                </div>
                <div class="form-group col-md-4">
                  <label>Employee / Vendor</label>
                  <input type="hidden" id="table_name" name="table_name" value="{{$tname}}">
                  <select class="form-control select2" name="employee_id" id="employee_id" required>
                    <option value="0" selected>All Employee / Vendor</option>
                    @if($employee->count())
                      @foreach($employee as $item)
                        <option data-type="employee" value="{{$item->employee_id}}" {{ ($tid == $item->employee_id && $tname == 'employee') ? 'selected' : '' }}>{{$item->employee_no}} - {{$item->name}}</option>
                      @endforeach
                    @endif
                    @if($vendor->count())
                      @foreach($vendor as $item)
                        <option data-type="vendor" value="{{$item->vendor_id}}" {{ ($tid == $item->vendor_id && $tname == 'vendor') ? 'selected' : '' }}>{{$item->vendor_no}} - {{$item->fname}}</option>
                      @endforeach
                    @endif
                  </select>
                  <div class="valid-feedback">Good job!</div>
                  <div class="invalid-feedback">Select Employee / Vendor</div>
                </div>
                <div class="form-group col-md-2">                    
                  <label>Date From</label>
                  <input type="text" class="form-control datepicker" name="dfrom" value="{{$dfrom}}" required>
                  <div class="valid-feedback">Good job!</div>
                </div>
                <div class="form-group col-md-2">                    
                  <label>Date To</label>
                  <input type="text" class="form-control datepicker" name="dto" value="{{$dto}}" required>
                  <div class="valid-feedback">Good job!</div>
                </div>
                <div class="form-group col-md-1 mt-4">     
                  <button class="btn btn-primary mt-2" type="submit">Filter</button>
                </div>
              </div>
            </form>
            @if(!empty($dfrom) && !empty($dto))
            <div class="row">
              <div class="col md-12">
                <table class="table table-sm">
                  <tbody>
                    <tr>
                      <td colspan="2"></td>
                      <td><b>Date From:</b> {{date("d F Y", strtotime($dfrom))}}</td>
                      <td><b>Date To:</b> {{date("d F Y", strtotime($dto))}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            @endif
            <div class="row">
              <div class="col-md-12">
                <table class="table table-sm table-striped">              
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Article No</th>
                      <th>Item / Product</th>
                      <th>Size</th>
                      <th>Stage</th>
                      <th>Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($issueItem->count())
                    @php $index = 1; $product_id = ''; $size = '';
                      $issueItem = $issueItem->unique(function ($item) {
                        return $item->product_type_id . '|' . $item->ifname;
                      }); @endphp
                      @foreach($issueItem as $item)
                      @php $currentKey = $item->product_type_id . '|' . $item->ifname;
                        $minAvg = isset($average[$currentKey]['min_avg']) ? $average[$currentKey]['min_avg'] : '0'; @endphp
                      @if($minAvg > 0)
                        <tr>
                          <td>{{$index++}}</td>
                          @if($item->product_id == $product_id)
                              <td colspan="2"></td>
                          @else
                            <td>{{$item->article_no}}</td>
                            <td>{{$item->pname}}</td>
                          @endif
                          @if($item->sname == $size)
                            <td></td>
                          @else
                            <td>{{$item->sname}}</td>
                          @endif
                          <td>{{$item->ifname}}</td>
                          <td>{{$minAvg}} {{$item->puname}}</td>
                        </tr>
                      @endif
                      @php $product_id = $item->product_id; $size = $item->sname @endphp
                      @endforeach
                    @else
                      <tr>
                        <td valign="top" colspan="6" class="dataTables_empty text-center">No data available in table</td>
                      </tr>
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Article No</th>
                      <th>Item / Product</th>
                      <th>Size</th>
                      <th>Stage</th>
                      <th>Quantity</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script> var isReportPage = false; </script>
@endsection