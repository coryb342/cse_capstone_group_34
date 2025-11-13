<?php

namespace App\Http\Controllers;

use App\Models\PredictiveModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    public function upload(Request $request) {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            return redirect()->back()->withErrors(['Unauthorized action.']);
        }

        $request->validate([
            'model_name' => 'required',
            'model_description' => 'required',
            'model_type' => 'required',
            'required_parameters' => 'required',
            'model_file' => 'required|file|max:256000',
            'model_accuracy' => 'nullable|max:100|min:0',
            'last_trained_on' => 'nullable|date',
        ]);

        $required_parameters = preg_split('/\s*,\s*/', $request->input('required_parameters'), -1, PREG_SPLIT_NO_EMPTY);
        $model_file = $request->file('model_file');

        $predictive_model = PredictiveModel::create([
            'organization_id' => $user->organization_id,
            'name' => $request->input('model_name'),
            'description' => $request->input('model_description'),
            'type' => $request->input('model_type'),
            'required_parameters' => json_encode($required_parameters),
            'accuracy' => $request->input('model_accuracy') ? $request->input('model_accuracy') : null,
            'last_trained_on' => $request->input('last_trained_on') ? $request->input('last_trained_on') : now(),
        ]);

        $predictive_model->save();
        $predictive_model_id = $predictive_model->id;

        $directory_path = $user->organization_id . '/models/' . $predictive_model_id;
        if (!Storage::disk('private')->exists($directory_path)) {
            Storage::disk('private')->makeDirectory($directory_path);
        }

        $model_file->storeAs($directory_path, $model_file->getClientOriginalName(), 'private');

        $predictive_model->update(['path' => $directory_path . '/' . $model_file->getClientOriginalName()]);

        return redirect()->back()->with(['success' => $predictive_model->name . ' uploaded successfully.']);
    }
}
