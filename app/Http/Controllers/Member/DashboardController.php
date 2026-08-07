<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\RewardKatalog;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $member = Auth::guard('member')->user();

        $rewardUnggulan = RewardKatalog::aktif()
            ->orderBy('poin_dibutuhkan')
            ->take(3)
            ->get();

        return view('member.dashboard', [
            'member' => $member,
            'rewardUnggulan' => $rewardUnggulan,
        ]);
    }
}
