@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Return Table</h4>
            <div class="card-header-action">
              <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Add Return</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Return No</th>
                    <th>Receive No</th>
                    <th>Purchase No</th>
                    <th>Vendor</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($return->count())
                    @foreach($return as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->return_no}}</td>
                      <td>{{$item->receive_no}}</td>
                      <td>{{$item->purchase_no}}</td>
                      <td>{{$item->fname}}</td>
                      <td>{{$item->return_date}}</td>                      
                      <td>
                        <a href="{{ route('return.show', $item->return_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('return.edit', $item->return_id) }}" class="btn btn-primary btn-sm">Edit</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Return No</th>
                    <th>Receive No</th>
                    <th>Purchase No</th>
                    <th>Vendor</th>
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
        <h5 class="modal-title" id="formModal">Add Return</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('return.add', 0) }}" method="POST" class="needs-validation" novalidate="" id="return"> @csrf
          <div class="card-body">
            <div class="form-group">
              <label>Select Receiving</label>
              <select class="form-control select2" name="receive_id" id="receive_id" required style="width: 100%">
                <option value="" selected disabled>Select Receiving</option>
                @if($receive->count())
                  @foreach($receive as $item)
                    <option value="{{$item->receive_id}}">{{$item->receive_no}} - {{($item->job_no)? $item->job_no:'Default Purchase'}}</option>
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
        var getId = document.getElementById('receive_id').value;
        document.getElementById('return').action = "{{ route('return.add', ':getId') }}".replace(':getId', getId);
        document.getElementById('return').submit();
    }
</script>
@endsection