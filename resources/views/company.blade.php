@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Company Table</h4>
            <div class="card-header-action">
              @if($company->isEmpty())
              <a href="{{ route('company.add') }}" class="btn btn-primary">Add Company</a>
              @endif
            </div>
          </div>
          <div class="card-body">
            @if($company->isEmpty())
              <div class="alert alert-info">
                <h5>No Company Information Found</h5>
                <p>Please add your company information to enable dynamic content throughout the system.</p>
              </div>
            @else
              <div class="table-responsive">
                <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                  <thead>
                    <tr>
                      <th>Sr.</th>
                      <th>Company Name</th>
                      <th>CEO</th>
                      <th>Phone</th>
                      <th>Email</th>
                      <th>Website</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @if($company->count())
                      @foreach($company as $item)
                      <tr>
                        <td>{{$loop->index + 1}}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->ceo }}</td>
                        <td>{{ $item->phone }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->website }}</td>
                        <td>
                          <a href="{{ route('company.show', $item->id) }}" class="btn btn-info btn-sm">View</a>
                          <a href="{{ route('company.edit', $item->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                      </tr>
                      @endforeach
                    @endif
                  </tbody>
                  <tfoot>
                    <tr>
                      <th>Sr.</th>
                      <th>Company Name</th>
                      <th>CEO</th>
                      <th>Phone</th>
                      <th>Email</th>
                      <th>Website</th>
                      <th>Action</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
