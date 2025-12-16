@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4><i class="fas fa-edit text-warning"></i> Edit PTC Issuance</h4>
            <div class="card-header-action">
              <a href="{{ route('ptc.show', $ptc->stock_id) }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('ptc.issue.update', [$ptc->stock_id, $issuance->stock_id]) }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              @method('PUT')

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
                  <strong>Issuance No:</strong> I{{ str_pad($issuance->stock_no, 3, '0', STR_PAD_LEFT) }}
                </div>
              </div>

              <hr>

              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Issuance Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="stock_date" required value="{{ $issuance->stock_date }}">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>Issue For Stage <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="issue_for" id="issue_for" required>
                      @foreach($stages as $stage)
                        <option value="{{ $stage->head_id }}" {{ $issuance->issue_for == $stage->head_id ? 'selected' : '' }}>{{ $stage->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Issue To (Employee / Contractor) <span class="text-danger">*</span></label>
                    <select class="form-control select2" name="employee_id" id="employee_id" required>
                      <option value="" disabled>Select Employee / Contractor</option>
                      @if($employees->count())
                        <optgroup label="Employees">
                        @foreach($employees as $item)
                          <option data-type="employee" value="{{ $item->employee_id }}" {{ $issuance->table_name == 'employee' && $issuance->employee_id == $item->employee_id ? 'selected' : '' }}>{{ $item->employee_no }} - {{ $item->name }}</option>
                        @endforeach
                        </optgroup>
                      @endif
                      @if($vendors->count())
                        <optgroup label="Contractors">
                        @foreach($vendors as $item)
                          <option data-type="vendor" value="{{ $item->vendor_id }}" {{ $issuance->table_name == 'vendor' && $issuance->employee_id == $item->vendor_id ? 'selected' : '' }}>{{ $item->vendor_no }} - {{ $item->fname }}</option>
                        @endforeach
                        </optgroup>
                      @endif
                    </select>
                    <input type="hidden" id="table_name" name="table_name" value="{{ $issuance->table_name }}">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" name="description" rows="2">{{ $issuance->description }}</textarea>
                  </div>
                </div>
              </div>

              <hr>

              <h5 class="text-primary"><i class="fas fa-boxes"></i> Issued Items</h5>
              <div class="table-responsive">
                <table class="table table-striped table-bordered">
                  <thead class="thead-light">
                    <tr>
                      <th>Sr.</th>
                      <th>Item</th>
                      <th>Stage</th>
                      <th>Quantity</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($issuanceItems as $item)
                      <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                          @if($item->component_product_type_id)
                            <span class="badge badge-info">Component:</span> {{ $item->component_article_no ?? '' }} - {{ $item->component_name ?? '' }}
                          @elseif($item->material_id > 0)
                            {{ $item->mname ?? 'Material #'.$item->material_id }}
                          @else
                            {{ $item->name ?? 'Product' }} - {{ $item->sname ?? 'Size' }}
                          @endif
                        </td>
                        <td>{{ $item->stname ?? 'N/A' }}</td>
                        <td>
                          <input type="hidden" name="stock_item_id[]" value="{{ $item->stock_item_id }}">
                          <input type="number" min="0" step="0.01" class="form-control" name="quantity[]" value="{{ $item->quantity }}" style="width:120px">
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              <hr>
              <div class="text-right">
                <a href="{{ route('ptc.show', $ptc->stock_id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update Issuance</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
document.getElementById('employee_id').addEventListener('change', function() {
  var selectedOption = this.options[this.selectedIndex];
  var type = selectedOption.getAttribute('data-type');
  document.getElementById('table_name').value = type || 'employee';
});
</script>
@endsection

