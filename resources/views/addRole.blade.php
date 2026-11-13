@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Add Role</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('role.store') }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row mb-3">
                <div class="col-md-6">
                  <div class="form-group mb-0">
                    <label class="font-weight-bold">Role Name</label>
                    <input type="text" class="form-control" name="name" placeholder="Enter role name" required>
                    <div class="invalid-feedback">Role name is required.</div>
                  </div>
                </div>
              </div>

              <hr>

              @include('rolePermissions', ['groupedPermissions' => $groupedPermissions])

              <div class="form-group row mt-4 mb-2">
                <div class="col-md-12 text-right">
                  <button class="btn btn-primary px-5" type="submit">Save Role</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
