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
                    <a class="dropdown-item" href="{{ route('ptc.issue.form', $ptc->stock_id) }}">
                      <i class="fas fa-arrow-circle-right text-primary"></i> Issue for Stage
                    </a>
                    <a class="dropdown-item" href="{{ route('ptc.receive.form', $ptc->stock_id) }}">
                      <i class="fas fa-arrow-circle-down text-success"></i> Receive from Stage
                    </a>
                    <div class="dropdown-divider"></div>
                    @if(!$isFinalStage)
                      <form action="{{ route('ptc.next.stage', $ptc->stock_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Move PTC to next stage?');">
                        @csrf
                        <button type="submit" class="dropdown-item">
                          <i class="fas fa-step-forward text-warning"></i> Move to Next Stage
                        </button>
                      </form>
                    @endif
                    <form action="{{ route('ptc.close', $ptc->stock_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to close/complete this PTC?');">
                      @csrf
                      <button type="submit" class="dropdown-item">
                        <i class="fas fa-check-circle text-danger"></i> Close PTC
                      </button>
                    </form>
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
                <strong>Date:</strong> {{ $ptc->stock_date }}
              </div>
              <div class="col-md-3">
                <strong>Order:</strong> {{ $ptc->job_no ?? 'Default PTC' }}
              </div>
              <div class="col-md-3">
                <strong>Status:</strong>
                @if($ptc->stock_status == 6)
                  <span class="badge badge-warning">In Progress</span>
                @elseif($ptc->stock_status == 7)
                  <span class="badge badge-success">Completed</span>
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
                <strong>Current Stage:</strong>
                <span class="badge {{ $ptc->stock_status == 7 ? 'badge-success' : 'badge-primary' }}">
                  {{ $ptc->current_stage_name ?? 'Completed' }}
                </span>
              </div>
            </div>

            <!-- Stage Progress Bar -->
            <div class="row mb-4">
              <div class="col-md-12">
                <label><strong>Stage Progress</strong></label>
                <div class="d-flex flex-wrap align-items-center">
                  @foreach($stages as $index => $stage)
                    @php
                      $isCompleted = false;
                      $isCurrent = false;
                      if ($ptc->stock_status == 7) {
                        $isCompleted = true;
                      } elseif ($ptc->current_stage_id == $stage->head_id) {
                        $isCurrent = true;
                      } else {
                        // Check if this stage comes before current stage
                        $currentIndex = collect($stages)->search(fn($s) => $s->head_id == $ptc->current_stage_id);
                        $isCompleted = $index < $currentIndex;
                      }
                    @endphp
                    <div class="badge {{ $isCompleted ? 'badge-success' : ($isCurrent ? 'badge-primary' : 'badge-secondary') }} mr-2 mb-2 p-2">
                      @if($isCompleted) <i class="fas fa-check"></i> @endif
                      {{ $index + 1 }}. {{ $stage->name }}
                    </div>
                    @if($index < count($stages) - 1)
                      <div class="mr-2 mb-2"><i class="fas fa-arrow-right text-muted"></i></div>
                    @endif
                  @endforeach
                </div>
              </div>
            </div>

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
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($issuances as $index => $issuance)
                    @php
                      $relatedReceivings = $receivingsByIssueId[$issuance->stock_id] ?? collect();
                      $isReceived = $relatedReceivings->count() > 0;
                      // Format issuance number: I + padded sequence
                      $issuanceSeqNo = str_pad($index + 1, 3, '0', STR_PAD_LEFT);
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
                      <td>{{ $issuance->stock_date }}</td>
                      <td>{{ $issuance->employee_name ?? $issuance->vendor_name ?? '-' }}</td>
                      <td>
                        @if($isReceived)
                          <span class="badge badge-success"><i class="fas fa-check"></i> Received ({{ $relatedReceivings->count() }})</span>
                        @else
                          <span class="badge badge-warning"><i class="fas fa-clock"></i> Not Received</span>
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
                        @if($ptc->stock_status == 6)
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
                      <td colspan="7" class="text-center">No issuance records yet.</td>
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
                      // Build a map of issuance stock_ids to their sequence numbers
                      $issuanceSeqMap = [];
                      foreach($issuances as $idx => $iss) {
                        $issuanceSeqMap[$iss->stock_id] = str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
                      }
                    @endphp
                    @foreach($movements as $index => $movement)
                      @php
                        // Format display reference based on type
                        if ($movement->is_ptc_master == 1) {
                          $displayRef = 'PTC-' . $ptc->stock_no . ' / I001';
                        } elseif ($movement->stock_type == 2) {
                          // Issuance - find sequence from map
                          $issSeq = $issuanceSeqMap[$movement->stock_id] ?? $movement->stock_no;
                          $displayRef = 'PTC-' . $ptc->stock_no . ' / I' . $issSeq;
                        } else {
                          // Receiving - use stored format directly (R{n}-I{seq})
                          $displayRef = 'PTC-' . $ptc->stock_no . ' / ' . $movement->stock_no;
                        }
                      @endphp
                      <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $displayRef }}</td>
                        <td>
                          @if($movement->is_ptc_master == 1)
                            <span class="badge badge-warning">Initial Issue</span>
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
                        <td>{{ $movement->stock_date }}</td>
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
    // Build maps for modal display
    $modalIssuanceSeqMap = [];
    foreach($issuances as $idx => $iss) {
      $modalIssuanceSeqMap[$iss->stock_id] = str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
    }
  @endphp
  @foreach($movements as $movement)
    @php
      // Format display reference for modal
      if ($movement->is_ptc_master == 1) {
        $modalDisplayRef = 'PTC-' . $ptc->stock_no . ' / I001';
      } elseif ($movement->stock_type == 2) {
        $issSeq = $modalIssuanceSeqMap[$movement->stock_id] ?? $movement->stock_no;
        $modalDisplayRef = 'PTC-' . $ptc->stock_no . ' / I' . $issSeq;
      } else {
        // Receiving - use stored format directly (R{n}-I{seq})
        $modalDisplayRef = 'PTC-' . $ptc->stock_no . ' / ' . $movement->stock_no;
      }
    @endphp
    <div class="modal fade" id="movementModal{{ $movement->stock_id }}" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              @if($movement->stock_type == 1)
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
              <div class="col-md-4"><strong>Date:</strong> {{ $movement->stock_date }}</div>
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
                        <td><span class="badge badge-success">Product</span></td>
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
          <div class="col-md-4"><strong>Date:</strong> {{ $ptc->stock_date }}</div>
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
    $issuanceSeqNoForModal = str_pad($issIdx + 1, 3, '0', STR_PAD_LEFT);
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
              <strong>Date:</strong> {{ $issuance->stock_date }} |
              <strong>Total Receivings:</strong> {{ $relatedReceivings->count() }}
            </div>

            @foreach($relatedReceivings as $recIdx => $receiving)
              <div class="card mb-3 {{ $loop->last ? '' : 'border-bottom' }}">
                <div class="card-header bg-light py-2">
                  <strong>PTC-{{ $ptc->stock_no }} / {{ $receiving->stock_no }}</strong>
                  <span class="float-right">
                    <span class="badge badge-success">{{ $receiving->stage_name ?? 'N/A' }}</span>
                    {{ $receiving->stock_date }}
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
@endsection

