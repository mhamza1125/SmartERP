@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Product Cost Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('productCost.print', $product['product_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('productCost') }}" class="btn btn-primary">Back</a>
                <a href="{{ route('productCost.edit', $product['product_id']) }}" class="btn btn-primary">Edit</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-3"><b>Article No:</b> {{ $product['article_no'] }}</div>
              <div class="col-md-3"><b>Article:</b> {{ $product['name'] }}</div>
              <div class="col-md-3"><b>Category:</b> {{ $product['cname'] ?? 'N/A' }}</div>
              <div class="col-md-3"><b>Unit:</b> {{ $product['hname'] ?? 'N/A' }}</div>
            </div>
            <table class="table" id="tableExport" style="width:100%;">
              <thead>
                <tr>
                  <th>Sr.</th>
                  <th>Employee / Vendor</th>
                  <th>Cost Head</th>
                  <th class="text-right">Price</th>
                </tr>
              </thead>
              <tbody>
                @if($productCost->count())
                  @foreach($productCost as $item)
                  <tr>
                    <td>{{$loop->index + 1}}</td>
                    <td>{{$item->employee_no ?? $item->vendor_no}}{{$item->name ? ' - '.$item->name : 'General Cost'}}</td>
                    <td>{{$item->hname}}</td>
                    <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                  </tr>
                  @endforeach
                @endif
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="3" class="text-right">Total Cost (General Cost only; labor-specific wages excluded)</th>
                  <th class="text-right">{{ number_format($productCost->where('table_name', 'general')->sum('amount'), 2) }}</th>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection