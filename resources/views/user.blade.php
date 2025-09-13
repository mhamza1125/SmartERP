@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Users Table</h4>
            <div class="card-header-action">
              <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModalAdd">Add User</button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($user->count())
                    @foreach($user as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->name}}</td>
                      <td>{{$item->email}}</td>
                      <td>{{$item->role->name}}</td>
                      <td><button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal{{$item->id}}">Edit</button></td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Username</th>
                    <th>Email</th>
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
<div class="modal fade" id="exampleModalAdd" tabindex="-1" role="dialog" aria-labelledby="formModal"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formModal">Add User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('user.store') }}" method="POST" class="needs-validation" novalidate=""> @csrf
          <div class="card-body">
            <div class="row">
              <div class="form-group col-md-6">
                <label>Username</label>
                <input type="text" class="form-control" name="name" placeholder="Username" required>
                <div class="valid-feedback">Good job!</div>
                <div class="invalid-feedback">Enter Username</div>
              </div>
              <div class="form-group col-md-6">
                <label>Email</label>
                <input type="text" class="form-control" name="email" placeholder="info@example.com" required>
                <div class="valid-feedback">Good job!</div>
                <div class="invalid-feedback">Enter Email</div>
              </div>
              <div class="form-group col-md-6">
                <label>Password</label>
                <input type="password" class="form-control" name="password" required>
                <div class="valid-feedback">Good job!</div>
                <div class="invalid-feedback">Enter Password</div>
              </div>
              <div class="form-group col-md-6">
                <label>Repeat Password</label>
                <input type="password" class="form-control" name="password_confirmation" required>
                <div class="valid-feedback">Good job!</div>
                <div class="invalid-feedback">Repeat Valid Password</div>
              </div>
              <div class="form-group col-md-12">
                <label>User Roles</label>
                <select class="form-control" name="role_id" required>
                  <option value="" selected>Select User Role</option>
                  @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ $role->id == $item->role_id ? 'selected' : '' }}>{{ $role->name }}</option>
                  @endforeach
                </select>
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
@if($user->count())
  @foreach($user as $item)
    <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="formModal"
      aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="formModal">Edit User</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="{{ route('user.update', $item->id) }}" method="POST" class="needs-validation" novalidate=""> @csrf
              <div class="card-body">
                <div class="row">
                  <div class="form-group col-md-6">
                    <label>Username</label>
                    <input type="text" class="form-control" name="name" value="{{$item->name}}" required>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Username</div>
                  </div>
                  <div class="form-group col-md-6">
                    <label>Email</label>
                    <input type="text" class="form-control" name="email" value="{{$item->email}}" required>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Email</div>
                  </div>
                  <div class="form-group col-md-6">
                    <label>Password</label>
                    <input type="password" class="form-control" name="password" placeholder="Leave empty to keep same">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Password</div>
                  </div>
                  <div class="form-group col-md-6">
                    <label>Repeat Password</label>
                    <input type="password" class="form-control" name="password_confirmation" placeholder="Leave empty to keep same">
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Repeat Valid Password</div>
                  </div>
                  <div class="form-group col-md-12">
                    <label>User Roles</label>
                    {{-- @if($item->role_id == '1') --}}
                      <select class="form-control" name="role_id" required>
                        <option value="" selected>Select User Role</option>
                        @foreach($roles as $role)
                          <option value="{{ $role->id }}" {{ $role->id == $item->role_id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                      </select>
                    {{-- @else
                      <input type="text" class="form-control" name="role" value="{{$item->role->name}}" required readonly>
                    @endif --}}
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