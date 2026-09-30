@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Process Travel Card Details</h4>
            <div class="card-header-action">
              @if($ptc->stock_status == 6)
                <div class="dropdown d-inline mr-2">
                  <button class="btn btn-success dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-tasks"></i> Actions
                  </button>
                  <div class="dropdown-menu dropdown-menu-right">
                    @if(!$isFinalStage)
                      <form action="{{ route('ptc.next.stage', $ptc->stock_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Move PTC to next stage?');">
                        @csrf
                        <button type="submit" class="dropdown-item">
                          <i class="fas fa-step-forward text-warning"></i> Move to Next Stage
                        </button>
                      </form>
                      <div class="dropdown-divider"></div>
                    @endif
                    @php
                      // Complete when the end stage is fully received, otherwise close early
                      $canComplete = $stageSummary['can_complete'];
                      $endStageName = $stageSummary['end_stage']->name ?? 'the end stage';
                      $finishBlocked = $finishBlockers['outstanding']->isNotEmpty() || $finishBlockers['virtual']->isNotEmpty();
                    @endphp
                    <form action="{{ route('ptc.close', $ptc->stock_id) }}" method="POST" class="d-inline"
                      onsubmit="return confirm(@js($canComplete
                        ? 'Complete this PTC? ' . $endStageName . ' is fully received. Nothing is moved in stock.'
                        : 'Close this PTC early? Production has not finished ' . $endStageName . '. Stages keep their actual status; nothing is moved in stock.'));">
                      @csrf
                      <button type="submit" class="dropdown-item" {{ $finishBlocked ? 'disabled' : '' }}>
                        @if($canComplete)
                          <i class="fas fa-check-circle text-success"></i> Complete PTC
                        @else
                          <i class="fas fa-stop-circle text-danger"></i> Close PTC (stop early)
                        @endif
                      </button>
                    </form>
                    @if($finishBlocked)
                      <span class="dropdown-item-text small text-muted" style="max-width: 280px; white-space: normal;">
                        @if($finishBlockers['outstanding']->isNotEmpty())
                          Receive the outstanding product first.
                        @else
                          Transfer or release the remaining PTC stock first.
                        @endif
                      </span>
                    @elseif(!$canComplete)
                      <span class="dropdown-item-text small text-muted" style="max-width: 280px; white-space: normal;">
                        Complete PTC becomes available once {{ $endStageName }} is fully received.
                      </span>
                    @endif
                  </div>
                </div>
              @endif
              <div class="dropdown d-inline mr-2">
                <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                  <i class="fas fa-print"></i> Print
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                  <a class="dropdown-item" href="{{ route('ptc.print', $ptc->stock_id) }}" target="_blank">
                    <i class="fas fa-file-pdf text-danger"></i> Print PTC
                  </a>
                </div>
              </div>
              <a href="{{ route('ptc') }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <!-- PTC Header Info -->
            <div class="row mb-4">
              <div class="col-md-3">
                <strong>PTC No:</strong> PTC-{{ $ptc->stock_no }}
              </div>
              <div class="col-md-3">
                <strong>Date:</strong> {{ \carbon\Carbon::parse($ptc->stock_date)->format('d-m-Y') }}
              </div>
              <div class="col-md-3">
                <strong>Order:</strong> {{ $ptc->job_no ?? 'Default PTC' }}
              </div>
              <div class="col-md-3">
                <strong>Status:</strong>
                @if($ptc->stock_status == 6)
                  <span class="badge badge-warning">In Progress</span>
                @elseif($ptc->stock_status == \App\Models\Stock::STATUS_PTC_COMPLETED)
                  <span class="badge badge-success">Completed</span>
                @elseif($ptc->stock_status == \App\Models\Stock::STATUS_PTC_CLOSED)
                  <span class="badge badge-dark">Closed Early</span>
                @endif
              </div>
            </div>

            <div class="row mb-4">
              <div class="col-md-3">
                <strong>Product:</strong> {{ $product ? $product->name . ' - ' . $product->size_name : 'N/A' }}
              </div>
              <div class="col-md-3">
                <strong>Order Qty:</strong> <span class="badge badge-info">{{ $orderQuantity ?? 'N/A' }}</span>
              </div>
              <div class="col-md-3">
                @php
                  // Extract quantity from description [QTY:X] format
                  $ptcQty = 1;
                  if (preg_match('/\[QTY:(\d+)\]/', $ptc->description ?? '', $matches)) {
                      $ptcQty = $matches[1];
                  }
                @endphp
                <strong>PTC Qty:</strong> {{ $ptcQty }}
              </div>
              <div class="col-md-3">
                @if($ptc->stock_status != 6)
                  <strong>{{ $ptc->stock_status == \App\Models\Stock::STATUS_PTC_COMPLETED ? 'Completed At Stage:' : 'Closed At Stage:' }}</strong>
                  <span class="badge badge-dark">{{ $ptc->current_stage_name ?? 'N/A' }}</span>
                @else
                  <strong>Current Stage:</strong>
                  <span class="badge badge-primary">{{ $ptc->current_stage_name ?? 'N/A' }}</span>
                @endif
              </div>
            </div>

            <!-- Stage Progress: actual status of each stage from its issuances and receipts -->
            @php
              $stageStatusLabels = [
                'completed' => ['badge-success', 'fa-check', 'Completed'],
                'partial' => ['badge-warning', 'fa-adjust', 'Partially received'],
                'in_progress' => ['badge-info', 'fa-hourglass-half', 'Issued, not received'],
                'not_started' => ['badge-secondary', 'fa-minus', $ptc->stock_status != 6 ? 'Not processed' : 'Not started'],
                'outside' => ['badge-light', 'fa-minus', 'Not in this PTC'],
              ];
            @endphp
            <div class="row mb-4">
              <div class="col-md-12">
                <label><strong>Stage Progress</strong></label>
                <span class="ml-3 text-muted">
                  Production ended at:
                  <strong>{{ $stageSummary['ended_at']->name ?? 'No output received yet' }}</strong>
                </span>
                <div class="d-flex flex-wrap align-items-start mt-2">
                  @foreach($stageSummary['stages'] as $index => $stageRow)
                    @php [$badgeClass, $icon, $label] = $stageStatusLabels[$stageRow->status]; @endphp
                    <div class="mr-2 mb-2 text-center">
                      <div class="badge {{ $badgeClass }} p-2 {{ $stageRow->is_current ? 'border border-primary' : '' }}" title="{{ $label }}">
                        <i class="fas {{ $icon }}"></i> {{ $index + 1 }}. {{ $stageRow->name }}
                        @if($stageRow->is_current) <span class="badge badge-primary ml-1">Current</span> @endif
                        @if($stageRow->is_end) <span class="badge badge-light ml-1">End</span> @endif
                      </div>
                      <div class="small text-muted">
                        {{ $label }}
                        @if($stageRow->issuance_count)
                          <br>Received {{ $stageRow->received + 0 }}@if($stageRow->issued) / Issued {{ $stageRow->issued + 0 }}@endif
                          @if($stageRow->outstanding > 0)<br><span class="text-danger">Outstanding {{ $stageRow->outstanding + 0 }}</span>@endif
                        @endif
                      </div>
                    </div>
                    @if(!$loop->last)
                      <div class="mr-2 mb-2 pt-1"><i class="fas fa-arrow-right text-muted"></i></div>
                    @endif
                  @endforeach
                </div>
              </div>
            </div>

            <!-- PTC Stock (virtual): product received on this PTC, not part of general stock -->
            <hr>
            <h5><i class="fas fa-boxes text-info"></i> PTC Stock
              <small class="text-muted">- held in this PTC only; not available in general stock until released</small>
            </h5>
            <div class="table-responsive">
              <table class="table table-striped table-bordered table-sm">
                <thead class="thead-light">
                  <tr>
                    <th>Product</th>
                    <th>Stage</th>
                    <th class="text-right">Received</th>
                    <th class="text-right">Transferred to Stage</th>
                    <th class="text-right">Released to Stock</th>
                    <th class="text-right">Available</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($virtualStock as $vs)
                    <tr>
                      <td>{{ $vs->article_no }} - {{ $vs->product_name }} ({{ $vs->size_name }})</td>
                      <td>{{ $vs->stage_name ?? 'N/A' }}</td>
                      <td class="text-right">{{ $vs->received + 0 }}</td>
                      <td class="text-right">{{ $vs->transferred + 0 }}</td>
                      <td class="text-right">{{ $vs->released + 0 }}</td>
                      <td class="text-right"><strong>{{ $vs->available + 0 }}</strong></td>
                      <td>
                        @if($vs->available > 0)
                          @if($ptc->stock_status == 6)
                            <a href="{{ route('ptc.issue.form', ['id' => $ptc->stock_id, 'source' => 'ptc', 'stage' => $vs->stage_id]) }}" class="btn btn-sm btn-primary">
                              <i class="fas fa-arrow-right"></i> Transfer to Stage
                            </a>
                          @endif
                          <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#releaseModal{{ $vs->product_type_id }}_{{ $vs->stage_id }}">
                            <i class="fas fa-warehouse"></i> Release to Stock
                          </button>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="text-center">No product has been received into this PTC yet.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            @if($consumption['materials']->isNotEmpty() || $consumption['components']->isNotEmpty())
              <h6 class="mt-3">Material & Component Consumption</h6>
              <div class="table-responsive">
                <table class="table table-striped table-bordered table-sm">
                  <thead class="thead-light">
                    <tr>
                      <th>Type</th>
                      <th>Item</th>
                      <th class="text-right">Issued</th>
                      <th class="text-right">Returned</th>
                      <th class="text-right">Consumed</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($consumption['materials'] as $row)
                      <tr>
                        <td><span class="badge badge-info">Material</span></td>
                        <td>{{ $row->name }}</td>
                        <td class="text-right">{{ $row->issued + 0 }} {{ $row->unit }}</td>
                        <td class="text-right">{{ $row->returned + 0 }} {{ $row->unit }}</td>
                        <td class="text-right">{{ $row->consumed + 0 }} {{ $row->unit }}</td>
                      </tr>
                    @endforeach
                    @foreach($consumption['components'] as $row)
                      <tr>
                        <td><span class="badge badge-warning">Component</span></td>
                        <td>{{ $row->article_no }} - {{ $row->name }} ({{ $row->size_name }})</td>
                        <td class="text-right">{{ $row->issued + 0 }}</td>
                        <td class="text-right">{{ $row->returned + 0 }}</td>
                        <td class="text-right">{{ $row->consumed + 0 }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif

            @php
              // Remove [QTY:X] from description for display
              $cleanDescription = preg_replace('/\s*\[QTY:\d+\]/', '', $ptc->description ?? '');
              $cleanDescription = trim($cleanDescription);
            @endphp
            @if($cleanDescription)
              <hr>
              <h5>Description</h5>
              <div>{!! $cleanDescription !!}</div>
            @endif

            <!-- Issuance Records List (Unified with Receiving) -->
            <hr>
            <h5><i class="fas fa-arrow-circle-right text-success"></i> Issuance Records
              @if($ptc->stock_status == 6)
                <a href="{{ route('ptc.issue.form', $ptc->stock_id) }}" class="btn btn-sm btn-primary float-right">
                  <i class="fas fa-plus"></i> New Issuance
                </a>
              @endif
            </h5>
            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead class="thead-light">
                  <tr>
                    <th>Sr.</th>
                    <th>Issuance Reference</th>
                    <th>For Stage</th>
                    <th>Date</th>
                    <th>Issued To</th>
                    <th class="text-right">Product Issued</th>
                    <th class="text-right">Received</th>
                    <th class="text-right">Outstanding</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($issuances as $index => $issuance)
                    @php
                      $relatedReceivings = $receivingsByIssueId[$issuance->stock_id] ?? collect();
                      $isReceived = $relatedReceivings->count() > 0;
                      // Issuance number by creation order (initial issuance = I001)
                      $issuanceSeqNo = $issuanceSeq[$issuance->stock_id] ?? str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                      $balance = $balances[$issuance->stock_id] ?? null;
                    @endphp
                    <tr>
                      <td>{{ $index + 1 }}</td>
                      <td>
                        <strong>PTC-{{ $ptc->stock_no }}</strong> / I{{ $issuanceSeqNo }}
                        @if($issuance->is_ptc_master == 1)
                          <span class="badge badge-info">Initial</span>
                        @endif
                      </td>
                      <td><span class="badge badge-primary">{{ $issuance->stage_name ?? 'N/A' }}</span></td>
                      <td>{{ \carbon\Carbon::parse($issuance->stock_date)->format('d-m-Y') }}</td>
                      <td>{{ $issuance->employee_name ?? $issuance->vendor_name ?? '-' }}</td>
                      <td class="text-right">{{ $balance ? collect($balance['products'])->sum('issued') + 0 : '-' }}</td>
                      <td class="text-right">{{ $balance ? $balance['produced'] + 0 : '-' }}</td>
                      <td class="text-right {{ $balance && $balance['outstanding'] > 0 ? 'text-danger font-weight-bold' : '' }}">{{ $balance ? $balance['outstanding'] + 0 : '-' }}</td>
                      <td>
                        @if(($balance['status'] ?? null) === 'received')
                          <span class="badge badge-success"><i class="fas fa-check"></i> Received ({{ $relatedReceivings->count() }})</span>
                        @elseif(($balance['status'] ?? null) === 'partial')
                          <span class="badge badge-warning"><i class="fas fa-adjust"></i> Partially Received ({{ $relatedReceivings->count() }})</span>
                        @else
                          <span class="badge badge-secondary"><i class="fas fa-clock"></i> Not Received</span>
                        @endif
                      </td>
                      <td>
                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#movementModal{{ $issuance->stock_id }}">
                          <i class="fas fa-eye"></i> View
                        </button>
                        @if($isReceived)
                          <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#receivingModal{{ $issuance->stock_id }}">
                            <i class="fas fa-arrow-circle-down"></i> View Receiving ({{ $relatedReceivings->count() }})
                          </button>
                        @endif
                        @if($ptc->stock_status == 6 && ($balance['can_receive'] ?? false))
                          <a href="{{ route('ptc.receive.issuance', [$ptc->stock_id, $issuance->stock_id]) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Add Receiving
                          </a>
                        @endif
                        @if(!$isReceived)
                          {{-- Edit button only visible for issuances that have NOT been received yet --}}
                          @if($issuance->is_ptc_master == 1)
                            <a href="{{ route('ptc.edit', $issuance->stock_id) }}" class="btn btn-sm btn-warning">
                              <i class="fas fa-edit"></i> Edit
                            </a>
                          @else
                            <a href="{{ route('ptc.issue.edit', [$ptc->stock_id, $issuance->stock_id]) }}" class="btn btn-sm btn-warning">
                              <i class="fas fa-edit"></i> Edit
                            </a>
                          @endif
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="10" class="text-center">No issuance records yet.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <!-- Stage Movement History (Combined) -->
            @if($movements && $movements->count() > 0)
              <hr>
              <h5><i class="fas fa-history text-warning"></i> Complete Movement History</h5>
              <div class="table-responsive">
                <table class="table table-striped table-bordered">
                  <thead class="thead-light">
                    <tr>
                      <th>Sr.</th>
                      <th>Reference</th>
                      <th>Type</th>
                      <th>Stage</th>
                      <th>Date</th>
                      <th>By</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                      // Map of issuance stock_ids to their sequence numbers (creation order)
                      $issuanceSeqMap = $issuanceSeq->all();
                    @endphp
                    @foreach($movements as $index => $movement)
                      @php
                        // Format display reference based on type
                        if ($movement->is_ptc_master == 1) {
                          $displayRef = 'PTC-' . $ptc->stock_no . ' / I001';
                        } elseif ($movement->stock_type == 2) {
                          // Issuance - find sequence from map
                          $issSeq = $issuanceSeqMap[$movement->stock_id] ?? \App\Models\Stock::ptcDisplayNo($movement->stock_no, $ptc->stock_no);
                          $displayRef = 'PTC-' . $ptc->stock_no . ' / I' . $issSeq;
                        } else {
                          // Receiving - use stored format directly (R{n}-I{seq})
                          $displayRef = 'PTC-' . $ptc->stock_no . ' / ' . \App\Models\Stock::ptcDisplayNo($movement->stock_no, $ptc->stock_no);
                        }
                      @endphp
                      <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $displayRef }}</td>
                        <td>
                          @if($movement->is_ptc_master == 1)
                            <span class="badge badge-warning">Initial Issue</span>
                          @elseif($movement->stock_status == \App\Models\Stock::STATUS_PTC_RELEASED)
                            <span class="badge badge-dark">Released to Stock</span>
                          @elseif($movement->stock_type == 1)
                            <span class="badge badge-success">Receive</span>
                          @else
                            <span class="badge badge-primary">Issue</span>
                          @endif
                        </td>
                        <td>
                          @if($movement->stock_type == 1)
                            {{ $movement->stage_name ?? 'N/A' }}
                          @else
                            {{ $movement->issue_stage_name ?? $movement->stage_name ?? 'N/A' }}
                          @endif
                        </td>
                        <td>{{ \carbon\Carbon::parse($movement->stock_date)->format('d-m-Y') }}</td>
                        <td>{{ $movement->employee_name ?? $movement->vendor_name ?? '-' }}</td>
                        <td>
                          <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#movementModal{{ $movement->stock_id }}">
                            <i class="fas fa-eye"></i> View Items
                          </button>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif

          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Movement Detail Modals -->
