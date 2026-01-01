@extends('index')
@section('content')
<section class="section">
  <!-- Dashboard Cards -->
  <div class="row">
    <!-- Card 1: Orders in Progress -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
      <div class="card">
        <div class="card-statistic-4">
          <div class="align-items-center justify-content-between">
            <div class="row">
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                <div class="card-content">
                  <h5 class="font-15">Orders in Progress</h5>
                  <h2 class="mb-3 font-18">{{ $ordersInProgress }}</h2>
                  <p class="mb-0"><span class="col-orange">Confirmed Orders</span></p>
                </div>
              </div>
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                <div class="banner-img">
                  <i class="fas fa-shopping-cart" style="font-size: 3rem; color: #ff9800; opacity: 0.3;"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 2: Running PTCs -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
      <div class="card">
        <div class="card-statistic-4">
          <div class="align-items-center justify-content-between">
            <div class="row">
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                <div class="card-content">
                  <h5 class="font-15">Running PTCs</h5>
                  <h2 class="mb-3 font-18">{{ $runningPtcs }}</h2>
                  <p class="mb-0"><span class="col-blue">In Progress</span></p>
                </div>
              </div>
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                <div class="banner-img">
                  <i class="fas fa-tasks" style="font-size: 3rem; color: #2196f3; opacity: 0.3;"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 3: Expense This Month -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
      <div class="card">
        <div class="card-statistic-4">
          <div class="align-items-center justify-content-between">
            <div class="row">
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                <div class="card-content">
                  <h5 class="font-15">Expense This Month</h5>
                  <h2 class="mb-3 font-18">Rs. {{ number_format($expenseThisMonth) }}</h2>
                  <p class="mb-0"><span class="col-red">Current Month</span></p>
                </div>
              </div>
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                <div class="banner-img">
                  <i class="fas fa-money-bill-wave" style="font-size: 3rem; color: #f44336; opacity: 0.3;"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 4: Low Stock Alerts -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
      <div class="card">
        <div class="card-statistic-4">
          <div class="align-items-center justify-content-between">
            <div class="row">
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                <div class="card-content">
                  <h5 class="font-15">Low Stock Alerts</h5>
                  <h2 class="mb-3 font-18">{{ $lowStockAlerts }}</h2>
                  <p class="mb-0"><span class="col-orange">Below 50 Units</span></p>
                </div>
              </div>
              <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                <div class="banner-img">
                  <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #ff9800; opacity: 0.3;"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


  </div>

  <!-- Running PTCs Table -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-tasks"></i> Running PTCs (Latest 10)</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-sm">
              <thead>
                <tr>
                  <th>PTC No.</th>
                  <th>Product</th>
                  <th>Size</th>
                  <th>Quantity</th>
                  <th>Status</th>
                  <th>Date Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @if($runningPtcsTable->count())
                  @foreach($runningPtcsTable as $ptc)
                    <tr>
                      <td><strong>PTC-{{ $ptc->stock_no }}</strong></td>
                      <td>{{ $ptc->article_no }} - {{ $ptc->product_name }}</td>
                      <td>{{ $ptc->size_name }}</td>
                      <td>{{ $ptc->quantity }}</td>
                      <td><span class="badge badge-info">In Progress</span></td>
                      <td>{{ \Carbon\Carbon::parse($ptc->stock_date)->format('d-m-Y') }}</td>
                      <td>
                        <a href="{{ route('ptc.show', $ptc->stock_id) }}" class="btn btn-sm btn-info">
                          <i class="fas fa-eye"></i> View
                        </a>
                      </td>
                    </tr>
                  @endforeach
                @else
                  <tr>
                    <td colspan="7" class="text-center text-muted">No running PTCs</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Orders in Progress Table -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-shopping-cart"></i> Orders in Progress (Latest 10)</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped table-sm">
              <thead>
                <tr>
                  <th>Order No.</th>
                  <th>Customer Name</th>
                  <th>Order Date</th>
                  <th>Due Date</th>
                  <th>Total Items</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @if($ordersInProgressTable->count())
                  @foreach($ordersInProgressTable as $order)
                    <tr>
                      <td><strong>#{{ $order->job_no }}</strong></td>
                      <td>{{ $order->fname }} {{ $order->lname }}</td>
                      <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d-m-Y') }}</td>
                      <td>{{ \Carbon\Carbon::parse($order->due_date)->format('d-m-Y') }}</td>
                      <td>{{ $order->item_count }}</td>
                      <td><span class="badge badge-warning">Confirmed</span></td>
                      <td>
                        <a href="{{ route('order.show', $order->order_id) }}" class="btn btn-sm btn-info">
                          <i class="fas fa-eye"></i> View
                        </a>
                      </td>
                    </tr>
                  @endforeach
                @else
                  <tr>
                    <td colspan="7" class="text-center text-muted">No orders in progress</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection