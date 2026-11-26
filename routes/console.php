<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Artisan::command('staging-out:split-history {--apply}', function () {
    $detail = \App\Models\StagingOutHistoryDetail::class;

    $ids = $detail::where('event_type', 'created')
        ->groupBy('staging_out_history_id')
        ->havingRaw('COUNT(*) > 1')
        ->pluck('staging_out_history_id');

    foreach (\App\Models\StagingOutHistory::findMany($ids) as $old) {
        $details = $detail::where('staging_out_history_id', $old->id)->orderBy('id')->get();
        $created = $details->where('event_type', 'created')->last();
        $row = \App\Models\StagingOut::find($old->staging_out_id);

        if (! $row || $created->created_at->lt($row->created_at->copy()->subSeconds(5))) {
            $this->warn("History {$old->id} ({$old->so_number}): perlu dicek manual");
            continue;
        }

        $this->info("History {$old->id} ({$old->so_number}) -> pisahkan {$row->so_number}");

        if (! $this->option('apply')) {
            continue;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($detail, $old, $details, $created, $row) {
            $delivered = $details->where('id', '<', $created->id)
                ->where('event_type', 'delivered')->last();

            $old->staging_out_id = null;
            $old->is_active = false;

            if ($delivered) {
                $meta = is_string($delivered->meta) ? json_decode($delivered->meta, true) : $delivered->meta;
                $old->do_number = $meta['do_number'] ?? null;
                $old->delivery_date = $meta['delivery_date'] ?? null;
            }

            $old->save();

            $new = \App\Models\StagingOutHistory::firstOrCreateForStaging($row);
            $new->forceFill([
                'initial_qty' => $created->qty_after,
                'created_at' => $created->created_at,
            ])->save();

            $detail::whereIn('id', $details->where('id', '>=', $created->id)->pluck('id'))
                ->update(['staging_out_history_id' => $new->id]);
        });
    }
});