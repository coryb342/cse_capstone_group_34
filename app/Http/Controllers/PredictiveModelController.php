<?php

namespace App\Http\Controllers;

use App\Models\PredictiveModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PredictiveModelController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $organization_id = $user->organization_id;
        $predictive_models = PredictiveModel::query()->where('organization_id', '=',  $organization_id)->get();

        return Inertia::render('PredictiveModels', ['models' => $predictive_models]);
    }
}
