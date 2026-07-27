<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = Activity::with(['causer.roles'])
            ->latest()
            ->paginate(15);

        return view('pages.activity.index', compact('logs'));
    }
}
