@extends('index')
@section('content')
@php
  $stockPages = [
    'product' => ['route' => 'stock', 'title' => 'Product Stock'],
    'material' => ['route' => 'stock.material', 'title' => 'Material Stock'],
    'machine' => ['route' => 'stock.machine', 'title' => 'Machine Material Stock'],
  ];
@endphp
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>{{ $stockPages[$type]['title'] }}</h4>
            <div class="card-header-action">
              <a href="{{ route('stock.print', ['type' => $type]) }}" class="btn btn-info" target="_blank">
                <i class="fas fa-print"></i> Print
              </a>
            </div>
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-3">
              @foreach($stockPages as $pageType => $page)
                <li class="nav-item">
                  <a class="nav-link {{ $type === $pageType ? 'active' : '' }}" href="{{ route($page['route']) }}">{{ $page['title'] }}</a>
                </li>
              @endforeach
            </ul>

            @if($type === 'product')
              {{-- Product Stock (Grouped by Product/Size with Stage Modal) --}}
              <div class="table-responsive">
                <table class="table table-sm table-striped" id="tableExport" style="width:100%;">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Article No</th>
                      <th>Item / Product</th>
                      <th>Size</th>
                      <th>Total Stock</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($pstock->count())
                      @php
                        // Group stock by product_type_id to show one row per product/size
                        $groupedPstock = $pstock->groupBy('product_type_id');
                        $rowIndex = 1;
                      @endphp
                      @foreach($groupedPstock as $productTypeId => $stageItems)
                        @php
                          $firstItem = $stageItems->first();
                          // Calculate total stock across all stages, excluding rejection stock (head_id = 105)
                          $totalStock = $stageItems->sum(function($item) {
                            // Exclude rejection stock from total count
                            if(($item->sthead_id ?? $item->stage_id) == 105) return 0;
                            return $item->stockIn - $item->stockOut;
                          });
                          // Show all products including zero stock
                        @endphp
                        <tr>
                          <td>{{ $rowIndex++ }}</td>
                          {{-- Article/Name repeated on every row so sorting, searching and export stay correct --}}
                          <td>{{ $firstItem->article_no }}</td>
                          <td>{{ $firstItem->name }}</td>
                          <td>{{ $firstItem->sname }}</td>
                          <td>
                            @if($totalStock > 0)
                              <span class="badge badge-success">{{ number_format($totalStock) }} {{ $firstItem->uname }}</span>
                            @else
                              <span class="badge badge-secondary">0 {{ $firstItem->uname }}</span>
                            @endif
                          </td>
                          <td>
                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#stageModal{{ $productTypeId }}">
                              <i class="fas fa-layer-group"></i> View Stages ({{ $stageItems->count() }})
                            </button>
                          </td>
                        </tr>
                      @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Article No</th>
                      <th>Item / Product</th>
                      <th>Size</th>
                      <th>Total Stock</th>
                      <th>Actions</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            @else
              {{-- Material / Machine Material Stock ($stock is already filtered by category) --}}
              <div class="table-responsive">
                <table class="table table-sm table-striped" id="tableExport" style="width:100%;">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Code</th>
                      <th>Material Name</th>
                      <th>Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($stock as $item)
                      <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$item->material_no}}</td>
                        <td>
                          {{$item->name}}
                          @if($item->location)
                            <sub style="color: #6c757d;">{{$item->location}}</sub>
                          @endif
                        </td>
                        <td>{{number_format($item->total_received + $item->stockIn - $item->stockOut - $item->total_returned)}} {{$item->uname}}</td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Code</th>
                      <th>Material Name</th>
                      <th>Quantity</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stage Detail Modals for Product Stock -->
@if($pstock->count())
  @php $groupedPstockForModals = $pstock->groupBy('product_type_id'); @endphp
  @foreach($groupedPstockForModals as $productTypeId => $stageItems)
    @php
      $firstItem = $stageItems->first();
      // Calculate total stock excluding rejection stock (head_id = 105)
      $totalStock = $stageItems->sum(function($item) {
        if(($item->sthead_id ?? $item->stage_id) == 105) return 0;
        return $item->stockIn - $item->stockOut;
      });
      // Calculate rejection stock separately for display
      $rejectionStock = $stageItems->sum(function($item) {
        if(($item->sthead_id ?? $item->stage_id) == 105) return $item->stockIn - $item->stockOut;
        return 0;
      });
      // Show all products including zero stock
    @endphp
    <div class="modal fade" id="stageModal{{ $productTypeId }}" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">
              <i class="fas fa-layer-group"></i> Stage Breakdown: {{ $firstItem->article_no }} - {{ $firstItem->name }} ({{ $firstItem->sname }})
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-info mb-3">
              <strong>Product:</strong> {{ $firstItem->name }} |
              <strong>Article:</strong> {{ $firstItem->article_no }} |
              <strong>Size:</strong> {{ $firstItem->sname }} |
              <strong>Usable Stock:</strong> {{ number_format($totalStock) }} {{ $firstItem->uname }}
              @if($rejectionStock != 0)
                | <strong class="text-warning">Rejection:</strong> {{ number_format($rejectionStock) }} {{ $firstItem->uname }}
              @endif
            </div>
            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead class="thead-light">
                  <tr>
                    <th>Sr.</th>
                    <th>Stage</th>
                    <th>Stock In</th>
                    <th>Stock Out</th>
                    <th>Available</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($stageItems as $stageIdx => $stageItem)
                    @php $stageStock = $stageItem->stockIn - $stageItem->stockOut; @endphp
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td><span class="badge badge-primary">{{ $stageItem->stname ?? 'N/A' }}</span></td>
                      <td>{{ number_format($stageItem->stockIn) }}</td>
                      <td>{{ number_format($stageItem->stockOut) }}</td>
                      <td>
                        @if($stageStock > 0)
                          <span class="badge badge-success">{{ number_format($stageStock) }} {{ $stageItem->uname }}</span>
                        @elseif($stageStock < 0)
                          <span class="badge badge-danger">{{ number_format($stageStock) }} {{ $stageItem->uname }}</span>
                        @else
                          <span class="badge badge-secondary">0 {{ $stageItem->uname }}</span>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr class="table-info">
                    <th colspan="4" class="text-right">Usable Stock:</th>
                    <th><span class="badge badge-success">{{ number_format($totalStock) }} {{ $firstItem->uname }}</span></th>
                  </tr>
                  @if($rejectionStock != 0)
                  <tr class="table-warning">
                    <th colspan="4" class="text-right">Rejection Stock (Excluded):</th>
                    <th><span class="badge badge-warning">{{ number_format($rejectionStock) }} {{ $firstItem->uname }}</span></th>
                  </tr>
                  @endif
                </tfoot>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endif
@endsection