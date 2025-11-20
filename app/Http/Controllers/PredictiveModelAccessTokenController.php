<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\PredictiveModel;
use App\Models\PredictiveModelAccessToken;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PredictiveModelAccessTokenController extends Controller
{
    public function index() {

        $user = auth()->user();
        $organization_id = $user->organization_id;

        $users_in_organization = User::query()->where('organization_id', $organization_id)->with('accessTokens')->get();
        $predictive_models = PredictiveModel::where('organization_id', $organization_id)->get();

        return Inertia::render('AccessTokenManagement', ['users' => $users_in_organization, 'models' => $predictive_models]);
    }

    public function grantAccess(Request $request) {
        $user = auth()->user();

        if (!$user->is_admin) {
            return redirect()->back()->withErrors(['unauthorized' => 'You are not authorized to access this page.']);
        }

        $request->validate([
            'user_id' => 'required',
            'model_id' => 'required',
        ]);

        $user_being_granted = User::query()->where('id', '=', $request->get(('user_id')))->first();

        $predictive_model_access_token = PredictiveModelAccessToken::create([
            'user_id' => $request->input('user_id'),
            'model_id' => $request->input('model_id'),
        ]);

        $predictive_model_access_token->save();

        return redirect()->back()->with('success', 'Access granted to ' . $user_being_granted->name . '.');




    }
}
