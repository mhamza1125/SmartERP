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
              {{-- <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Receive Issuance</a> --}}
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('rstock.add', 0) }}" method="POST" class="needs-validation" novalidate="" id="rstock">@csrf
              <div class="row">
                <div class="form-group col-md-3">
                  <label>Issuance For</label>
                  <select class="form-control select2" name="head_id" id="head_id" required style="width: 100%">
                    <option value="0" selected>All</option>
                    @foreach($head as $item)
                      <option value="{{$item->head_id}}">{{$item->name}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-3">
                  <label>Select Job Number</label>
                  <select class="form-control select2" name="job_no" id="job_no" required style="width: 100%">
                    <option value="0">All</option>
                    @foreach($jobs as $job)
                      <option value="{{$job}}">{{$job}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-4">
                  <label>Select Issuance</label>
                  <select class="form-control select2" name="stock_id" id="stock_id" required style="width: 100%">
                    <option value="" selected disabled>Select Issuance</option>
                    @foreach($issue as $item)
                      <option value="{{$item->stock_id}}" data-type="{{$item->issue_for}}" data-job="{{$item->job_no}}">{{$item->stock_no}} - {{$item->name}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-2 mt-4">
                  <button type="button" class="btn btn-primary mt-2" onclick="updateFormAction()">Receive Issuance</button>
                </div>
              </div>
            </form>
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Date</th>
                    <th>Receive Issuance No</th>
                    <th>Job No</th>
                    <th>Issued For</th>
                    <th>Employee</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($receive->count())
                    @foreach($receive as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->stock_date}}</td>
                      <td>{{$item->stock_no}}</td>
                      <td>{{($item->job_no)? $item->job_no:'Default issue'}}</td>
                      <td>{{$item->sname}}</td>
                      <td>{{ $item->table_name === 'employee' ? $item->employee_no . ' - ' . $item->name : $item->vendor_no . ' - ' . $item->fname }}</td>
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
                    <th>Date</th>
                    <th>Issue No</th>
                    <th>Job No</th>
                    <th>Issued For</th>
                    <th>Employee</th>
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
<script>
  $(document).ready(function() {
    function filterIssuances() {
      var selectedHeadId = $('#head_id').val();
      var selectedJobNo = $('#job_no').val();
      $('#stock_id option').detach(); // Detach all options

      // Append options based on selectedHeadId and selectedJobNo
      $('#stock_id').append('<option value="" selected disabled>Select Issuance</option>');
      @foreach($issue as $item)
        if ((selectedHeadId == "0" || "{{$item->issue_for}}" == selectedHeadId) &&
            (selectedJobNo == "0" || "{{$item->job_no}}" == selectedJobNo)) {
          $('#stock_id').append('<option value="{{$item->stock_id}}" data-type="{{$item->issue_for}}" data-job="{{$item->job_no}}">{{$item->stock_no}} - {{($item->job_no) ? $item->job_no : 'Default Issue'}} - {{$item->name}}</option>');
        }
      @endforeach
    }

    $('#head_id').change(filterIssuances);
    $('#job_no').change(filterIssuances);
  });

  function updateFormAction() {
    var getId = document.getElementById('stock_id').value;
    document.getElementById('rstock').action = "{{ route('rstock.add', ':getId') }}".replace(':getId', getId);
    document.getElementById('rstock').submit();
  }
</script>
@endsection
