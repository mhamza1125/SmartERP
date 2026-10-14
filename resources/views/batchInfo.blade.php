@extends('index')
@section('content')
@php
  $sourceLabels = ['ptc' => 'PTC', 'purchase' => 'Purchase', 'issuance' => 'Issuance', 'opening' => 'Opening stock', 'split' => 'Split', 'legacy' => 'Legacy'];
  [$sourceLabel, $sourceRoute, $sourceId] = $batch->source ?? ['-', null, null];
  $splittableStages = $batch->stages->where('balance', '>', 0);
@endphp
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Batch {{ $batch->batch_no }}</h4>
            <div class="card-header-action">
              <a href="{{ route('batch.index') }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            @if($errors->any())
              <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                  <div>{{ $error }}</div>
                @endforeach
              </div>
            @endif

            <div class="row">
              <div class="col-md-6">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Product:</b> {{ $batch->article_no }} - {{ $batch->product_name }} (Size {{ $batch->size_name }})</td></tr>
                    <tr><td><b>Source:</b> {{ $sourceLabels[$batch->source_type] ?? $batch->source_type }} &middot;
                      @if($sourceRoute)
                        <a href="{{ route($sourceRoute, $sourceId) }}">{{ $sourceLabel }}</a>
                      @else
                        {{ $sourceLabel }}
                      @endif
                    </td></tr>
                    <tr><td><b>Created:</b> {{ formatDateTime($batch->created_at) }}</td></tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-sm">
                  <tbody>
                    <tr><td><b>Reserved For:</b>
                      @if($batch->order_id)
                        <a href="{{ route('order.status', $batch->order_id) }}" class="badge badge-warning">{{ $batch->job_no ?? 'Order ' . $batch->order_id }}</a>
                      @else
                        <span class="badge badge-success">Free stock</span>
                      @endif
                    </td></tr>
                    <tr><td><b>Usable Stock:</b> {{ number_format($batch->balance) }} {{ $batch->unit_name }}
                      @if($batch->rejected != 0)
                        &middot; <span class="text-warning">Rejected: {{ number_format($batch->rejected) }}</span>
                      @endif
                    </td></tr>
                    @if($batch->description)
                      <tr><td><b>Notes:</b> {!! nl2br(e($batch->description)) !!}</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <h6><i class="fas fa-layer-group"></i> Stock by Stage</h6>
                <table class="table table-sm table-bordered">
                  <thead class="thead-light">
                    <tr><th>Stage</th><th>In</th><th>Out</th><th>Available</th></tr>
                  </thead>
                  <tbody>
                    @forelse($batch->stages as $stage)
                      <tr>
                        <td>{{ $stage->stage_name }}</td>
                        <td>{{ number_format($stage->qty_in) }}</td>
                        <td>{{ number_format($stage->qty_out) }}</td>
                        <td class="{{ $stage->balance < 0 ? 'text-danger' : '' }}">{{ number_format($stage->balance) }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="4" class="text-muted text-center">No stock left in this batch.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
              <div class="col-md-6">
                @if($batch->made_from->isNotEmpty())
                  <h6><i class="fas fa-sitemap"></i> Made From</h6>
                  <table class="table table-sm table-bordered">
                    <thead class="thead-light"><tr><th>Batch</th><th>Stage</th><th>Quantity</th></tr></thead>
                    <tbody>
                      @foreach($batch->made_from as $origin)
                        <tr>
                          <td><a href="{{ route('batch.show', $origin->batch_id) }}">{{ $origin->batch_no }}</a></td>
                          <td>{{ $origin->stage_name ?? 'No stage' }}</td>
                          <td>{{ number_format($origin->quantity) }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                @endif
                @if($batch->children->isNotEmpty())
                  <h6><i class="fas fa-code-branch"></i> Split Into</h6>
                  <table class="table table-sm table-bordered">
                    <thead class="thead-light"><tr><th>Batch</th><th>Reserved For</th></tr></thead>
                    <tbody>
                      @foreach($batch->children as $child)
                        <tr>
                          <td><a href="{{ route('batch.show', $child->batch_id) }}">{{ $child->batch_no }}</a></td>
                          <td>{{ $child->job_no ?? 'Free' }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                @endif
              </div>
            </div>

            @can('edit', App\Models\StockBatch::class)
              <div class="row mt-2">
                <div class="col-md-5">
                  <div class="card card-primary">
                    <div class="card-header"><h4>Change Reservation (whole batch)</h4></div>
                    <div class="card-body">
                      <form action="{{ route('batch.reserve', $batch->batch_id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                          <label>Reserved For</label>
                          <select class="form-control select2" name="order_id">
                            <option value="">Free stock (any order)</option>
                            @foreach($orders as $order)
                              <option value="{{ $order->order_id }}" @selected($batch->order_id == $order->order_id)>{{ $order->job_no }} - {{ $order->fname }} {{ $order->lname }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group">
                          <label>Note</label>
                          <input type="text" class="form-control" name="note" maxlength="500" placeholder="Why the reservation changed">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Reservation</button>
                      </form>
                    </div>
                  </div>
                </div>
                <div class="col-md-7">
                  <div class="card card-warning">
                    <div class="card-header"><h4>Split Part of the Batch</h4></div>
                    <div class="card-body">
                      @if($splittableStages->isEmpty())
                        <p class="text-muted mb-0">Nothing left to split.</p>
                      @else
                        <form action="{{ route('batch.split', $batch->batch_id) }}" method="POST">
                          @csrf
                          <div class="row">
                            <div class="form-group col-md-4">
                              <label>Stage</label>
                              <select class="form-control" name="stage_id" required>
                                @foreach($splittableStages as $stage)
                                  <option value="{{ $stage->stage_id }}" @selected(old('stage_id') == $stage->stage_id)>{{ $stage->stage_name }} ({{ number_format($stage->balance) }})</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="form-group col-md-4">
                              <label>Quantity</label>
                              <input type="number" step="any" min="0" class="form-control" name="quantity" value="{{ old('quantity') }}" required>
                            </div>
                            <div class="form-group col-md-4">
                              <label>Date</label>
                              <input type="date" class="form-control" name="transfer_date" value="{{ old('transfer_date', now()->toDateString()) }}" required>
                            </div>
                          </div>
                          <div class="row">
                            <div class="form-group col-md-6">
                              <label>New Batch Reserved For</label>
                              <select class="form-control select2" name="order_id">
                                <option value="">Free stock (any order)</option>
                                @foreach($orders as $order)
                                  <option value="{{ $order->order_id }}" @selected(old('order_id') == $order->order_id)>{{ $order->job_no }} - {{ $order->fname }} {{ $order->lname }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="form-group col-md-6">
                              <label>Note</label>
                              <input type="text" class="form-control" name="note" maxlength="500" value="{{ old('note') }}">
                            </div>
                          </div>
                          <button type="submit" class="btn btn-warning">Split</button>
                        </form>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @endcan

            <h6 class="mt-2"><i class="fas fa-history"></i> Movement History</h6>
            <div class="table-responsive">
              <table class="table table-sm table-striped table-bordered">
                <thead class="thead-light">
                  <tr>
                    <th>Date</th>
                    <th>Movement</th>
                    <th>Reference</th>
                    <th>Stage</th>
                    <th>In</th>
                    <th>Out</th>
                    <th>Where</th>
                    <th>Order</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($batch->movements as $movement)
                    <tr>
                      <td>{{ formatDate($movement->date) }}</td>
                      <td>{{ $movement->movement }}</td>
                      <td>
                        @if($movement->link)
                          <a href="{{ route($movement->link[0], $movement->link[1]) }}">{{ $movement->reference }}</a>
                        @else
                          {{ $movement->reference }}
                        @endif
                      </td>
                      <td>{{ $movement->stage_name }}</td>
                      <td>{{ $movement->qty_in ? number_format($movement->qty_in) : '' }}</td>
                      <td>{{ $movement->qty_out ? number_format($movement->qty_out) : '' }}</td>
                      <td>{{ $movement->place }}</td>
                      <td>{{ $movement->job_no }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="8" class="text-center text-muted">No movements.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
