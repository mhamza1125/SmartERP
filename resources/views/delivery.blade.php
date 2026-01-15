@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Delivery Table</h4>
            <div class="card-header-action">
              <a href="{{ route('delivery-return') }}" class="btn btn-warning">View Returns</a>
              <button type="button" class="btn btn-info" data-toggle="modal" data-target="#multiOrderDeliveryModal">
                Multi-Order Delivery
              </button>
              {{-- <a href="{{ route('delivery') }}" class="btn btn-primary">Add Delivery</a> --}}
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Delivery No</th>
                    <th>Order No</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($delivery->count())
                    @foreach($delivery as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{\Carbon\Carbon::parse($item->delivery_date)->format('d-m-Y')}}</td>                      
                      <td>
                        {{$item->delivery_no}}
                        @if(isset($item->is_multi_order) && $item->is_multi_order)
                          <span class="badge badge-secondary ml-1">Multi-Order</span>
                        @endif
                      </td>
                      <td>{{$item->job_no}}</td>
                      <td>{{$item->customer_no}} - {{$item->fname}} {{$item->lname}}</td>
                      <td>
                        <div class="btn-group">
                            <button class="btn <?php
                                if($item->delivery_status == 1){ echo 'btn-warning'; $status = 'Pending'; }
                                elseif($item->delivery_status == 2){ echo 'btn-info'; $status = 'Dispatched'; }
                                elseif($item->delivery_status == 3){ echo 'btn-success'; $status = 'Delivered'; }
                                elseif($item->delivery_status == 4){ echo 'btn-danger'; $status = 'Returned'; }
                                else{ echo 'btn-danger'; $status = 'Unknown'; }
                               ?> btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              {{$status}}
                            </button>
                            <div class="dropdown-menu">
                              <a class="dropdown-item" href="{{ route('delivery.updateStatus', ['id' => $item->delivery_id, 'status' => '1']) }}">Pending</a>
                              <a class="dropdown-item" href="{{ route('delivery.updateStatus', ['id' => $item->delivery_id, 'status' => '2']) }}">Dispatched</a>
                              <a class="dropdown-item" href="{{ route('delivery.updateStatus', ['id' => $item->delivery_id, 'status' => '3']) }}">Delivered</a>
                              <a class="dropdown-item" href="{{ route('delivery.updateStatus', ['id' => $item->delivery_id, 'status' => '4']) }}">Returned</a>
                            </div>
                          </div>
                      </td>
                      <td>
                        <a href="{{ route('delivery.show', $item->delivery_id) }}" class="btn btn-info btn-sm">View</a>
                        @if(isset($item->is_multi_order) && $item->is_multi_order)
                          @php
                            // For multi-order deliveries, we need to construct the edit URL with order IDs
                            // We'll use a placeholder for now and handle this with JavaScript or controller logic
                          @endphp
                          <a href="{{ route('delivery.show', $item->delivery_id) }}?edit=1" class="btn btn-primary btn-sm">Edit</a>
                        @else
                          <a href="{{ route('delivery.edit', $item->delivery_id) }}" class="btn btn-primary btn-sm">Edit</a>
                        @endif
                        @if($item->delivery_status == 3)
                          <a href="{{ route('delivery-return.create', $item->delivery_id) }}" class="btn btn-warning btn-sm">Return</a>
                        @endif
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Order No</th>
                    <th>Job No</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Action</th>
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

<!-- Multi-Order Delivery Modal -->
<div class="modal fade" id="multiOrderDeliveryModal" tabindex="-1" role="dialog" aria-labelledby="multiOrderDeliveryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="multiOrderDeliveryModalLabel">Create Multi-Order Delivery</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="multiOrderDeliveryForm">
          <div class="form-group">
            <label for="customerSelect">Select Customer</label>
            <select class="form-control select2 w-100" id="customerSelect" name="customer_id" required style="width: 100% !important;">
              <option value="">Choose a customer...</option>
              @foreach($customers ?? [] as $customer)
                <option value="{{ $customer->customer_id }}">{{ $customer->customer_no }} - {{ $customer->fname }} {{ $customer->lname }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group" id="ordersGroup" style="display: none;">
            <label for="ordersSelect">Select Orders</label>
            <select class="form-control select2 w-100" id="ordersSelect" name="order_ids[]" multiple required style="width: 100% !important;">
              <!-- Orders will be loaded via AJAX -->
            </select>
            <small class="form-text text-muted">Use the search box to find orders, then select multiple orders</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="createMultiOrderDelivery" disabled>Create Multi-Order Delivery</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2 for the customer dropdown
    $('#customerSelect').select2({
        placeholder: 'Choose a customer...',
        allowClear: true
    });

    // When customer is selected, load their orders
    $('#customerSelect').change(function() {
        var customerId = $(this).val();
        if (customerId) {
            loadCustomerOrders(customerId);
            $('#ordersGroup').show();
        } else {
            $('#ordersGroup').hide();
            // Destroy and reinitialize the orders select2
            $('#ordersSelect').select2('destroy').empty().select2({
                placeholder: 'Select orders...',
                allowClear: true,
                multiple: true
            });
            $('#createMultiOrderDelivery').prop('disabled', true);
        }
    });

    // When orders are selected, enable the create button
    $(document).on('change', '#ordersSelect', function() {
        var selectedOrders = $(this).val();
        $('#createMultiOrderDelivery').prop('disabled', !selectedOrders || selectedOrders.length === 0);
    });

    // Create multi-order delivery
    $('#createMultiOrderDelivery').click(function() {
        var customerId = $('#customerSelect').val();
        var orderIds = $('#ordersSelect').val();

        if (customerId && orderIds && orderIds.length > 0) {
            // Redirect to standard delivery creation page with multi-order parameters
            var url = '{{ route("delivery.add") }}?customer_id=' + customerId + '&order_ids=' + orderIds.join(',');
            window.location.href = url;
        }
    });

    function loadCustomerOrders(customerId) {
        $.ajax({
            url: '/ajax/customer-orders/' + customerId,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                var ordersSelect = $('#ordersSelect');

                // Destroy existing Select2 instance
                ordersSelect.select2('destroy');

                // Clear and populate options
                ordersSelect.empty();

                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(index, order) {
                        ordersSelect.append('<option value="' + order.order_id + '">' +
                            order.job_no + ' - ' + order.order_date + ' (Status: ' + order.status + ')</option>');
                    });
                } else {
                    ordersSelect.append('<option value="">No available orders for this customer</option>');
                }

                // Reinitialize Select2 with search functionality
                ordersSelect.select2({
                    placeholder: 'Search and select orders...',
                    allowClear: true,
                    multiple: true,
                    width: '100%'
                });
            },
            error: function() {
                alert('Error loading customer orders. Please try again.');
            }
        });
    }
});
</script>

@endsection