@if($movements && $movements->count() > 0)
  @php
    // Map of issuance stock_ids to their sequence numbers (creation order)
    $modalIssuanceSeqMap = $issuanceSeq->all();
  @endphp
  @foreach($movements as $movement)
    @php
      // Format display reference for modal
      if ($movement->is_ptc_master == 1) {
        $modalDisplayRef = 'PTC-' . $ptc->stock_no . ' / I001';
      } elseif ($movement->stock_type == 2) {
        $issSeq = $modalIssuanceSeqMap[$movement->stock_id] ?? \App\Models\Stock::ptcDisplayNo($movement->stock_no, $ptc->stock_no);
        $modalDisplayRef = 'PTC-' . $ptc->stock_no . ' / I' . $issSeq;
      } else {
        // Receiving - use stored format directly (R{n}-I{seq})
        $modalDisplayRef = 'PTC-' . $ptc->stock_no . ' / ' . \App\Models\Stock::ptcDisplayNo($movement->stock_no, $ptc->stock_no);
      }
    @endphp
    <div class="modal fade" id="movementModal{{ $movement->stock_id }}" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              @if($movement->stock_status == \App\Models\Stock::STATUS_PTC_RELEASED)
                <span class="badge badge-dark">Released to Stock</span>
              @elseif($movement->stock_type == 1)
                <span class="badge badge-success">Receive</span>
              @else
                <span class="badge badge-primary">Issue</span>
              @endif
              {{ $modalDisplayRef }} - {{ $movement->stage_name ?? $movement->issue_stage_name ?? 'N/A' }}
            </h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-md-4"><strong>Date:</strong> {{ $movement->stock_date ? \Carbon\Carbon::parse($movement->stock_date)->format('d-m-Y') : 'N/A' }}</div>
              <div class="col-md-4"><strong>By:</strong> {{ $movement->employee_name ?? $movement->vendor_name ?? '-' }}</div>
              <div class="col-md-4"><strong>Notes:</strong> {{ $movement->description ?? '-' }}</div>
            </div>
            <div class="table-responsive">
              <table class="table table-striped table-sm">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Type</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    @if($movement->stock_type == 1)
                      <th>Work Log</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @php $modalRowIndex = 1; @endphp
                  @forelse($movementItems[$movement->stock_id] ?? [] as $item)
                    <tr>
                      <td>{{ $modalRowIndex++ }}</td>
                      @if($item->component_product_type_id)
                        <td><span class="badge badge-warning">Component</span></td>
                        <td>{{ $item->component_article_no ?? '' }} - {{ $item->component_name ?? 'N/A' }} ({{ $item->component_size ?? '' }})</td>
                      @elseif($item->material_id > 0)
                        <td><span class="badge badge-info">Material</span></td>
                        <td>{{ $item->material_name ?? 'N/A' }}</td>
                      @else
                        <td><span class="badge badge-success">Product</span>
                          @if($item->ptc_virtual)<span class="badge badge-light">PTC stock</span>@endif
                        </td>
                        <td>{{ $item->product_name ?? 'N/A' }} - {{ $item->size_name ?? 'N/A' }} ({{ $item->stage_name ?? 'N/A' }})</td>
                      @endif
                      <td>{{ $item->quantity }}</td>
                      @if($movement->stock_type == 1)
                        <td>{{ $item->work_logs ?? '-' }}</td>
                      @endif
                    </tr>
                  @empty
                    <tr>
                      <td colspan="{{ $movement->stock_type == 1 ? 5 : 4 }}" class="text-center">No items.</td>
                    </tr>
                  @endforelse
                </tbody>
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

