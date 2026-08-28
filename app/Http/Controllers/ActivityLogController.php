<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $roleSubQuery = 'SELECT roles.name FROM model_has_roles '
                    . 'JOIN roles ON roles.id = model_has_roles.role_id '
                    . 'WHERE model_has_roles.model_id = activity_log.causer_id AND model_has_roles.model_type = ? LIMIT 1';

                $query = Activity::query()
                    ->leftJoin('users', function ($join) {
                        $join->on('users.id', '=', 'activity_log.causer_id')
                            ->where('activity_log.causer_type', '=', User::class);
                    })
                    ->leftJoin('members', function ($join) {
                        $join->on('members.id', '=', 'activity_log.causer_id')
                            ->where('activity_log.causer_type', '=', \App\Models\Member::class);
                    })
                    ->select('activity_log.*')
                    ->selectRaw("COALESCE(users.name, members.nama, 'Sistem') as causer_nama")
                    ->selectRaw(
                        "CASE WHEN activity_log.causer_type = ? THEN 'Member' WHEN activity_log.causer_type = ? THEN COALESCE(($roleSubQuery), 'User') ELSE '-' END as causer_role",
                        [\App\Models\Member::class, User::class, User::class]
                    );

                return \Yajra\DataTables\Facades\DataTables::of($query)
                    ->editColumn('created_at', fn($item) => $item->created_at->format('d M Y H:i'))
                    ->editColumn('causer_nama', fn($item) => e($item->causer_nama))
                    ->addColumn('causer_role_html', fn($item) => view('pages.activity.partials.role', compact('item'))->render())
                    ->editColumn('description', fn($item) => e($item->description))
                    ->orderColumn('causer_role_html', function ($query, $order) {
                        $query->orderBy('causer_role', $order)
                            ->orderBy('activity_log.created_at', 'desc');
                    })
                    ->filter(function ($query) use ($request) {
                        $search = $request->input('search.value');

                        if ($search) {
                            $query->where(function ($q) use ($search) {
                                $q->where('activity_log.description', 'like', "%{$search}%")
                                    ->orWhere('users.name', 'like', "%{$search}%")
                                    ->orWhere('members.nama', 'like', "%{$search}%");
                            });
                        }
                    }, true)
                    ->rawColumns(['causer_role_html'])
                    ->make(true);
            } catch (\Throwable $e) {
                activity('error')
                    ->causedBy(auth()->user())
                    ->withProperties(['error_message' => $e->getMessage(), 'exception' => get_class($e), 'file' => $e->getFile(), 'line' => $e->getLine()])
                    ->log('Error: ' . $e->getMessage());

                return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'Gagal memuat activity log.'], 500);
            }
        }

        return view('pages.activity.index');
    }
}