@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Receive Purchase Table</h4>
            <div class="card-header-action">
              <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Add Receive</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Receive No</th>
                    <th>Purchase No</th>
                    <th>Vendor</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($receive->count())
                    @foreach($receive as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->receive_no}}</td>
                      <td>{{$item->purchase_no}}</td>
                      <td>{{$item->fname}}</td>
                      <td>
                        <div class="btn-group">
                          <button class="btn <?php 
                              if($item->receive_status == 0){ echo 'btn-warning'; $status = 'Pending'; }
                              elseif($item->receive_status == 1){ echo 'btn-success'; $status = 'Checked'; }
                              else{ echo 'btn-danger'; $status = 'Unknown'; }
                             ?> btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{$status}}
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('receive.updateStatus', ['id' => $item->receive_id, 'status' => '0']) }}">Pending</a>
                            <a class="dropdown-item" href="{{ route('receive.updateStatus', ['id' => $item->receive_id, 'status' => '1']) }}">Checked</a>
                          </div>
                        </div>
                      </td>
                      <td>{{$item->receive_date}}</td>                      
                      <td>
                        <a href="{{ route('receive.show', $item->receive_id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('receive.edit', $item->receive_id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <a href="{{ route('return.add', $item->receive_id) }}" class="btn btn-success btn-sm">Return</a>
                      </td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Receive No</th>
                    <th>Purchase No</th>
                    <th>Vendor</th>
                    <th>Status</th>
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
        <h5 class="modal-title" id="formModal">Add Receive</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('receive.add', 0) }}" method="POST" class="needs-validation" novalidate="" id="receive"> @csrf
          <div class="card-body">
            <div class="form-group">
              <label>Select Purchase</label>
              <select class="form-control select2" name="purchase_id" id="purchase_id" required style="width: 100%">
                <option value="" selected disabled>Select Purchase</option>
                @if($purchase->count())
                  @foreach($purchase as $item)
                    <option value="{{$item->purchase_id}}">{{$item->purchase_no}} - {{($item->job_no)? $item->job_no:'Default Purchase'}}</option>
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
        var getId = document.getElementById('purchase_id').value;
        document.getElementById('receive').action = "{{ route('receive.add', ':getId') }}".replace(':getId', getId);
        document.getElementById('receive').submit();
    }
</script>
@endsection