@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Move PTC to Next Stage</h4>
            <div class="card-header-action">
              <a href="{{ route('ptc.show', $ptc->stock_id) }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('ptc.move', $ptc->stock_id) }}" method="POST" class="needs-validation" novalidate="">
              @csrf

              <!-- PTC Info -->
              <div class="row mb-3">
                <div class="col-md-3">
                  <strong>PTC No:</strong> PTC-{{ $ptc->stock_no }}
                </div>
                <div class="col-md-3">
                  <strong>Product:</strong> {{ $product ? $product->name . ' - ' . $product->size_name : 'N/A' }}
                </div>
                <div class="col-md-3">
                  <strong>Order:</strong> {{ $ptc->job_no ?? 'Default PTC' }}
                </div>
                <div class="col-md-3">
                  <strong>Quantity:</strong> {{ $ptc->product_quantity ?? 1 }}
                </div>
              </div>

              <!-- Stage Progress -->
              <div class="row mb-4">
                <div class="col-md-12">
                  <label><strong>Stage Progress</strong></label>
                  <div class="d-flex flex-wrap align-items-center">
                    @foreach($stages as $index => $stage)
                      @php
                        $isCompleted = false;
                        $isCurrent = $ptc->current_stage_id == $stage->head_id;
                        $isNext = $ptc->next_stage_id == $stage->head_id;
                        if (!$isCurrent && !$isNext) {
                          $currentIndex = collect($stages)->search(fn($s) => $s->head_id == $ptc->current_stage_id);
                          $isCompleted = $index < $currentIndex;
                        }
                      @endphp
                      <div class="badge {{ $isCompleted ? 'badge-success' : ($isCurrent ? 'badge-warning' : ($isNext ? 'badge-info' : 'badge-secondary')) }} mr-2 mb-2 p-2">
                        @if($isCompleted) <i class="fas fa-check"></i> @endif
                        @if($isCurrent) <i class="fas fa-spinner"></i> @endif
                        @if($isNext) <i class="fas fa-arrow-right"></i> @endif
                        {{ $index + 1 }}. {{ $stage->name }}
                        @if($isCurrent) <small>(Current)</small> @endif
                        @if($isNext) <small>(Next)</small> @endif
                      </div>
                      @if($index < count($stages) - 1)
                        <div class="mr-2 mb-2"><i class="fas fa-arrow-right text-muted"></i></div>
                      @endif
                    @endforeach
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Current Stage</label>
                    <input type="text" class="form-control" value="{{ $currentStage ? $currentStage->name : 'N/A' }}" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Next Stage</label>
                    <input type="text" class="form-control" value="{{ $nextStage ? $nextStage->name : 'Complete PTC' }}" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Movement Date</label>
                    <input type="date" class="form-control" name="stock_date" required value="{{ date('Y-m-d') }}">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Employee / Vendor <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="employee_id" id="employee_id" required>
                      <option value="" selected disabled>Select Employee / Vendor</option>
                      @if($employees->count())
                        @foreach($employees as $item)
                          <option data-type="employee" value="{{ $item->employee_id }}">{{ $item->employee_no }} - {{ $item->name }}</option>
                        @endforeach
                      @endif
                      @if($vendors->count())
                        @foreach($vendors as $item)
                          <option data-type="vendor" value="{{ $item->vendor_id }}">{{ $item->vendor_no }} - {{ $item->fname }}</option>
                        @endforeach
                      @endif
                    </select>
                    <input type="hidden" id="table_name" name="table_name">
                  </div>
                </div>
              </div>

              <hr>

              {{-- ============== SECTION 1: RECEIVE FROM CURRENT STAGE ============== --}}
              <h5 class="text-primary"><i class="fas fa-download"></i> Receive from Current Stage: {{ $currentStage ? $currentStage->name : 'N/A' }}</h5>

              {{-- Material Receive Section --}}
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Materials</label>
                    <select class="form-control select2" name="smaterial_id" style="width: 100%" id="r_material_id">
                      <option value="" disabled selected>Select Material</option>
                      @php $lastKey = null; @endphp
                      @if($issueItem->count())
                        @foreach($issueItem as $item)
                          @if($item->material_id)
                            @php $currentKey = $item->product_type_id; @endphp
                            @if($lastKey != $currentKey)
                              @php $minAvg = isset($average[$currentKey]['min_avg']) ? $average[$currentKey]['min_avg'] : '0'; @endphp
                              <option disabled>========== {{$item->article_no}} | Size {{$item->sname}} | Avg {{$minAvg}} ==========</option>
                              @php $lastKey = $currentKey; @endphp
                            @endif
                            <option value="{{$item->material_id}}|{{$item->product_type_id}}">
                              {{$item->name}} | Avg {{ $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0' }}
                            </option>
                          @endif
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="receiveable_stock">Receiveable Stock</label>
                    <input type="text" class="form-control" id="receiveable_stock" name="receiveable_stock" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="r_quantityMaterial" placeholder="0" id="r_quantityMaterial">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label><br>
                    <button type="button" id="addBtnReceiveMaterial" class="btn btn-success">Add</button>
                  </div>
                </div>
              </div>

              {{-- Product Receive Section --}}
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Products</label>
                    <select class="form-control select2" name="r_product_type_id" id="r_product_type_id">
                      <option value="" disabled selected>Select Product</option>
                      @if($issueItem->count())
                        @php $issueItemUniqueR = $issueItem->unique('product_type_id'); @endphp
                        @foreach($issueItemUniqueR as $item)
                          @php
                            $currentKey = $item->product_type_id;
                            $minAvg = isset($average[$currentKey]['min_avg']) ? $average[$currentKey]['min_avg'] : '0';
                          @endphp
                          <option value="{{$item->product_type_id}}">
                            {{$item->article_no}} | Size {{$item->sname}} | Avg {{$minAvg}}
                          </option>
                        @endforeach
                      @endif
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Product Stage</label>
                    <select class="form-control select2" name="r_stage_id" id="r_stage_id">
                      <option value="" disabled selected>Select Stage</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Work Log (Cost)</label>
                    <select class="form-control select2" name="r_pcost_id[]" id="r_pcost_id" multiple="">
                      <option value="" disabled>Select Work Log</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="r_quantityProduct" placeholder="0" id="r_quantityProduct">
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label>Add</label><br>
                    <button type="button" id="addBtnReceiveProduct" class="btn btn-success">Add</button>
                  </div>
                </div>
              </div>

              {{-- Receive Items Table with Tabs --}}
              <div class="row">
                <div class="col-md-12">
                  <ul class="nav nav-tabs" id="receiveTab" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active" id="receive-items-tab" data-toggle="tab" href="#receive-items" role="tab">Receive Items</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="issued-items-tab" data-toggle="tab" href="#issued-items" role="tab">Issued Items</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="received-items-tab" data-toggle="tab" href="#received-items" role="tab">Received Items</a>
                    </li>
                  </ul>
                  <div class="tab-content" id="receiveTabContent">
                    {{-- Receive Items Table --}}
                    <div class="tab-pane fade show active" id="receive-items" role="tabpanel">
                      <table class="table" id="receive-table">
                        <thead>
                          <tr>
                            <th>Sr.</th>
                            <th>Item / Product</th>
                            <th>Material / Stage</th>
                            <th>Work/Cost</th>
                            <th>Quantity</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody></tbody>
                      </table>
                    </div>
                    {{-- Issued Items Tab --}}
                    <div class="tab-pane fade" id="issued-items" role="tabpanel">
                      <table class="table table-sm table-striped">
                        <thead>
                          <tr>
                            <th>Sr.</th>
                            <th>Item / Product</th>
                            <th>Material / Stage</th>
                            <th>Quantity</th>
                            <th>Average</th>
                          </tr>
                        </thead>
                        <tbody>
                          @if($issueItem->count())
                            @foreach($issueItem as $item)
                              <tr>
                                <td>{{$loop->index + 1}}</td>
                                <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                                <td>{{($item->name)? $item->name:$item->stage}}</td>
                                <td>{{$item->quantity}} {{($item->uname)? $item->uname:$item->puname}}</td>
                                <td>{{ $item->pqty != 0 ? bcdiv($item->quantity, $item->pqty, 1) : '0' }} Units</td>
                              </tr>
                            @endforeach
                          @endif
                        </tbody>
                      </table>
                    </div>
                    {{-- Received Items Tab --}}
                    <div class="tab-pane fade" id="received-items" role="tabpanel">
                      <table class="table table-sm table-striped">
                        <thead>
                          <tr>
                            <th>Sr.</th>
                            <th>Article No</th>
                            <th>Material / Stage</th>
                            <th>Quantity</th>
                          </tr>
                        </thead>
                        <tbody>
                          @if($issueSum->count())
                            @foreach($issueSum as $item)
                              <tr>
                                <td>{{$loop->index + 1}}</td>
                                <td>{{$item->article_no}} - Size {{$item->sname}}</td>
                                <td>{{($item->name)? $item->name:$item->stage}}</td>
                                <td>{{$item->total_quantity}} {{($item->uname)? $item->uname:$item->puname}}</td>
                              </tr>
                            @endforeach
                          @endif
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              @if($nextStage)
              <hr>

              {{-- ============== SECTION 2: ISSUE TO NEXT STAGE ============== --}}
              <h5 class="text-info"><i class="fas fa-upload"></i> Issue to Next Stage: {{ $nextStage->name }}</h5>

              {{-- Material Issue Section --}}
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Materials (Optional)</label>
                    <select class="form-control select2" name="i_material_id" id="material_id">
                      <option value="" disabled selected>Select Material</option>
                      @foreach($materials as $material)
                        <option value="{{ $material->material_id }}">{{ $material->name }} | {{ $material->unit }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Available Stock</label>
                    <input type="text" class="form-control" id="available_stock" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" step="0.01" class="form-control" name="quantityMaterial" id="quantityMaterial" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label><br>
                    <button type="button" id="addBtnMaterial" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>

              {{-- Product Issue Section --}}
              <div class="row">
                <div class="col-md-5">
                  <div class="form-group">
                    <label>Product Stage (Optional)</label>
                    <select class="form-control select2" name="stage_id" id="stage_id" multiple>
                      <option value="" disabled>Select Stage</option>
                      @foreach($stages as $stage)
                        <option value="{{ $stage->head_id }}">{{ $stage->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Available Stock</label>
                    <input type="text" class="form-control" id="available_pstock" readonly>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" min="0" class="form-control" name="quantityStage" id="quantityStage" placeholder="0">
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label>Add</label><br>
                    <button type="button" id="addBtnStage" class="btn btn-primary">Add</button>
                  </div>
                </div>
              </div>

              {{-- Issue Items Table --}}
              <div class="row">
                <div class="col-md-12">
                  <table class="table" id="items-table">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Item / Product</th>
                        <th>Material / Stage</th>
                        <th>Quantity</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>
              @endif

              <hr>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Notes</label>
                    <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                  </div>
                </div>
              </div>

              <div class="form-group row mb-4">
                <div class="col-md-12 text-right">
                  @if($nextStage)
                    <button class="btn btn-success" type="submit" onclick="return submits()">
                      <i class="fas fa-arrow-right"></i> Move to {{ $nextStage->name }}
                    </button>
                  @else
                    <button class="btn btn-primary" type="submit" onclick="return submits()">
                      <i class="fas fa-check"></i> Complete PTC
                    </button>
                  @endif
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// PTC Move Stage Page Variables
var isPtcMoveStage = true;
var isPtcPage = true;

// Issue section data (for custom.js PTC Script)
var stockData = @json($stock);
var pstockData = @json($pstock);
var ptcProductTypeId = '{{ $product ? $product->product_type_id : 0 }}';
var ptcProductName = '{{ $product ? $product->name . " - " . $product->size_name : "N/A" }}';

// Receive section data
var issueItems = @json($issueItem);
var rstock = {!! $rstock->toJson() !!};
var ajaxPCUrl = "{{ route('ajaxPC') }}";
var ajaxPSUrl = "{{ route('ajaxPS') }}";
</script>
@endsection

