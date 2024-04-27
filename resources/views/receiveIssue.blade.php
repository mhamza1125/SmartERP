@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Received Issuance Table</h4>
            <div class="card-header-action">
              <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Receive Issuance</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Receive Issuance No</th>
                    <th>Job No</th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($receive->count())
                    @foreach($receive as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->stock_no}}</td>
                      <td>{{($item->job_no)? $item->job_no:'Default issue'}}</td>
                      <td>{{$item->name}}</td>
                      <td>{{$item->stock_date}}</td>                      
                      <td>
                        <a href="{{ route('rstock.show', $item->stock_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('rstock.edit', $item->stock_id) }}" class="btn btn-primary btn-sm">Edit</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Issue No</th>
                    <th>Job No</th>
                    <th>Employee</th>
                    <th>Date</th>
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
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="formModal"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formModal">Add Receive Issuance</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('rstock.add', 0) }}" method="POST" class="needs-validation" novalidate="" id="rstock"> @csrf
          <div class="card-body">
            <div class="form-group">
              <label>Select Issuance</label>
              <select class="form-control select2" name="stock_id" id="stock_id" required style="width: 100%">
                <option value="" selected disabled>Select Issuance</option>
                @if($issue->count())
                  @foreach($issue as $item)
                    <option value="{{$item->stock_id}}">{{$item->stock_no}} - {{($item->job_no)? $item->job_no:'Default issue'}} - {{$item->name}}</option>
                  @endforeach
                @endif
              </select>
            </div>
            <div class="form-group text-right">
              <button class="btn btn-primary" onclick="updateFormAction()">Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  function updateFormAction() {
        var stockId = document.getElementById('stock_id').value;
        document.getElementById('rstock').action = "{{ route('rstock.add', ':stockId') }}".replace(':stockId', stockId);
        document.getElementById('rstock').submit();
    }
</script>
@endsection