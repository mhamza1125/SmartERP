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
              @can('roles_create', App\Models\User::class)<a href="{{ route('role.add') }}" class="btn btn-primary">Add Role</a>@endcan
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="tableExport" style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr.</th>
                    <th>Role</th>
                    <th>Users</th>
                    <th>Permissions</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @if($role->count())
                    @foreach($role as $item)
                    <tr>
                      <td>{{$loop->index + 1}}</td>
                      <td>{{$item->name}}</td>
                      <td>{{ $summaries[$item->id]['users'] ?? 0 }}</td>
                      <td style="max-width: 560px;">
                        @if($item->id == App\Models\User::ROLE_ADMIN)
                          <span class="badge badge-success">All permissions (system role)</span>
                        @elseif(empty($summaries[$item->id]['modules']))
                          <span class="text-muted">None</span>
                        @else
                          <div class="small" style="line-height:1.35;">
                            @foreach($summaries[$item->id]['modules'] as $line)
                              <div>{{ $line }}</div>
                            @endforeach
                          </div>
                        @endif
                      </td>
                      <td>@can('roles_edit', App\Models\User::class)<a href="{{ route('role.edit', $item->id) }}" class="btn btn-primary btn-sm">Edit</a>@endcan
@if($item->id != App\Models\User::ROLE_ADMIN)@can('roles_delete', App\Models\User::class)@include('deleteButton', ['url' => route('role.destroy', $item->id), 'check' => route('role.deleteCheck', $item->id), 'label' => 'Role '.$item->name, 'class' => 'btn btn-danger btn-sm'])@endcan @endif</td>
                    </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr>
                    <th>Sr.</th>
                    <th>Role</th>
                    <th>Users</th>
                    <th>Permissions</th>
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