<?php

namespace App\Http\Controllers;

use App\Models\OrgAccessCode;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules;
use Inertia\Inertia;


class JoinOrganizationController extends Controller
{
    public function index() {
        return Inertia::render('auth/JoinOrganization', []);
    }

    public function store(Request $request) {
        $request->validate([
            'org_access_code' => ['required', 'integer'],
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $active_access_code = OrgAccessCode::query()->where('access_code', '=', $request->get('org_access_code'))->where('is_active', '=', true)->first();

        if ($active_access_code && !$active_access_code->isExpired()) {

            $active_access_code->update(['is_active' => false]);

            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
                'organization_id' => $active_access_code->organization_id
            ]);

            event(new Registered($user));

            Auth::login($user);

            $request->session()->regenerate();

            return to_route('dashboard');

        } else {
            return Redirect::back()->withErrors(['org_access_code' => 'Invalid access code.']);
        }
    }
}
