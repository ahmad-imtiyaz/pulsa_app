<?php

namespace App\Jobs;

use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateDailySummaries implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Carbon $startDate,
        public Carbon $endDate,
        public int $userId
    ) {}

    public function handle(DailySummaryService $summaryService): void
    {
        $summaryService->recalculateRange($this->startDate, $this->endDate, $this->userId);
    }
}
