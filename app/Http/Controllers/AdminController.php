<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Requests\RoleRequest;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepository;
use App\Repositories\AdminRepository;

class AdminController extends Controller
{
    protected $userRepository;
    protected $roleRepository;

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
        $user = $this->userRepository->all();
        $role = $this->adminRepository->role();

        return view('user', [
            'user' => $user,
            'roles' => $role,
        ]);
    }

    public function role()
    {
        $role = $this->adminRepository->role();

        return view('role', [
            'role' => $role,
        ]);
    }

    public function create()
    {
        $permission = $this->adminRepository->permission();

        return view('addRole', [
            'permission' => $permission,
        ]);
    }

    public function store(RoleRequest $request)
    {
        $validatedData = $request->validated();
        $getId = $this->adminRepository->store($validatedData);

        return redirect()->route('role.add')->with('success', 'Record Inserted Successfully');
    }


    public function edit(Role $id)
    {
        $permission = $this->adminRepository->permission();

        $rolePermissionIds = DB::table('permission_role')
            ->where('role_id', $id->id)
            ->pluck('permission_id')
            ->toArray();

        return view('editRole', [
            'role' => $id,
            'permission' => $permission,
            'rolePermissionIds' => $rolePermissionIds,
        ]);
    }

    public function update(Request $request, $id)
    {
        $getId = $this->adminRepository->update($id, $request->input());

        return redirect()->route('role.edit', $id)->with('success', 'Record Updated Successfully');
    }
}
