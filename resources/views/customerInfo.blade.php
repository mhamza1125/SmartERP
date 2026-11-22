@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Customer Info</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a class="btn btn-info" href="{{ route('customer.print', $customer['customer_id']) }}" target="_blank">
                  <i class="fas fa-file-alt"></i> Print
                </a>
                <a href="{{ route('customer') }}" class="btn btn-primary">Back</a>
                @can('edit', App\Models\Customer::class)<a href="{{ route('customer.edit', $customer['customer_id']) }}" class="btn btn-primary">Edit</a>@endcan
@can('delete', App\Models\Customer::class)@include('deleteButton', ['url' => route('customer.destroy', $customer['customer_id']), 'check' => route('customer.deleteCheck', $customer['customer_id']), 'label' => 'Customer '.$customer['customer_no'], 'class' => 'btn btn-danger'])@endcan
                @if(auth()->user()->can('show', App\Models\Customer::class) && auth()->user()->can('show', App\Models\Transaction::class))<a href="{{ route('customer.detail', $customer['customer_id']) }}" class="btn btn-primary">Ledger</a>@endif
              </div>
            </div>
          </div>
          <div class="card-body">
            <table class="table">
              <tbody>
                <tr>
                  <td><b>Customer No: </b> {{$customer['customer_no']}}</td>
                  <td><b>Country: </b> {{$customer['coname']}}</td>
                  <td colspan="2"><b>Name: </b> {{$customer['fname']}} {{$customer['lname']}}</td>
                </tr>
                <tr>
                  <td><b>Email: </b> {{$customer['email']}}</td>
                  <td><b>Contact: </b> {{$customer['phone']}}</td>
                  <td><b>Fax No: </b> {{$customer['fax']}}</td>
                  <td><b>Currency: </b> {{$customer['cuname']}}</td>
                </tr>
                <tr>
                  <td><b>Port Name: </b> {{$customer['port_no']}}</td>
                  <td colspan="3"><b>Address: </b> {{$customer['address']}}</td>
                </tr>
                <tr>
                  <td colspan="4">
                    <div class="row">
                      <div class="col-md-1"><b>Details: </b></div>
                      <div class="col-md-11">
                        @php echo $customer['description'] @endphp
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            {{-- Saved selling prices of this customer (shown on the order form) --}}
            @if($sellingPrices)
              @include('partyPriceList', $sellingPrices)
            @endif

            {{-- Prices this customer bought at (order lines of non-cancelled orders) --}}
            <h5 class="mt-4">Prices on Past Orders</h5>
            @if($priceSummary->count())
              <div class="table-responsive">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Product</th>
                      <th>Size</th>
                      <th class="text-right">Last Price</th>
                      <th>Last Order</th>
                      <th class="text-right">Lowest</th>
                      <th class="text-right">Highest</th>
                      <th class="text-right">Orders</th>
                      <th class="text-right">Total QTY</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($priceSummary as $item)
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                          @can('show', App\Models\Product::class)
                            <a href="{{ route('product.show', $item->product_id) }}" target="_blank">{{ $item->article_no ? $item->article_no.' - ' : '' }}{{ $item->product_name }}</a>
                          @else
                            {{ $item->article_no ? $item->article_no.' - ' : '' }}{{ $item->product_name }}
                          @endcan
                        </td>
                        <td>{{ $item->size_name ?? '-' }}</td>
                        <td class="text-right"><b>{{ number_format($item->last_price, 2) }}</b></td>
                        <td>{{ $item->last_order_no }} ({{ formatDate($item->last_order_date) }})</td>
                        <td class="text-right">{{ number_format($item->lowest, 2) }}</td>
                        <td class="text-right">{{ number_format($item->highest, 2) }}</td>
                        <td class="text-right">{{ $item->orders }}</td>
                        <td class="text-right">{{ number_format($item->quantity) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              <h5 class="mt-4">Price History (Customer Orders)</h5>
              <div class="table-responsive">
                <table class="table table-sm table-striped">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Date</th>
                      <th>Order No</th>
                      <th>Job No</th>
                      <th>Product</th>
                      <th>Size</th>
                      <th>Status</th>
                      <th class="text-right">QTY</th>
                      <th class="text-right">Price</th>
                      <th class="text-right">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($priceHistory as $item)
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ formatDate($item->order_date) }}</td>
                        <td>
                          @can('show', App\Models\Order::class)
                            <a href="{{ route('order.show', $item->order_id) }}" target="_blank">{{ $item->order_no }}</a>
                          @else
                            {{ $item->order_no }}
                          @endcan
                        </td>
                        <td>{{ $item->job_no ?? '-' }}</td>
                        <td>{{ $item->article_no ? $item->article_no.' - ' : '' }}{{ $item->product_name }}</td>
                        <td>{{ $item->size_name ?? '-' }}</td>
                        <td>{{ \App\Models\Order::STATUS_LABELS[$item->order_status] ?? '-' }}</td>
                        <td class="text-right">{{ number_format($item->quantity) }}</td>
                        <td class="text-right">{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->total, 2) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <blockquote> No orders yet </blockquote>
            @endif

            @if($image->count())
              <h5>Images</h5>
              <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                @foreach($image as $item)
                  <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <a href="{{ URL::asset('resources/customer/'. $item->image) }}">
                      <img class="img-responsive thumbnail" src="{{ URL::asset('resources/customer/'. $item->image) }}" alt="">
                    </a>
                    @can('edit', App\Models\Customer::class)
                    <form action="{{route('image.delete', ['id' => $item->image_id, 'dir' => 'customer'])}}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-danger delbtn"><i class="fa fa-trash"></i></button>
                    </form>
                    @endcan
                  </div>
                @endforeach
              </div>
            @else
              <blockquote> No Images </blockquote>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection