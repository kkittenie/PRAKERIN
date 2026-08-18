<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        try {
            $logs = Activity::with(['causer' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\User::class => ['roles'],
                ]);
            }])
                ->latest()
                ->paginate(15);

            return view('pages.activity.index', compact('logs'));
        } catch (\Throwable $e) {
            return \App\Support\ActivityLogHelper::handleException($e);
        }
    }
}
