@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Roles Table</h4>
            <div class="card-header-action">
              <a href="{{ route('role.add') }}" class="btn btn-primary">Add Role</a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Role</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($role->count())
                    @foreach($role as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->name}}</td>
                      <td><a href="{{ route('role.edit', $item->id) }}" class="btn btn-primary btn-sm">Edit</a></td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Role</th>
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
@endsection