{{--
  Permission picker of Add Role / Edit Role.
  Params: $groupedPermissions (AdminRepository::groupedPermissions()), $rolePermissionIds (checked ids),
          $locked (true for the system Admin role: shown, not editable).
  Saving adds the permissions a selection depends on (PermissionCatalog::normalize); the script below
  ticks them right away so what is shown is what will be saved.
--}}
@php
  $locked = $locked ?? false;
  $rolePermissionIds = $rolePermissionIds ?? [];
  $actions = \App\Support\PermissionCatalog::ACTIONS;
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
  <h5 class="mb-0 font-weight-bold">Permissions</h5>
  @unless($locked)
  <div class="custom-control custom-switch">
    <input type="checkbox" class="custom-control-input" id="globalToggle">
    <label class="custom-control-label font-weight-bold" for="globalToggle">Select All Permissions</label>
  </div>
  @endunless
</div>

@if($locked)
  <div class="alert alert-info">
    This is the system <b>Admin</b> role: it always has every permission, including ones added in future.
    Create another role to give a limited set of permissions.
  </div>
@else
  <div class="alert alert-light border small mb-3">
    @foreach($actions as $action => [$label, $help])
      <span class="mr-3"><b>{{ $label }}</b>: {{ $help }}.</span>
    @endforeach
    <div class="mt-1 text-muted">Any permission of a module also gives its <b>Access</b>, and <b>Edit</b> also gives <b>View</b>; they are ticked automatically.</div>
  </div>
@endif

@foreach($groupedPermissions as $section => $modules)
  <h6 class="text-uppercase text-muted font-weight-bold mt-3 mb-2" style="font-size:0.75rem; letter-spacing:.05em;">{{ $section }}</h6>
  <div class="row">
    @foreach($modules as $module => $info)
    <div class="col-md-6 col-xl-4 mb-3">
      <div class="card border shadow-sm h-100 mb-0">
        <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background:#f8f9fa;">
          <span class="font-weight-bold" style="font-size:0.9rem;">{{ $info['label'] }}</span>
          @unless($locked)
          <div class="custom-control custom-switch mb-0" title="All {{ $info['label'] }} permissions">
            <input type="checkbox" class="custom-control-input module-toggle" id="mod_{{ $module }}" data-module="{{ $module }}">
            <label class="custom-control-label" for="mod_{{ $module }}"></label>
          </div>
          @endunless
        </div>
        <div class="card-body py-2 px-3">
          @if($info['description'])
            <div class="text-muted mb-1" style="font-size:0.75rem; line-height:1.3;">{{ $info['description'] }}</div>
          @endif
          <div class="d-flex flex-wrap">
          @foreach($info['permissions'] as $action => $perm)
            <div class="custom-control custom-checkbox py-1 mr-3" title="{{ $actions[$action][1] ?? $perm->name }}">
              <input type="checkbox"
                     class="custom-control-input perm-checkbox"
                     id="perm_{{ $perm->id }}"
                     name="permission_id[]"
                     value="{{ $perm->id }}"
                     data-module="{{ $module }}"
                     data-action="{{ $action }}"
                     {{ $locked || in_array($perm->id, $rolePermissionIds) ? 'checked' : '' }}
                     {{ $locked ? 'disabled' : '' }}>
              <label class="custom-control-label" for="perm_{{ $perm->id }}" style="font-size:0.85rem;">
                {{ $actions[$action][0] ?? ucfirst($action) }}
              </label>
            </div>
          @endforeach
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
@endforeach

@unless($locked)
@push('scripts')
<script>
(function () {
  const globalToggle = document.getElementById('globalToggle');
  const moduleToggles = document.querySelectorAll('.module-toggle');
  const allPerms = document.querySelectorAll('.perm-checkbox');
  const modulePerms = m => document.querySelectorAll('.perm-checkbox[data-module="' + m + '"]');
  const permOf = (m, a) => document.querySelector('.perm-checkbox[data-module="' + m + '"][data-action="' + a + '"]');

  // Same rule as PermissionCatalog::normalize(): any action => Access, Edit => View;
  // removing Access removes the rest of the module (they would bring it back on save).
  function applyDependencies(cb) {
    const m = cb.dataset.module, a = cb.dataset.action;
    if (cb.checked) {
      if (a !== 'access' && permOf(m, 'access')) permOf(m, 'access').checked = true;
      if (a === 'edit' && permOf(m, 'show')) permOf(m, 'show').checked = true;
    } else if (a === 'access') {
      modulePerms(m).forEach(x => x.checked = false);
    } else if (a === 'show' && permOf(m, 'edit')) {
      permOf(m, 'edit').checked = false;
    }
  }

  function syncModuleToggle(m) {
    const perms = Array.from(modulePerms(m));
    const toggle = document.querySelector('.module-toggle[data-module="' + m + '"]');
    if (!toggle) return;
    const n = perms.filter(cb => cb.checked).length;
    toggle.checked = n === perms.length;
    toggle.indeterminate = n > 0 && n < perms.length;
  }

  function syncGlobalToggle() {
    const n = Array.from(allPerms).filter(cb => cb.checked).length;
    globalToggle.checked = n === allPerms.length;
    globalToggle.indeterminate = n > 0 && n < allPerms.length;
  }

  allPerms.forEach(cb => { if (cb.checked) applyDependencies(cb); });
  moduleToggles.forEach(t => syncModuleToggle(t.dataset.module));
  syncGlobalToggle();

  globalToggle.addEventListener('change', function () {
    allPerms.forEach(cb => cb.checked = this.checked);
    moduleToggles.forEach(t => { t.checked = this.checked; t.indeterminate = false; });
  });
  moduleToggles.forEach(t => t.addEventListener('change', function () {
    modulePerms(this.dataset.module).forEach(cb => cb.checked = this.checked);
    syncGlobalToggle();
  }));
  allPerms.forEach(cb => cb.addEventListener('change', function () {
    applyDependencies(this);
    syncModuleToggle(this.dataset.module);
    syncGlobalToggle();
  }));
})();
</script>
@endpush
@endunless
