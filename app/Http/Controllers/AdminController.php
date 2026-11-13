<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\RoleRequest;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepository;
use App\Repositories\AdminRepository;

class AdminController extends Controller
{
    protected $userRepository;
    protected $adminRepository;

    public function __construct(
        UserRepository $userRepository,
        AdminRepository $adminRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->userRepository = $userRepository;
        $this->adminRepository = $adminRepository;
    }

    public function index()
    {
        return view('dashboard');
    }

    public function user()
    {
        $this->authorize('users_access', User::class);
        $user = $this->userRepository->all();
        $role = $this->adminRepository->role();

        return view('user', [
            'user' => $user,
            'roles' => $role,
        ]);
    }

    public function role()
    {
        $this->authorize('roles_access', User::class);
        $role = $this->adminRepository->role();

        return view('role', [
            'summaries' => $this->adminRepository->roleSummaries(),
            'role' => $role,
        ]);
    }

    public function create()
    {
        $this->authorize('roles_create', User::class);
        $groupedPermissions = $this->adminRepository->groupedPermissions();

        return view('addRole', [
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    public function store(RoleRequest $request)
    {
        $this->authorize('roles_create', User::class);
        $validatedData = $request->validated();
        $this->adminRepository->store($validatedData);

        return redirect()->route('role.add')->with('success', 'Record Inserted Successfully');
    }

    public function edit(Role $id)
    {
        $this->authorize('roles_edit', User::class);
        $groupedPermissions = $this->adminRepository->groupedPermissions();

        $rolePermissionIds = DB::table('permission_role')
            ->where('role_id', $id->id)
            ->pluck('permission_id')
            ->toArray();

        return view('editRole', [
            'isSystemAdminRole' => $this->adminRepository->isSystemAdminRole($id->id),
            'role' => $id,
            'groupedPermissions' => $groupedPermissions,
            'rolePermissionIds' => $rolePermissionIds,
        ]);
    }

    public function update(RoleRequest $request, $id)
    {
        $this->authorize('roles_edit', User::class);
        $this->adminRepository->update($id, $request->validated());

        return redirect()->route('role.edit', $id)->with('success', 'Record Updated Successfully');
    }

    /**
     * What deleting this user would affect (JSON for the delete button).
     */
    public function userDeleteCheck($id)
    {
        $this->authorize('users_delete', User::class);

        return $this->deletionCheckResponse($this->userRepository->deletionImpact($id));
    }

    public function userDestroy($id)
    {
        $this->authorize('users_delete', User::class);

        return $this->runDeletion(
            $this->userRepository->deletionImpact($id),
            fn () => $this->userRepository->delete($id),
            route('user')
        );
    }

    /**
     * What deleting this role would affect (JSON for the delete button).
     */
    public function roleDeleteCheck($id)
    {
        $this->authorize('roles_delete', User::class);

        return $this->deletionCheckResponse($this->adminRepository->deletionImpact($id));
    }

    public function roleDestroy($id)
    {
        $this->authorize('roles_delete', User::class);

        return $this->runDeletion(
            $this->adminRepository->deletionImpact($id),
            fn () => $this->adminRepository->delete($id),
            route('role')
        );
    }
}
