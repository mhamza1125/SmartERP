@extends('index')
@section('content')
@php
  // Which permission each document link needs (a link is shown only when the user can open it)
  $linkAbility = [
    'receive.show' => App\Models\Receive::class,
    'return.show' => App\Models\Returns::class,
    'delivery.show' => App\Models\Delivery::class,
    'delivery-return.show' => App\Models\Delivery::class,
    'ptc.show' => App\Models\Stock::class,
    'stock.show' => App\Models\Stock::class,
    'rstock.show' => App\Models\Stock::class,
  ];
  $selected = $productTypeId ? $productTypes->firstWhere('product_type_id', $productTypeId) : null;
  $qty = fn ($value) => rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
@endphp
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Product Purchase Ledger</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('product.ledger.filter') }}" method="POST" class="needs-validation col-md-12" novalidate="">@csrf
              <div class="row">
                <div class="col-md-5 form-group">
                  <label>Product / Size</label>
                  <select class="form-control select2" name="product_type_id">
                    <option value="0" {{ $productTypeId ? '' : 'selected' }}>All Products</option>
                    @foreach($productTypes as $item)
                      <option value="{{ $item->product_type_id }}" {{ $productTypeId == $item->product_type_id ? 'selected' : '' }}>{{ $item->article_no }} - {{ $item->product_name }}{{ $item->size_name ? ' (' . $item->size_name . ')' : '' }}</option>
                    @endforeach
                  </select>
                  <small class="form-text text-muted">Select one size to see its opening and running balance.</small>
                </div>
                <div class="form-group col-md-3">
                  <label>Date From</label>
                  <input type="text" class="form-control datepicker" name="dfrom" value="{{ $dfrom }}" required>
                </div>
                <div class="form-group col-md-3">
                  <label>Date To</label>
                  <input type="text" class="form-control datepicker" name="dto" value="{{ $dto }}" required>
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
                      <td><b>Product:</b> {{ $selected ? ($selected->article_no . ' - ' . $selected->product_name . ($selected->size_name ? ' (' . $selected->size_name . ')' : '')) : 'All' }}</td>
                      <td><b>Date From:</b> {{ formatDate($dfrom) }}</td>
                      <td><b>Date To:</b> {{ formatDate($dto) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            @endif
            <div class="row">
              <div class="col-md-12">
                <div class="table-responsive">
                <table class="table table-sm table-striped" id="tableExport" style="width:100%;">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Date</th>
                      <th>Article No</th>
                      <th>Product</th>
                      <th>Size</th>
                      <th>Stage</th>
                      <th>Purchase / Movement</th>
                      <th>Reference</th>
                      <th class="text-right">Stock In</th>
                      <th class="text-right">Stock Out</th>
                      @if($productTypeId)<th class="text-right">Balance</th>@endif
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($productTypeId && !empty($dfrom) && !empty($dto))
                      <tr>
                        <td>1</td>
                        <td>{{ formatDate($dfrom) }}</td>
                        <td>{{ $selected->article_no ?? '' }}</td>
                        <td>{{ $selected->product_name ?? '' }}</td>
                        <td>{{ $selected->size_name ?? '' }}</td>
                        <td>-</td>
                        <td>Opening Balance</td>
                        <td>-</td>
                        <td class="text-right"></td>
                        <td class="text-right"></td>
                        <td class="text-right">{{ $qty($opening) }}</td>
                        <td></td>
                      </tr>
                    @endif
                    @php $offset = ($productTypeId && !empty($dfrom) && !empty($dto)) ? 1 : 0; $sumIn = 0; $sumOut = 0; @endphp
                    @foreach($rows as $item)
                      @php $sumIn += $item->qty_in; $sumOut += $item->qty_out; @endphp
                      <tr>
                        <td>{{ $loop->iteration + $offset }}</td>
                        <td>{{ formatDate($item->date) }}</td>
                        <td>{{ $item->article_no }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->size_name }}</td>
                        <td>{{ $item->stage_name ?? '-' }}</td>
                        <td>{{ $item->movement }}</td>
                        <td>{{ $item->reference ?? '-' }}</td>
                        <td class="text-right">{{ $item->qty_in > 0 ? $qty($item->qty_in) : '' }}</td>
                        <td class="text-right">{{ $item->qty_out > 0 ? $qty($item->qty_out) : '' }}</td>
                        @if($productTypeId)<td class="text-right">{{ $qty($item->balance) }}</td>@endif
                        <td>
                          @if($item->link_route && $item->link_id && auth()->user()->can('show', $linkAbility[$item->link_route]))
                            <a href="{{ route($item->link_route, $item->link_id) }}" class="btn btn-info btn-sm">View</a>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr>
                      <th colspan="8" class="text-right">Total</th>
                      <th class="text-right">{{ $qty($sumIn) }}</th>
                      <th class="text-right">{{ $qty($sumOut) }}</th>
                      @if($productTypeId)<th class="text-right">{{ $qty(($opening ?? 0) + $sumIn - $sumOut) }}</th>@endif
                      <th></th>
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
  </div>
</section>
@endsection
