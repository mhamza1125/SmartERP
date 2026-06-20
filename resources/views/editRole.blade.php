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
              <a href="{{ url()->previous() }}" class="btn btn-primary">Back</a>
            </div>
          </div>
          <div class="card-body">
            <form action="{{ route('role.update', $role->id) }}" method="POST" class="needs-validation" novalidate="">
              @csrf
              <div class="row mb-3">
                <div class="col-md-6">
                  <div class="form-group mb-0">
                    <label class="font-weight-bold">Role Name</label>
                    <input type="text" class="form-control" name="name" value="{{ $role->name }}" required>
                    <div class="invalid-feedback">Role name is required.</div>
                  </div>
                </div>
              </div>

              <hr>

              {{-- Global Select All --}}
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 font-weight-bold">Permissions</h5>
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" id="globalToggle">
                  <label class="custom-control-label font-weight-bold" for="globalToggle">Select All Permissions</label>
                </div>
              </div>

              {{-- Module Permission Cards --}}
              <div class="row">
                @foreach($groupedPermissions as $module => $permissions)
                <div class="col-md-6 col-xl-4 mb-3">
                  <div class="card border shadow-sm h-100 mb-0">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center"
                         style="background:#f8f9fa;">
                      <span class="font-weight-bold text-capitalize" style="font-size:0.9rem;">
                        {{ ucwords(str_replace('_', ' ', $module)) }}
                      </span>
                      <div class="custom-control custom-switch mb-0">
                        <input type="checkbox" class="custom-control-input module-toggle"
                               id="mod_{{ $loop->index }}"
                               data-module="{{ $module }}">
                        <label class="custom-control-label" for="mod_{{ $loop->index }}"></label>
                      </div>
                    </div>
                    <div class="card-body py-2 px-3">
                      @foreach($permissions as $perm)
                      <div class="custom-control custom-checkbox py-1">
                        <input type="checkbox"
                               class="custom-control-input perm-checkbox"
                               id="perm_{{ $perm->id }}"
                               name="permission_id[]"
                               value="{{ $perm->id }}"
                               data-module="{{ $module }}"
                               {{ in_array($perm->id, $rolePermissionIds) ? 'checked' : '' }}>
                        <label class="custom-control-label" for="perm_{{ $perm->id }}" style="font-size:0.85rem;">
                          {{ ucfirst(substr($perm->name, strrpos($perm->name, '_') + 1)) }}
                        </label>
                      </div>
                      @endforeach
                    </div>
                  </div>
                </div>
                @endforeach
              </div>

              <div class="form-group row mt-4 mb-2">
                <div class="col-md-12 text-right">
                  <button class="btn btn-primary px-5" type="submit">Update Role</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
(function () {
  const globalToggle = document.getElementById('globalToggle');
  const moduleToggles = document.querySelectorAll('.module-toggle');
  const allPerms = document.querySelectorAll('.perm-checkbox');

  // Init module toggles based on pre-checked state
  moduleToggles.forEach(function (toggle) {
    syncModuleToggle(toggle.dataset.module);
  });
  syncGlobalToggle();

  // Global toggle → set all
  globalToggle.addEventListener('change', function () {
    const checked = this.checked;
    allPerms.forEach(cb => cb.checked = checked);
    moduleToggles.forEach(mt => {
      mt.checked = checked;
      mt.indeterminate = false;
    });
  });

  // Module toggle → set its perms
  moduleToggles.forEach(function (toggle) {
    toggle.addEventListener('change', function () {
      const module = this.dataset.module;
      getModulePerms(module).forEach(cb => cb.checked = this.checked);
      syncGlobalToggle();
    });
  });

  // Individual perm change → sync module + global
  allPerms.forEach(function (perm) {
    perm.addEventListener('change', function () {
      syncModuleToggle(this.dataset.module);
      syncGlobalToggle();
    });
  });

  function getModulePerms(module) {
    return document.querySelectorAll('.perm-checkbox[data-module="' + module + '"]');
  }

  function syncModuleToggle(module) {
    const perms = Array.from(getModulePerms(module));
    const toggle = document.querySelector('.module-toggle[data-module="' + module + '"]');
    if (!toggle) return;
    const checkedCount = perms.filter(cb => cb.checked).length;
    toggle.checked = checkedCount === perms.length;
    toggle.indeterminate = checkedCount > 0 && checkedCount < perms.length;
  }

  function syncGlobalToggle() {
    const total = allPerms.length;
    const checked = Array.from(allPerms).filter(cb => cb.checked).length;
    globalToggle.checked = checked === total;
    globalToggle.indeterminate = checked > 0 && checked < total;
  }
})();
</script>
@endpush
@endsection
