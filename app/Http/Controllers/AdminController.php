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
            'role' => $id,
            'groupedPermissions' => $groupedPermissions,
            'rolePermissionIds' => $rolePermissionIds,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('roles_edit', User::class);
        $this->adminRepository->update($id, $request->input());

        return redirect()->route('role.edit', $id)->with('success', 'Record Updated Successfully');
    }
}
