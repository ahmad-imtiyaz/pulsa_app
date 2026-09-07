<?php

namespace App\Http\Controllers;

use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DailySummaryService $summaryService
    ) {}

    public function index(Request $request)
    {
        $date = $request->get('tanggal', Carbon::today()->toDateString());
        $carbonDate = Carbon::parse($date);

        $data = $this->summaryService->getDashboardData($carbonDate);

        return view('dashboard', [
            'data' => $data,
            'tanggal' => $carbonDate,
        ]);
    }
}
