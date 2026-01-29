<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisterOrganizationController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'org_name' => ['required', 'string', 'max:255'],
            'num_seats' => ['required', 'integer', 'min:1', 'max:10'],
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $org = Organization::create([
            'org_name' => $request->org_name,
            'num_seats' => $request->num_seats,
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'organization_id' => $org->id,
        ]);

        $super_role_id = Role::query()->where('name', '=', 'Super')->first()->id;
        $admin_role_id = Role::query()->where('name', '=', 'Admin')->first()->id;
        $user_role_id = Role::query()->where('name', '=', 'User')->first()->id;

        $user->roles()->attach($super_role_id);
        $user->roles()->attach($admin_role_id);
        $user->roles()->attach($user_role_id);

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return to_route('dashboard');
    }
}
