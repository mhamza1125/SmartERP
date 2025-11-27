@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Material Ledger</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('material.filter') }}" method="POST" class="needs-validation col-md-12" novalidate="">@csrf
              <div class="row">
                <div class="col-md-5 form-group">
                  <label>Material</label>
                  <select class="form-control select2" name="material_id" required>
                    <option value="0" selected>All Materials</option>
                    @if($material->count())
                      @foreach($material as $item)
                        <option value="{{$item->material_id}}" {{ $mid == $item->material_id ? 'selected' : '' }}>{{$item->material_no}} - {{$item->name}}</option>
                      @endforeach
                    @endif
                  </select>
                  <div class="valid-feedback">Good job!</div>
                  <div class="invalid-feedback">Select Material</div>
                </div>
                <div class="form-group col-md-3">                    
                  <label>Date From</label>
                  <input type="text" class="form-control datepicker" name="dfrom" value="{{$dfrom}}" required>
                  <div class="valid-feedback">Good job!</div>
                </div>
                <div class="form-group col-md-3">                    
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
                      @php $item = $material->where('material_id', $mid)->first(); @endphp
                      <td><b>Material:</b> {{$item ? ($item->material_no . ' - ' . $item->name) : 'All'}}</td>
                      <td><b>Date From:</b> {{date("d F Y", strtotime($dfrom))}}</td>
                      <td><b>Date To:</b> {{date("d F Y", strtotime($dto))}}</td>
                      <td>
                        <a class="btn btn-info" href="{{ route('material.detail.print', ['dfrom' => $dfrom, 'dto' => $dto, 'material_id' => $mid]) }}" target="_blank">
                          <i class="fas fa-file-alt"></i> Print
                        </a>
                      </td>
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
                      <th>Date</th>
                      <th>Material No</th>
                      <th>Material</th>
                      <th>Purchase / Issuance</th>
                      <th>Stock In</th>
                      <th>Stock Out</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($materialItem->count())
                      @php $loopIndex = 1; @endphp
                      @foreach($materialItem as $item)
                        <tr>
                          <td>{{$loopIndex++}}</td>
                          <td>{{(new DateTime($item->timestamp))->format('Y-m-d')}}</td>
                          <td>{{$item->material_no}}</td>
                          <td>{{$item->name}}</td>
                          @if(isset($item->purchase_id) && !isset($item->return_material_id))
                            <td>Purchase</td>
                            <td>{{$item->total_received}} {{$item->uname}}</td>
                            <td></td>
                            {{-- <td>{{(new DateTime($item->timestamp))->format('Y-m-d')}}</td> --}}
                            <td>
                              <a href="{{ route('receive.show', $item->receive_id) }}" class="btn btn-info btn-sm">View</a>
                            </td>
                          @elseif(isset($item->purchase_id) && isset($item->return_material_id))
                            <td>Return</td>
                            <td>{{$item->total_returned}} {{$item->uname}}</td>
                            <td></td>
                            {{-- <td>{{(new DateTime($item->timestamp))->format('Y-m-d')}}</td> --}}
                            <td>
                              <a href="{{ route('return.show', $item->return_id) }}" class="btn btn-info btn-sm">View</a>
                            </td>
                          @elseif(isset($item->stock_type) && $item->stock_type == 1)
                            <td>Receive Issuance</td>
                            <td>{{$item->quantity}} {{$item->uname}}</td>
                            <td></td>
                            {{-- <td>{{(new DateTisme($item->timestamp))->format('Y-m-d')}}</td> --}}
                            <td>
                              <a href="{{ route('rstock.show', $item->stock_id) }}" class="btn btn-info btn-sm">View</a>
                            </td>
                          @elseif(isset($item->stock_type) && $item->stock_type == 2)
                            <td>Issuance</td> 
                            <td></td>
                            <td>{{$item->quantity}} {{$item->uname}}</td>
                            {{-- <td>{{(new DateTime($item->timestamp))->format('Y-m-d')}}</td> --}}
                            <td>
                              <a href="{{ route('stock.show', $item->stock_id) }}" class="btn btn-info btn-sm">View</a>
                            </td>
                          @endif
                        </tr>
                      @endforeach
                    @else
                      <tr>
                        <td valign="top" colspan="8" class="dataTables_empty text-center">No data available in table</td>
                      </tr>
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Date</th>
                      <th>Material No</th>
                      <th>Material</th>
                      <th>Purchase / Issuance</th>
                      <th>Stock In</th>
                      <th>Stock Out</th>
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
  </div>
</section>
<script> var isReportPage = false; </script>
@endsection