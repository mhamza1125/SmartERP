@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Heads Table</h4>
            <div class="card-header-action">
              <div class="btn-group">
                <a href="{{ route('head.add') }}" class="btn btn-primary">Add Head</a>
                <a href="{{ route('head.headType') }}" class="btn btn-primary">Head Types</a>
              </div>
            </div>
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <!-- First Tab for All Heads -->
              <li class="nav-item">
                <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">All Heads</a>
              </li>
              <!-- Create a Tab for Each Head Type -->
              @foreach($headType as $ht)
                <li class="nav-item">
                  <a class="nav-link" id="type-{{$ht->head_type_id}}-tab" data-toggle="tab" href="#type-{{$ht->head_type_id}}" role="tab" aria-controls="type-{{$ht->head_type_id}}" aria-selected="false">{{$ht->name}}</a>
                </li>
              @endforeach
            </ul>

            <div class="tab-content" id="myTabContent">
              <!-- Tab Content for All Heads -->
              <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                <div class="table-responsive mt-3">
                  <table class="table table-striped table-hover" id="save-stage" style="width:100%;">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Head Type</th>
                        <th>Head</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @if($head->count())
                        @foreach($head as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->htname}}</td>
                          <td>{{$item->name}}</td>
                          <td>
                            @if($item->head_status)
                              <span class="badge badge-success">Active</span>
                            @else
                              <span class="badge badge-danger">Inactive</span>
                            @endif
                          </td>
                          <td>
                            <button type="button" {{($item->action == 1)? 'disabled':''}} class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal{{$item->head_id}}">Edit</button>
                          </td>
                        </tr>
                        @endforeach
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Tab Content for Each Head Type -->
              @foreach($headType as $ht)
              <div class="tab-pane fade" id="type-{{$ht->head_type_id}}" role="tabpanel" aria-labelledby="type-{{$ht->head_type_id}}-tab">
                <div class="table-responsive mt-3">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        <th>Sr.</th>
                        <th>Head</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php
                        $filteredHeads = $head->filter(function($item) use ($ht) {
                          return $item->head_type_id == $ht->head_type_id;
                        });
                      @endphp
                      @if($filteredHeads->count())
                        @foreach($filteredHeads as $item)
                        <tr>
                          <td>{{$loop->index + 1}}</td>
                          <td>{{$item->name}}</td>
                          <td>
                            @if($item->head_status)
                              <span class="badge badge-success">Active</span>
                            @else
                              <span class="badge badge-danger">Inactive</span>
                            @endif
                          </td>
                          <td>
                            <button type="button" {{($item->action == 1)? 'disabled':''}} class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal{{$item->head_id}}">Edit</button>
                          </td>
                        </tr>
                        @endforeach
                      @else
                        <tr>
                          <td colspan="4" class="text-center">No heads available for this type</td>
                        </tr>
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Modal for Editing Heads -->
@if($head->count())
  @foreach($head as $item)
    <div class="modal fade" id="exampleModal{{$item->head_id}}" tabindex="-1" role="dialog" aria-labelledby="formModal" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="formModal">Edit Head</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="{{ route('head.update', $item->head_id) }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="card-body">
                <div class="form-group">
                  <label>Head Name</label>
                  <input type="text" class="form-control" name="name" value="{{$item->name}}" required>
                  <div class="valid-feedback">Good job!</div>
                  <div class="invalid-feedback">Enter Head Name</div>
                </div>
                <div class="form-group">
                  <label>Head Type</label>
                  <select class="form-control" name="head_type_id" required="">
                    <option value="" selected disabled>Choose Head Type</option>
                    @foreach($headType as $ht)
                      <option value="{{ $ht->head_type_id }}" {{ $item->head_type_id == $ht->head_type_id ? 'selected' : '' }}>{{ $ht->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group">
                  <label>Head Status</label>
                  <select class="form-control" name="head_status" required="">
                    <option value="" selected disabled>Choose Head Status</option>
                    <option value="1" {{ $item->head_status == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $item->head_status == '0' ? 'selected' : '' }}>Inactive</option>
                  </select>
                </div>
              </div>
              <div class="card-footer text-right">
                <button class="btn btn-primary">Submit</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endif
@endsection
