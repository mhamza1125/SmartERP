<?php

namespace App\Repositories;

use App\Support\DeletionImpact;
use App\Repositories\Concerns\ChecksReferences;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserRepository implements GlobalInterface
{
    use ChecksReferences;

    public function all()
    {
        return User::with('role')->get();
    }

    public function get($id)
    {
        return User::findOrfail($id);
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = User::create($data);
    }

    public function update($id, array $data)
    {
        $update = User::findOrFail($id);
        $update->update($data);
    }

    /**
     * Tables whose created_by is a foreign key to users with ON DELETE CASCADE: deleting a user
     * who created rows there would silently delete those documents too, so it is refused.
     */
    private const CASCADING_CREATED_BY = [
        'delivery_returns' => 'delivery return',
        'delivery_return_items' => 'delivery return line',
        'packing_lists' => 'packing list',
        'packing_cartons' => 'packing list carton',
        'packing_carton_items' => 'packing list carton line',
    ];

    /**
     * A user cannot delete their own account, the last administrator, or a user who created
     * delivery returns / packing lists (the database would delete those with the user).
     * Other records keep the user's id as their creator.
     */
    public function deletionImpact($id): DeletionImpact
    {
        $user = User::findOrFail($id);
        $impact = DeletionImpact::for('User '.$user->name.' ('.$user->email.')');

        if ((int) $user->id === (int) auth()->id()) {
            $impact->block('you cannot delete your own account');
        }
        if ($user->isAdmin() && User::where('role_id', User::ROLE_ADMIN)->count() <= 1) {
            $impact->block('it is the only administrator');
        }
        if ($user->isAdmin() && ! auth()->user()?->isAdmin()) {
            $impact->block('only an administrator can delete an administrator');
        }
        foreach (self::CASCADING_CREATED_BY as $table => $label) {
            $impact->blockIf($this->refCount($table, 'created_by', $id), $label.' created by this user', Str::plural($label).' created by this user');
        }
        $impact->note('Records this user created are kept. They are signed out immediately.');

        return $impact;
    }

    public function delete($id)
    {
        DB::table('sessions')->where('user_id', $id)->delete();

        return User::findOrFail($id)->delete();
    }
}
