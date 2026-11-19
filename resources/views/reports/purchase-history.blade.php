@extends('index')
@section('content')
@php
  $qty = fn ($value) => rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
  $unit = $type === 'material' ? ($selected->uname ?? '') : '';
  $canOpenPurchase = auth()->user()->can('show', App\Models\Purchase::class);
@endphp
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Purchase Ledger</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('purchase.ledger') }}" method="GET" class="col-md-12">
              <div class="row">
                <div class="col-md-6 form-group">
                  <label>Material / Product</label>
                  <select class="form-control select2" name="item" required>
                    <option value="" {{ $item === '' ? 'selected' : '' }} disabled>Select a material or product</option>
                    <optgroup label="Materials">
                      @foreach($materials as $m)
                        <option value="material:{{ $m->material_id }}" {{ $item === 'material:'.$m->material_id ? 'selected' : '' }}>{{ $m->material_no }} - {{ $m->name }}{{ $m->uname ? ' ('.$m->uname.')' : '' }}</option>
                      @endforeach
                    </optgroup>
                    <optgroup label="Products">
                      @foreach($products as $p)
                        <option value="product:{{ $p->product_id }}" {{ $item === 'product:'.$p->product_id ? 'selected' : '' }}>{{ $p->article_no }} - {{ $p->name }}</option>
                      @endforeach
                    </optgroup>
                  </select>
                </div>
                <div class="form-group col-md-2">
                  <label>Date From <small class="text-muted">(optional)</small></label>
                  <input type="text" class="form-control datepicker" name="dfrom" value="{{ $dfrom }}">
                </div>
                <div class="form-group col-md-2">
                  <label>Date To</label>
                  <input type="text" class="form-control datepicker" name="dto" value="{{ $dto }}">
                </div>
                <div class="form-group col-md-2 mt-4">
                  <button class="btn btn-primary mt-2" type="submit">Search</button>
                  @if($dfrom)<a href="{{ route('purchase.ledger', ['item' => $item]) }}" class="btn btn-secondary mt-2">All Dates</a>@endif
                </div>
              </div>
            </form>

            @if($selected)
              <table class="table table-sm">
                <tbody>
                  <tr>
                    <td><b>{{ $type === 'material' ? 'Material' : 'Product' }}:</b>
                      @if($type === 'material')
                        {{ $selected->material_no }} - {{ $selected->name }}
                        @if(auth()->user()->can('show', App\Models\Material::class)) <a href="{{ route('material.show', $selected->material_id) }}" target="_blank" class="ml-1">Open</a>@endif
                      @else
                        {{ $selected->article_no }} - {{ $selected->name }}
                        @if(auth()->user()->can('show', App\Models\Product::class)) <a href="{{ route('product.show', $selected->product_id) }}" target="_blank" class="ml-1">Open</a>@endif
                      @endif
                    </td>
                    <td><b>Period:</b> {{ $dfrom ? formatDate($dfrom).' to '.formatDate($dto) : 'All purchases' }}</td>
                    <td><b>Vendors:</b> {{ $comparison->count() }}</td>
                    <td><b>Purchase lines:</b> {{ $history->count() }}</td>
                  </tr>
                </tbody>
              </table>

              <h5>Vendor Price Comparison</h5>
              @include('purchaseVendorComparison', ['comparison' => $comparison, 'unit' => $unit])

              <h5>Purchase History</h5>
              <div class="table-responsive">
                <table class="table table-sm table-striped" id="tableExport" style="width:100%;">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Date</th>
                      <th>Purchase No</th>
                      <th>Job No</th>
                      <th>Vendor</th>
                      @if($type === 'product')<th>Size</th><th>Stage</th>@endif
                      <th class="text-right">QTY</th>
                      <th class="text-right">Received</th>
                      <th class="text-right">Price</th>
                      <th class="text-right">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($history as $line)
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td data-order="{{ $line->purchase_date }}">{{ formatDate($line->purchase_date) }}</td>
                        <td>
                          @if($canOpenPurchase)
                            <a href="{{ ! empty($line->is_process) ? route('mprocess.show', $line->purchase_id) : route('purchase.show', $line->purchase_id) }}" target="_blank">{{ $line->purchase_no }}</a>
                          @else
                            {{ $line->purchase_no }}
                          @endif
                          @if(! empty($line->is_process)) <span class="badge badge-info">Processing</span>@endif
                        </td>
                        <td>{{ $line->job_no ?? '-' }}</td>
                        <td>{{ $line->vendor_no }}{{ $line->fname ? ' - '.$line->fname : '' }}</td>
                        @if($type === 'product')<td>{{ $line->size_name }}</td><td>{{ $line->stage_name ?? '-' }}</td>@endif
                        <td class="text-right">{{ $qty($line->quantity) }} {{ $unit }}</td>
                        <td class="text-right">{{ $qty($line->received_qty) }} {{ $unit }}</td>
                        <td class="text-right">{{ number_format($line->price, 2) }}</td>
                        <td class="text-right">{{ number_format($line->total, 2) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <blockquote>Select a material or product to see every vendor it was bought from, their prices and the purchase history.</blockquote>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
