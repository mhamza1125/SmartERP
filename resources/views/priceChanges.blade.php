@extends('index')
@section('content')
{{--
  Price Change History (PartyPriceController::changes): every buying (vendor) or selling (customer)
  price change, filtered by product / material, vendor / customer, user and date. 50 a page.
--}}
@php
  $isVendor = $partyType === 'vendor';
  $partyLabel = $isVendor ? 'Vendor' : 'Customer';
  $canVendor = auth()->user()->can('access', App\Models\Purchase::class);
  $canCustomer = auth()->user()->can('access', App\Models\Customer::class);
  $item = $filters['item'] ?? '';
  $filtered = collect($filters)->filter()->isNotEmpty();
@endphp
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Price Change History - {{ $isVendor ? 'Buying (Vendors)' : 'Selling (Customers)' }}</h4>
            <div class="card-header-action">
              <div class="btn-group">
                @if($canVendor)<a href="{{ route('partyPrice.changes', 'vendor') }}" class="btn {{ $isVendor ? 'btn-primary' : 'btn-outline-primary' }}">Buying Prices</a>@endif
                @if($canCustomer)<a href="{{ route('partyPrice.changes', 'customer') }}" class="btn {{ $isVendor ? 'btn-outline-primary' : 'btn-primary' }}">Selling Prices</a>@endif
              </div>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('partyPrice.changes', $partyType) }}" method="GET">
              <div class="row">
                <div class="col-md-4 form-group">
                  <label>Product{{ $isVendor ? ' / Material' : '' }}</label>
                  <select class="form-control select2" name="item" style="width: 100%">
                    <option value="">All</option>
                    @if($options['products']->count())
                      <optgroup label="Products">
                        @foreach($options['products'] as $option)
                          <option value="{{ $option->value }}" {{ $item === $option->value ? 'selected' : '' }}>{{ $option->label }}</option>
                        @endforeach
                      </optgroup>
                    @endif
                    @if($options['materials']->count())
                      <optgroup label="Materials">
                        @foreach($options['materials'] as $option)
                          <option value="{{ $option->value }}" {{ $item === $option->value ? 'selected' : '' }}>{{ $option->label }}</option>
                        @endforeach
                      </optgroup>
                    @endif
                  </select>
                </div>
                <div class="col-md-3 form-group">
                  <label>{{ $partyLabel }}</label>
                  <select class="form-control select2" name="party_id" style="width: 100%">
                    <option value="">All</option>
                    @foreach($options['parties'] as $option)
                      <option value="{{ $option->value }}" {{ (string) ($filters['party_id'] ?? '') === (string) $option->value ? 'selected' : '' }}>{{ $option->label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2 form-group">
                  <label>User</label>
                  <select class="form-control" name="user_id">
                    <option value="">All</option>
                    @foreach($options['users'] as $option)
                      <option value="{{ $option->value }}" {{ (string) ($filters['user_id'] ?? '') === (string) $option->value ? 'selected' : '' }}>{{ $option->label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3 form-group">
                  <label>Date From / To <small class="text-muted">(optional)</small></label>
                  <div class="d-flex">
                    <input type="text" class="form-control datepicker mr-1" name="dfrom" value="{{ $filters['dfrom'] ?? '' }}" autocomplete="off">
                    <input type="text" class="form-control datepicker" name="dto" value="{{ $filters['dto'] ?? '' }}" autocomplete="off">
                  </div>
                </div>
              </div>
              <div class="text-right mb-3">
                <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Filter</button>
                @if($filtered)<a href="{{ route('partyPrice.changes', $partyType) }}" class="btn btn-secondary">Clear</a>@endif
              </div>
            </form>

            <p class="text-muted small mb-2">
              {{ $changes->total() }} change{{ $changes->total() === 1 ? '' : 's' }}{{ $filtered ? ' match the filters' : '' }}, newest first.
              "User" is the person who saved the change (by hand, or the {{ $isVendor ? 'purchase' : 'order' }} that changed the price).
            </p>
            @include('priceChangesTable', ['changes' => $changes, 'partyType' => $partyType, 'showParty' => true, 'empty' => 'No price changes match the filters.'])
            <div class="mt-3">{{ $changes->links('pagination::bootstrap-4') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
