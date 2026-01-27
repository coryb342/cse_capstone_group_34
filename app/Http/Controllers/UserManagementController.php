<?php

namespace App\Http\Controllers;

use App\Models\OrgAccessCode;
use App\Models\Organization;
use App\Models\Role;
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

        $isSuper = $user->isSuper();

        $organization = Organization::find($user->organization_id);

        $org_allowed_seats = $organization->getAllowedSeats();

        $all_org_users = User::query()->where('organization_id', $organization->id)->with('roles')->get();

        return Inertia::render('UserManagement', ["users" => $all_org_users, "org_allowed_seats" => $org_allowed_seats, "current_user_is_super" => $isSuper]);
    }

    public function toggleAdmin(Request $request) {

        $admin_role_id = Role::query()->where('name', 'Admin')->first()->id;

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user_to_modify_id = $request->get('user_id');

        $user_to_modify = User::query()->find($user_to_modify_id);

        if (! $user_to_modify->isAdmin()) {
            $user_to_modify->roles()->attach($admin_role_id);
            $success = $user_to_modify->name . 'is now an Admin.';
        } else {
            if (auth()->user()->isSuper()) {
                $user_to_modify->roles()->detach($admin_role_id);
                $success = $user_to_modify->name . 'is no longer an Admin.';
            } else {
                return Redirect::back()->withErrors(['unauthorized' => 'Access Denied']);
            }
        }

        $user_to_modify->save();

        return redirect()->back()->with(['success' => $success]);
    }

    public function toggleStatus(Request $request) {

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user_to_modify_id = $request->get('user_id');

        $user_to_modify = User::query()->find($user_to_modify_id);

        if (! $user_to_modify->isActive()) {
            $user_to_modify->status = 0;
            $success = $user_to_modify->name . ' is now active';
        } else {
            $user_to_modify->status = 1;
            $success = $user_to_modify->name . ' is now inactive';

        }

        $user_to_modify->save();

        return redirect()->back()->with(['success' => $success]);
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

    public function generateAccessCode(Request $request) {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::query()->find($request->get('user_id'));

        if (! $user->isAdmin()) {
            return Redirect::back()->withErrors(['unauthorized' => 'Access Denied']);
        }

        $organization = Organization::query()->find($user->organization_id);

        $access_code = OrgAccessCode::create([
            'organization_id' => $organization->id,
            'access_code' => mt_rand(100000, 999999),
            'created_by' => $user->id,
            'is_active' => true,
        ]);

        $code = $access_code->access_code;

        return redirect()->back()->with(['success' => 'Access Code Generated Successfully' , 'code' => $code]);
    }
}
