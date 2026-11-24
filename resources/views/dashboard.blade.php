@extends('index')
@section('content')
@php
  $today = \Carbon\Carbon::today();
  $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
  // Days until a due / required date: badge text + colour (overdue red, within 7 days orange)
  $dueBadge = function ($date) use ($today) {
      if (! $date) {
          return ['-', 'light'];
      }
      $days = $today->diffInDays(\Carbon\Carbon::parse($date)->startOfDay(), false);
      if ($days < 0) {
          return ['Overdue '.abs($days).' day'.(abs($days) === 1 ? '' : 's'), 'danger'];
      }
      if ($days === 0) {
          return ['Due today', 'warning'];
      }

      return [$days.' day'.($days === 1 ? '' : 's').' left', $days <= 7 ? 'warning' : 'success'];
  };
@endphp
<section class="section">
  <!-- Dashboard Cards: each opens its page -->
  <div class="row">
    @can('access', App\Models\Order::class)
    <!-- Card 1: Orders in Progress -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
      <a href="{{ route('order') }}" class="d-block text-reset" style="text-decoration: none;" title="Open Orders">
        <div class="card">
          <div class="card-statistic-4">
            <div class="align-items-center justify-content-between">
              <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                  <div class="card-content">
                    <h5 class="font-15">Orders in Progress</h5>
                    <h2 class="mb-3 font-18">{{ $ordersInProgress }}</h2>
                    <p class="mb-0"><span class="col-orange">Confirmed Orders</span> <i class="fas fa-arrow-right text-muted small ml-1"></i></p>
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
      </a>
    </div>
    @endcan

    @can('access', App\Models\Stock::class)
    <!-- Card 2: Running PTCs -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
      <a href="{{ route('ptc') }}" class="d-block text-reset" style="text-decoration: none;" title="Open PTCs">
        <div class="card">
          <div class="card-statistic-4">
            <div class="align-items-center justify-content-between">
              <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                  <div class="card-content">
                    <h5 class="font-15">Running PTCs</h5>
                    <h2 class="mb-3 font-18">{{ $runningPtcs }}</h2>
                    <p class="mb-0"><span class="col-blue">In Progress</span> <i class="fas fa-arrow-right text-muted small ml-1"></i></p>
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
      </a>
    </div>
    @endcan

    @can('access', App\Models\Transaction::class)
    <!-- Card 3: Expense This Month -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
      <a href="{{ route('expense') }}" class="d-block text-reset" style="text-decoration: none;" title="Open Expenses">
        <div class="card">
          <div class="card-statistic-4">
            <div class="align-items-center justify-content-between">
              <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                  <div class="card-content">
                    <h5 class="font-15">Expense This Month</h5>
                    <h2 class="mb-3 font-18">Rs. {{ number_format($expenseThisMonth) }}</h2>
                    <p class="mb-0"><span class="col-red">Current Month</span> <i class="fas fa-arrow-right text-muted small ml-1"></i></p>
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
      </a>
    </div>
    @endcan

    @can('stocks_access', App\Models\Stock::class)
    <!-- Card 4: Low Stock Alerts -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
      <a href="{{ route('stock') }}" class="d-block text-reset" style="text-decoration: none;" title="Open Product Stock">
        <div class="card">
          <div class="card-statistic-4">
            <div class="align-items-center justify-content-between">
              <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                  <div class="card-content">
                    <h5 class="font-15">Low Stock Alerts</h5>
                    <h2 class="mb-3 font-18">{{ $lowStockAlerts }}</h2>
                    <p class="mb-0"><span class="col-orange">Below 50 Units</span> <i class="fas fa-arrow-right text-muted small ml-1"></i></p>
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
      </a>
    </div>
    @endcan
  </div>

  @can('access', App\Models\Order::class)
  <!-- Orders in Progress: every confirmed order, nearest due date first -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-shopping-cart"></i> Orders in Progress ({{ $ordersInProgressTable->count() }})</h4>
          <div class="card-header-action">
            <small class="text-muted">Drag a column heading to move it; the order is remembered.</small>
          </div>
        </div>
        <div class="card-body">
          @if($ordersInProgressTable->count())
            <div class="table-responsive">
              <table class="table table-striped table-sm js-dashboard-table" id="dashboardOrders">
                <thead>
                  <tr>
                    <th>Job No.</th>
                    <th>Order No.</th>
                    <th>Customer</th>
                    <th>Order Date</th>
                    <th>Due Date</th>
                    <th>Time Left</th>
                    <th class="text-right">Items</th>
                    <th class="text-right">Total Qty</th>
                    <th>Status</th>
                    <th class="no-sort">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($ordersInProgressTable as $order)
                    @php [$dueText, $dueClass] = $dueBadge($order->due_date); @endphp
                    <tr>
                      <td><strong>{{ $order->job_no }}</strong></td>
                      <td>{{ $order->order_no }}</td>
                      <td>{{ $order->customer_no }} - {{ $order->fname }} {{ $order->lname }}</td>
                      <td data-order="{{ $order->order_date }}">{{ $order->order_date ? formatDate($order->order_date) : '-' }}</td>
                      <td data-order="{{ $order->due_date ?? '9999-12-31' }}">{{ $order->due_date ? formatDate($order->due_date) : '-' }}</td>
                      <td data-order="{{ $order->due_date ?? '9999-12-31' }}"><span class="badge badge-{{ $dueClass }}">{{ $dueText }}</span></td>
                      <td class="text-right">{{ $order->item_count }}</td>
                      <td class="text-right">{{ $qty($order->total_qty) }}</td>
                      <td><span class="badge badge-warning">Confirmed</span></td>
                      <td>
                        <a href="{{ route('order.show', $order->order_id) }}" class="btn btn-sm btn-info">
                          <i class="fas fa-eye"></i> View
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="text-center text-muted mb-0">No orders in progress</p>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endcan

  @if($openPurchasesTable !== null)
  <!-- Open Purchase Orders: pending / partially received, nearest required date first -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-truck"></i> Open Purchase Orders ({{ $openPurchasesTable->count() }})</h4>
          <div class="card-header-action">
            <small class="text-muted">Not fully received yet. Drag a column heading to move it.</small>
          </div>
        </div>
        <div class="card-body">
          @if($openPurchasesTable->count())
            <div class="table-responsive">
              <table class="table table-striped table-sm js-dashboard-table" id="dashboardPurchases">
                <thead>
                  <tr>
                    <th>PO No.</th>
                    <th>Vendor</th>
                    <th>Type</th>
                    <th>Order</th>
                    <th>PO Date</th>
                    <th>Required Date</th>
                    <th>Time Left</th>
                    <th class="text-right">Ordered</th>
                    <th class="text-right">Received</th>
                    <th class="text-right">Balance</th>
                    <th>Status</th>
                    <th class="no-sort">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($openPurchasesTable as $po)
                    @php [$dueText, $dueClass] = $dueBadge($po->require_date); @endphp
                    <tr>
                      <td><strong>{{ $po->purchase_no }}</strong></td>
                      <td>{{ $po->vendor_no }}{{ $po->fname ? ' - '.$po->fname : '' }}</td>
                      <td>{{ ucfirst($po->purchase_type) }}</td>
                      <td>{{ $po->job_no ?? '-' }}</td>
                      <td data-order="{{ $po->purchase_date }}">{{ $po->purchase_date ? formatDate($po->purchase_date) : '-' }}</td>
                      <td data-order="{{ $po->require_date ?? '9999-12-31' }}">{{ $po->require_date ? formatDate($po->require_date) : '-' }}</td>
                      <td data-order="{{ $po->require_date ?? '9999-12-31' }}"><span class="badge badge-{{ $dueClass }}">{{ $dueText }}</span></td>
                      <td class="text-right">{{ $qty($po->ordered_qty) }}</td>
                      <td class="text-right">{{ $qty($po->received_qty) }}</td>
                      <td class="text-right"><strong>{{ $qty($po->balance_qty) }}</strong></td>
                      <td><span class="badge badge-{{ $po->receive_status === 'Pending' ? 'danger' : 'info' }}">{{ $po->receive_status }}</span></td>
                      <td>
                        <a href="{{ route('purchase.show', $po->purchase_id) }}" class="btn btn-sm btn-info">
                          <i class="fas fa-eye"></i> View
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="text-center text-muted mb-0">All purchase orders are fully received</p>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endif

  @can('access', App\Models\Stock::class)
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
  @endcan

  {{-- Recent price changes: buying (purchases access) and selling (customers access) --}}
  @if($purchasePriceChanges !== null)
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-tags"></i> Recent Purchase Price Changes (Latest 10)</h4>
          <div class="card-header-action">
            <a href="{{ route('partyPrice.changes', 'vendor') }}" class="btn btn-sm btn-primary">View All / Filter</a>
          </div>
        </div>
        <div class="card-body">
          @include('priceChangesTable', ['changes' => $purchasePriceChanges, 'partyType' => 'vendor', 'empty' => 'No vendor price changes yet.'])
        </div>
      </div>
    </div>
  </div>
  @endif
  @if($sellingPriceChanges !== null)
  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4><i class="fas fa-tags"></i> Recent Selling Price Changes (Latest 10)</h4>
          <div class="card-header-action">
            <a href="{{ route('partyPrice.changes', 'customer') }}" class="btn btn-sm btn-primary">View All / Filter</a>
          </div>
        </div>
        <div class="card-body">
          @include('priceChangesTable', ['changes' => $sellingPriceChanges, 'partyType' => 'customer', 'empty' => 'No customer price changes yet.'])
        </div>
      </div>
    </div>
  </div>
  @endif
</section>
@endsection

@push('scripts')
<script>
  // Orders / purchase orders: all rows (no paging), the built-in search, sortable, and columns that can be
  // dragged into another order (ColReorder). Only the column order is remembered; sorting and search
  // start fresh on each visit, so the nearest due date is always first.
  $(function () {
    $('.js-dashboard-table').each(function () {
      $(this).DataTable({
        paging: false,
        order: [],
        autoWidth: false,
        colReorder: true,
        stateSave: true,
        stateDuration: 0,
        stateLoadParams: function (settings, data) {
          delete data.order;
          if (data.search) data.search.search = '';
          (data.columns || []).forEach(function (c) { if (c.search) c.search.search = ''; });
        },
        columnDefs: [{ orderable: false, targets: 'no-sort' }],
        language: { info: 'Showing all _TOTAL_', infoFiltered: '(of _MAX_)' }
      });
    });
  });
</script>
@endpush
