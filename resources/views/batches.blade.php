@extends('index')
@section('content')
@php
  $stockPages = [
    'product' => ['route' => 'stock', 'title' => 'Product Stock'],
    'material' => ['route' => 'stock.material', 'title' => 'Material Stock'],
    'machine' => ['route' => 'stock.machine', 'title' => 'Machine Material Stock'],
    'batches' => ['route' => 'batch.index', 'title' => 'Batches'],
  ];
  $sourceLabels = ['ptc' => 'PTC', 'purchase' => 'Purchase', 'issuance' => 'Issuance', 'opening' => 'Opening', 'split' => 'Split', 'legacy' => 'Legacy'];
@endphp
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Stock Batches</h4>
            <div class="card-header-action">
              @if($withEmpty)
                <a href="{{ route('batch.index') }}" class="btn btn-secondary">Hide empty batches</a>
              @else
                <a href="{{ route('batch.index', ['all' => 1]) }}" class="btn btn-secondary">Show empty batches</a>
              @endif
            </div>
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-3">
              @foreach($stockPages as $pageType => $page)
                <li class="nav-item">
                  <a class="nav-link {{ $pageType === 'batches' ? 'active' : '' }}" href="{{ route($page['route']) }}">{{ $page['title'] }}</a>
                </li>
              @endforeach
            </ul>

            <p class="text-muted">
              Every lot of product stock belongs to a batch. A batch <strong>reserved</strong> for an order can only be delivered or issued for that order;
              <strong>free</strong> batches can be used by any order.
            </p>

            <div class="table-responsive">
              <table class="table table-sm table-striped" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Batch</th>
                    <th>Article No</th>
                    <th>Item / Product</th>
                    <th>Size</th>
                    <th>Source</th>
                    <th>Reserved For</th>
                    <th>Stock by Stage</th>
                    <th>Usable Stock</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($batches as $batch)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $batch->batch_no }}</td>
                      <td>{{ $batch->article_no }}</td>
                      <td>{{ $batch->product_name }}</td>
                      <td>{{ $batch->size_name }}</td>
                      <td>{{ $sourceLabels[$batch->source_type] ?? $batch->source_type }}</td>
                      <td>
                        @if($batch->order_id)
                          <span class="badge badge-warning">{{ $batch->job_no ?? 'Order ' . $batch->order_id }}</span>
                        @else
                          <span class="badge badge-success">Free</span>
                        @endif
                      </td>
                      <td>
                        @forelse($batch->stages as $stage)
                          <span class="badge {{ $stage->balance < 0 ? 'badge-danger' : 'badge-light' }}">{{ $stage->stage_name }}: {{ number_format($stage->balance) }}</span>
                        @empty
                          <span class="text-muted">Empty</span>
                        @endforelse
                      </td>
                      <td>{{ number_format($batch->balance) }} {{ $batch->unit_name }}</td>
                      <td>
                        <a href="{{ route('batch.show', $batch->batch_id) }}" class="btn btn-sm btn-info" title="Batch history">
                          <i class="fas fa-eye"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Batch</th>
                    <th>Article No</th>
                    <th>Item / Product</th>
                    <th>Size</th>
                    <th>Source</th>
                    <th>Reserved For</th>
                    <th>Stock by Stage</th>
                    <th>Usable Stock</th>
                    <th>Actions</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
