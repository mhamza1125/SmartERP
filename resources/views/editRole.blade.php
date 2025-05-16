@extends('index')
@section('content')
<section class="section">
  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Edit Role</h4>
            <div class="card-header-action">
              <a href="{{ url()->previous() }}" class="btn btn-primary">
                Back
              </a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('role.update', $role->id) }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Role</label>
                    <input type="text" class="form-control" name="name" value="{{ $role->name }}" required>
                    <div class="valid-feedback">Good job!</div>
                    <div class="invalid-feedback">Enter Role Name</div>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Permissions</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="checkAllBtn">
                  Check All
                </button>
              </div>
              <div class="permissions-grid">
                @if($permission->count())
                  @foreach($permission as $item)
                    <div class="permission-item">
                      <div class="pretty p-default p-curve">
                        <input type="checkbox" name="permission_id[]" value="{{ $item->id }}"
                          {{ in_array($item->id, $rolePermissionIds) ? 'checked' : '' }}>
                        <div class="state p-primary">
                          <label>{{ $item->name }}</label>
                        </div>
                      </div>
                    </div>
                  @endforeach
                @endif
              </div>
              <div class="form-group row mb-4">
                <div class="col-md-12 text-right">
                  <button class="btn btn-primary" type="submit" onclick="return submits()">Submit</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  document.getElementById('checkAllBtn').addEventListener('click', function () {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);

    checkboxes.forEach(cb => cb.checked = !allChecked);

    this.innerText = allChecked ? 'Check All' : 'Uncheck All';
  });
</script>
@endsection
