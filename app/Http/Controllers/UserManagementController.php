<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index() {
        $user = auth()->user();

        if (! $user->isAdmin()) {
            return Redirect::back()->withErrors(['unauthorized' => 'Access Denied']);
        }

        $organization = Organization::find($user->organization_id);

        $all_org_users = $organization->users;

        return Inertia::render('UserManagement', ["users" => $all_org_users]);
    }

    public function toggleAdmin(Request $request) {

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user_to_modify_id = $request->get('user_id');

        $user_to_modify = User::query()->find($user_to_modify_id);

        if (! $user_to_modify->isAdmin()) {
            $user_to_modify->is_admin = true;
        } else {
            $user_to_modify->is_admin = false;
        }

        $user_to_modify->save();

        return redirect()->back()->with(['success' => 'User privileges have been successfully modified.']);
    }

    public function toggleStatus(Request $request) {

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user_to_modify_id = $request->get('user_id');

        $user_to_modify = User::query()->find($user_to_modify_id);

        if (! $user_to_modify->isActive()) {
            $user_to_modify->status = 0;
        } else {
            $user_to_modify->status = 1;
        }

        $user_to_modify->save();

        return redirect()->back()->with(['success' => 'User status has been successfully updated.']);
    }

    public function deleteUser(Request $request) {

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user_to_delete_id = $request->get('user_id');

        $user_to_delete = User::query()->find($user_to_delete_id);
        $user_to_delete_name = $user_to_delete->name;
        $user_to_delete->delete();

        return redirect()->back()->with(['success' =>  $user_to_delete_name . ' has been successfully deleted.']);
    }
}
