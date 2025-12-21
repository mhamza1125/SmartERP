@extends('index')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4>Product Stock Requirements Report</h4>
          <div class="card-header-action">
            <a href="{{ route('reports.product.stock.requirements') }}" class="btn btn-primary">
              <i class="fas fa-redo"></i> Reset Filter
            </a>
          </div>
        </div>
        <div class="card-body">
          <!-- Filter Section -->
          <form method="GET" action="{{ route('reports.product.stock.requirements') }}" class="mb-4">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="product_type_id">Filter by Product:</label>
                  <select name="product_type_id" id="product_type_id" class="form-control select2">
                    <option value="">-- All Products --</option>
                    @foreach($productTypes as $pt)
                      <option value="{{ $pt->product_type_id }}" 
                        {{ $selectedProductTypeId == $pt->product_type_id ? 'selected' : '' }}>
                        {{ $pt->article_no }} - {{ $pt->product_name }} ({{ $pt->size_name }}
                        @if($pt->color_name), {{ $pt->color_name }}@endif)
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>&nbsp;</label>
                  <button type="submit" class="btn btn-info btn-block">
                    <i class="fas fa-filter"></i> Filter
                  </button>
                </div>
              </div>
            </div>
          </form>

          <!-- Report Table -->
          @if(count($reportData) > 0)
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead class="table-light">
                  <tr>
                    <th style="width: 20%">Product</th>
                    <th style="width: 12%">Current Stock</th>
                    <th style="width: 12%">Unclosed PTC</th>
                    <th style="width: 12%">Confirmed Orders</th>
                    <th style="width: 12%">Total Needed</th>
                    <th style="width: 22%">Materials</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($reportData as $item)
                    @php
                      $statusClass = '';
                      $statusText = '';
                      if ($item['total_needed'] > 0) {
                        $statusClass = 'badge-danger';
                        $statusText = 'Shortage';
                      } elseif ($item['total_needed'] < 0) {
                        $statusClass = 'badge-success';
                        $statusText = 'Surplus';
                      } else {
                        $statusClass = 'badge-secondary';
                        $statusText = 'Balanced';
                      }
                    @endphp
                    <tr>
                      <td>
                        <strong>{{ $item['article_no'] }}</strong><br>
                        <small>{{ $item['product_name'] }}</small><br>
                        <small class="text-muted">{{ $item['size_name'] }}
                          @if($item['color_name']), {{ $item['color_name'] }}@endif
                        </small>
                      </td>
                      <td>
                        <span class="badge badge-info">{{ $item['current_stock'] }}</span>
                      </td>
                      <td>
                        <span class="badge badge-warning">{{ $item['unclosed_ptc'] }}</span>
                      </td>
                      <td>
                        <span class="badge badge-primary">{{ $item['confirmed_orders'] }}</span>
                      </td>
                      <td>
                        <span class="badge {{ $statusClass }}">
                          {{ $item['total_needed'] > 0 ? '+' : '' }}{{ $item['total_needed'] }}
                        </span>
                        <br>
                        <small class="text-muted">{{ $statusText }}</small>
                      </td>
                      <td>
                        @if(count($item['materials']) > 0)
                          <ul class="list-unstyled mb-0">
                            @foreach($item['materials'] as $material)
                              <li>
                                <small>
                                  <strong>{{ $material->name }}:</strong>
                                  {{ $material->quantity }} {{ $material->unit_name }}
                                  @if($item['total_needed'] > 0)
                                    <br>
                                    <span class="text-danger">
                                      Need: {{ $material->quantity * $item['total_needed'] }} {{ $material->unit_name }}
                                    </span>
                                  @endif
                                </small>
                              </li>
                            @endforeach
                          </ul>
                        @else
                          <small class="text-muted">No materials</small>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-info">
              <i class="fas fa-info-circle"></i> No products found matching the selected filter.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .table-light {
    background-color: #f8f9fa;
  }
  
  .badge {
    padding: 0.5rem 0.75rem;
    font-size: 0.85rem;
  }
  
  .list-unstyled li {
    margin-bottom: 0.5rem;
  }
</style>
@endsection

