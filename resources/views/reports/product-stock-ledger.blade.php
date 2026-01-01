@extends('index')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <h4>Product Stock Ledger Report</h4>
          <div class="card-header-action">
            <a href="{{ route('reports.product.stock.ledger') }}" class="btn btn-primary">
              <i class="fas fa-redo"></i> Reset
            </a>
          </div>
        </div>
        <div class="card-body">
          <!-- Product Selector -->
          <form method="GET" action="{{ route('reports.product.stock.ledger') }}" class="mb-4">
            <div class="row">
              <div class="col-md-8">
                <div class="form-group">
                  <label for="product_type_id"><strong>Select Product:</strong></label>
                  <select name="product_type_id" id="product_type_id" class="form-control select2" required>
                    <option value="">-- Select a Product --</option>
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
              <div class="col-md-4">
                <div class="form-group">
                  <label>&nbsp;</label>
                  <button type="submit" class="btn btn-info btn-block">
                    <i class="fas fa-search"></i> View Ledger
                  </button>
                </div>
              </div>
            </div>
          </form>

          @if($selectedProduct)
            <!-- Product Details -->
            <div class="alert alert-info mb-4">
              <div class="row">
                <div class="col-md-6">
                  <strong>Product:</strong> {{ $selectedProduct->article_no }} - {{ $selectedProduct->product_name }}<br>
                  <strong>Size:</strong> {{ $selectedProduct->size_name }}
                  @if($selectedProduct->color_name)
                    <br><strong>Color:</strong> {{ $selectedProduct->color_name }}
                  @endif
                </div>
              </div>
            </div>

            <!-- Ledger Table -->
            @if(count($ledgerData) > 0)
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead class="table-light">
                    <tr>
                      <th style="width: 40%">Transaction Type</th>
                      <th style="width: 15%" class="text-right">Stock In</th>
                      <th style="width: 15%" class="text-right">Stock Out</th>
                      <th style="width: 15%" class="text-right">Balance</th>
                      <th style="width: 15%" class="text-right">Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                      $totalStockIn = 0;
                      $totalStockOut = 0;
                      $finalBalance = 0;
                    @endphp
                    @foreach($ledgerData as $row)
                      @php
                        $totalStockIn += $row['stock_in'];
                        $totalStockOut += $row['stock_out'];
                        $finalBalance = $row['balance'];
                        $balanceClass = $row['balance'] >= 0 ? 'text-success' : 'text-danger';
                      @endphp
                      <tr>
                        <td>
                          <strong>{{ $row['transaction_type'] }}</strong>
                          @if($row['reference'] === 'Opening')
                            <span class="badge badge-secondary">Opening</span>
                          @elseif($row['reference'] === 'PTC')
                            <span class="badge badge-warning">PTC</span>
                          @elseif($row['reference'] === 'Order')
                            <span class="badge badge-primary">Order</span>
                          @endif
                        </td>
                        <td class="text-right">
                          @if($row['stock_in'] > 0)
                            <span class="badge badge-success">{{ $row['stock_in'] }}</span>
                          @else
                            -
                          @endif
                        </td>
                        <td class="text-right">
                          @if($row['stock_out'] > 0)
                            <span class="badge badge-danger">{{ $row['stock_out'] }}</span>
                          @else
                            -
                          @endif
                        </td>
                        <td class="text-right {{ $balanceClass }}">
                          <strong>{{ $row['balance'] }}</strong>
                        </td>
                        <td class="text-right">
                          <small>{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</small>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot class="table-light">
                    <tr>
                      <th>TOTAL</th>
                      <th class="text-right"><span class="badge badge-success">{{ $totalStockIn }}</span></th>
                      <th class="text-right"><span class="badge badge-danger">{{ $totalStockOut }}</span></th>
                      <th class="text-right">
                        <strong class="{{ $finalBalance >= 0 ? 'text-success' : 'text-danger' }}">
                          {{ $finalBalance }}
                        </strong>
                      </th>
                      <th></th>
                    </tr>
                  </tfoot>
                </table>
              </div>

              <!-- Summary Section -->
              <div class="row mt-4">
                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-body">
                      <h6 class="card-title">Total Stock In</h6>
                      <h3 class="text-success">{{ $totalStockIn }}</h3>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-body">
                      <h6 class="card-title">Total Stock Out</h6>
                      <h3 class="text-danger">{{ $totalStockOut }}</h3>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-body">
                      <h6 class="card-title">Final Balance</h6>
                      <h3 class="{{ $finalBalance >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $finalBalance >= 0 ? '+' : '' }}{{ $finalBalance }}
                      </h3>
                      <small class="text-muted">
                        @if($finalBalance > 0)
                          Surplus - {{ $finalBalance }} units available
                        @elseif($finalBalance < 0)
                          Shortage - {{ abs($finalBalance) }} units needed
                        @else
                          Balanced - No surplus or shortage
                        @endif
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            @else
              <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No transactions found for this product.
              </div>
            @endif
          @else
            <div class="alert alert-warning">
              <i class="fas fa-exclamation-triangle"></i> Please select a product to view the ledger.
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
  
  .text-right {
    text-align: right;
  }
</style>
@endsection

