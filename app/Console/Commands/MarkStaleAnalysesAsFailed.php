<?php

namespace App\Console\Commands;

use App\Models\Analysis;
use Illuminate\Console\Command;

class MarkStaleAnalysesAsFailed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analyses:mark-stale-as-failed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marca como failed los analyses atascados en "analyzing" (el workflow nunca reportó de vuelta).';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $staleBefore = now()->subMinutes((int) config('analyzer.stale_after_minutes'));

        $count = Analysis::where('status', Analysis::STATUS_ANALYZING)
            ->where('updated_at', '<', $staleBefore)
            ->update(['status' => Analysis::STATUS_FAILED]);

        $this->info("Marked {$count} stale analysis(es) as failed.");
    }
}