<!-- Modal for Initial PTC Record -->
<div class="modal fade" id="movementModal{{ $ptc->stock_id }}" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <span class="badge badge-info">Initial Issue</span>
          {{ $ptc->stock_no }} - {{ $stages->first()->name ?? 'N/A' }}
        </h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-4"><strong>Date:</strong> {{ $ptc->stock_date ? \Carbon\Carbon::parse($ptc->stock_date)->format('d-m-Y') : 'N/A' }}</div>
          <div class="col-md-4"><strong>By:</strong> {{ $ptc->employee_name ?? $ptc->vendor_name ?? '-' }}</div>
          <div class="col-md-4"><strong>Notes:</strong> {{ $ptc->description ?? '-' }}</div>
        </div>
        <div class="table-responsive">
          <table class="table table-striped table-sm">
            <thead>
              <tr>
                <th>Sr.</th>
                <th>Type</th>
                <th>Item</th>
                <th>Quantity</th>
              </tr>
            </thead>
            <tbody>
              @php $modalRowIndex = 1; @endphp
              @forelse($movementItems[$ptc->stock_id] ?? [] as $item)
                <tr>
                  <td>{{ $modalRowIndex++ }}</td>
                  @if($item->component_product_type_id)
                    <td><span class="badge badge-warning">Component</span></td>
                    <td>{{ $item->component_article_no ?? '' }} - {{ $item->component_name ?? 'N/A' }} ({{ $item->component_size ?? '' }})</td>
                  @elseif($item->material_id > 0)
                    <td><span class="badge badge-info">Material</span></td>
                    <td>{{ $item->material_name ?? 'N/A' }}</td>
                  @else
                    <td><span class="badge badge-success">Product</span></td>
                    <td>{{ $item->product_name ?? 'N/A' }} - {{ $item->size_name ?? 'N/A' }} ({{ $item->stage_name ?? 'N/A' }})</td>
                  @endif
                  <td>{{ $item->quantity }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center">No items.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Receiving Modals for each Issuance (shows all related receiving records) -->
@foreach($issuances as $issIdx => $issuance)
  @php
    $relatedReceivings = $receivingsByIssueId[$issuance->stock_id] ?? collect();
    $issuanceSeqNoForModal = $issuanceSeq[$issuance->stock_id] ?? str_pad($issIdx + 1, 3, '0', STR_PAD_LEFT);
  @endphp
  @if($relatedReceivings->count() > 0)
    <div class="modal fade" id="receivingModal{{ $issuance->stock_id }}" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">
              <i class="fas fa-arrow-circle-down"></i> Receiving Records for: PTC-{{ $ptc->stock_no }} / I{{ $issuanceSeqNoForModal }}
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-info mb-3">
              <strong>Issuance:</strong> PTC-{{ $ptc->stock_no }} / I{{ $issuanceSeqNoForModal }} |
              <strong>Stage:</strong> {{ $issuance->stage_name ?? 'N/A' }} |
              <strong>Date:</strong> {{ \carbon\Carbon::parse($issuance->stock_date)->format('d-m-Y') }} |
              <strong>Total Receivings:</strong> {{ $relatedReceivings->count() }}
            </div>

            @foreach($relatedReceivings as $recIdx => $receiving)
              <div class="card mb-3 {{ $loop->last ? '' : 'border-bottom' }}">
                <div class="card-header bg-light py-2">
                  <strong>PTC-{{ $ptc->stock_no }} / {{ \App\Models\Stock::ptcDisplayNo($receiving->stock_no, $ptc->stock_no) }}</strong>
                  <span class="float-right">
                    <span class="badge badge-success">{{ $receiving->stage_name ?? 'N/A' }}</span>
                    {{ \carbon\Carbon::parse($receiving->stock_date)->format('d-m-Y') }}
                  </span>
                </div>
                <div class="card-body py-2">
                  <div class="row mb-2">
                    <div class="col-md-6"><strong>Received From:</strong> {{ $receiving->employee_name ?? $receiving->vendor_name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Notes:</strong> {{ $receiving->description ?? '-' }}</div>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-striped table-sm mb-0">
                      <thead>
                        <tr>
                          <th>Sr.</th>
                          <th>Type</th>
                          <th>Item</th>
                          <th>Quantity</th>
                          <th>Work Log</th>
                        </tr>
                      </thead>
                      <tbody>
                        @php $recRowIndex = 1; @endphp
                        @forelse($movementItems[$receiving->stock_id] ?? [] as $item)
                          <tr>
                            <td>{{ $recRowIndex++ }}</td>
                            @if($item->component_product_type_id)
                              <td><span class="badge badge-warning">Component</span></td>
                              <td>{{ $item->component_article_no ?? '' }} - {{ $item->component_name ?? 'N/A' }} ({{ $item->component_size ?? '' }})</td>
                            @elseif($item->material_id > 0)
                              <td><span class="badge badge-info">Material</span></td>
                              <td>{{ $item->material_name ?? 'N/A' }}</td>
                            @else
                              <td><span class="badge badge-success">Product</span></td>
                              <td>{{ $item->product_name ?? 'N/A' }} - {{ $item->size_name ?? 'N/A' }} ({{ $item->stage_name ?? 'N/A' }})</td>
                            @endif
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->work_logs ?? '-' }}</td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="5" class="text-center">No items.</td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  @endif
@endforeach

<!-- Release PTC stock to general stock -->
@foreach($virtualStock->where('available', '>', 0) as $vs)
  <div class="modal fade" id="releaseModal{{ $vs->product_type_id }}_{{ $vs->stage_id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form action="{{ route('ptc.release', $ptc->stock_id) }}" method="POST" onsubmit="return confirm('Release this quantity to general stock? It will no longer be held in this PTC.');">
          @csrf
          <input type="hidden" name="product_type_id" value="{{ $vs->product_type_id }}">
          <input type="hidden" name="stage_id" value="{{ $vs->stage_id }}">
          <div class="modal-header">
            <h5 class="modal-title">Release to General Stock</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
          </div>
          <div class="modal-body">
            <p class="mb-3">
              <strong>{{ $vs->article_no }} - {{ $vs->product_name }} ({{ $vs->size_name }})</strong>
              at <span class="badge badge-primary">{{ $vs->stage_name ?? 'N/A' }}</span><br>
              Available in this PTC: <strong>{{ $vs->available + 0 }}</strong>
            </p>
            <div class="form-group">
              <label>Quantity to Release <span class="text-danger">*</span></label>
              <input type="number" class="form-control" name="quantity" min="0.0001" max="{{ $vs->available }}" step="any" required>
              <small class="form-text text-muted">Only the released quantity becomes available in general stock (and to other PTCs). The rest stays in this PTC.</small>
            </div>
            <div class="form-group">
              <label>Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="stock_date" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="form-group mb-0">
              <label>Notes</label>
              <textarea class="form-control" name="description" rows="2"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success"><i class="fas fa-warehouse"></i> Release</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endforeach
@endsection

