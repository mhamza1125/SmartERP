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
              <a href="{{ route('head.add') }}" class="btn btn-primary">Add Head</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
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
                      <td>{{ $loop->index + 1 }}</td>
                      <td>{{$item->htname}}</td>
                      <td>{{$item->name}}</td>                      
                      <td>
                        @if($item->head_status)
                          <span class="badge badge-success">Active</span>
                        @else
                          <span class="badge badge-danger">Inactive</span>
                        @endif
                      </td>
                      <td><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal{{$item->head_id}}">Edit</button></td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Head Type</th>
                    <th>Head</th>
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
@if($head->count())
  @foreach($head as $item)
    <div class="modal fade" id="exampleModal{{$item->head_id}}" tabindex="-1" role="dialog" aria-labelledby="formModal"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="formModal">Edit Head</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="{{ route('head.update', $item->head_id) }}" method="POST" class="needs-validation" novalidate=""> @csrf
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