@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Work Holiday Table</h4>
            <div class="card-header-action">
              <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModalAdd">Add Holidays</button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Detail</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($work->count())
                    @foreach($work as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->date_from}}</td>
                      <td>{{$item->date_to}}</td>
                      <td style="max-width:200px">{{$item->description}}</td>
                      <td><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal{{$item->work_holiday_id}}">Edit</button></td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Detail</th>
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
<div class="modal fade" id="exampleModalAdd" tabindex="-1" role="dialog" aria-labelledby="formModal"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formModal">Add Holidays</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('work.store') }}" method="POST" class="needs-validation" novalidate=""> @csrf
          <div class="card-body">
            <div class="row">
              <div class="form-group col-md-6">
                <label>Date From</label>
                <input type="date" class="form-control" name="date_from" required>
                <div class="valid-feedback">Good job!</div>
                <div class="invalid-feedback">Enter Date From</div>
              </div>
              <div class="form-group col-md-6">
                <label>Date To</label>
                <input type="date" class="form-control" name="date_to" required>
                <div class="valid-feedback">Good job!</div>
                <div class="invalid-feedback">Enter Date To</div>
              </div>

              <div class="form-group col-md-12">
                <label>Detail</label>
                <textarea class="form-control" name="description"></textarea>
                <div class="valid-feedback">Good job!</div>
              </div>
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
@if($work->count())
  @foreach($work as $item)
    <div class="modal fade" id="exampleModal{{$item->work_holiday_id}}" tabindex="-1" role="dialog" aria-labelledby="formModal"
      aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="formModal">Edit Holidays</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="{{ route('work.update', $item->work_holiday_id) }}" method="POST" class="needs-validation" novalidate=""> @csrf
              <div class="card-body">
                <div class="row">
                  <div class="form-group col-md-6">
                    <label>Date From</label>
                    <input type="date" class="form-control" name="date_from" value="{{$item->date_from}}" required>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Date From</div>
                  </div>
                  <div class="form-group col-md-6">
                    <label>Date To</label>
                    <input type="date" class="form-control" name="date_to" value="{{$item->date_to}}" required>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Date To</div>
                  </div>

                  <div class="form-group col-md-12">
                    <label>Detail</label>
                    <textarea class="form-control" name="description">{{$item->description}}</textarea>
                    <div class="valid-feedback">Good job!</div>
                  </div>
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