<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        return Inertia::render('ActivityLog/Index', [
            'activities' => Activity::with('causer')
                ->latest()
                ->paginate(8)
        ]);
    }

    // Existing modal API method
    public function getLogs($module, $id)
    {
        $modelClass = Relation::getMorphedModel($module);
        if (!$modelClass) return response()->json(['error' => 'Not found'], 404);

        return response()->json(
            Activity::with('causer')
                ->where('subject_type', $modelClass)
                ->where('subject_id', $id)
                ->latest()
                ->get()
        );
    }


    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();

        return back()->with('danger', 'Activity log deleted successfully.');
    }

    public function clearAll()
    {
        Activity::truncate(); // This deletes ALL logs from the database
        return back()->with('danger', 'All activity logs have been cleared.');
    }
}
