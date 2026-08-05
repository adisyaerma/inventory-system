<?php

namespace App\Imports\Concerns;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Shared, defensive Excel date parsing — used by every Import class so
 * behaviour stays identical everywhere. Never throws: an unparseable date
 * always comes back as null instead of blocking the row from being saved.
 *
 * IMPORTANT CONTEXT:
 * Source spreadsheets were typed in dd/mm/yyyy (Indonesian) format, but
 * opened/saved under a locale that reads mm/dd/yyyy. As a result:
 *
 *   - If the intended DAY is > 12 (e.g. "24/01/2026"), Excel cannot read
 *     it as a valid month, so it gives up and stores the cell as plain
 *     TEXT — still in the original dd/mm/yyyy order. No swap needed here;
 *     the text-parsing branches below already read day-first correctly.
 *
 *   - If the intended DAY is <= 12 (e.g. "05/03/2026", meant to be
 *     5 March), Excel happily (and wrongly) treats the first number as
 *     the MONTH and the second as the DAY, and converts the cell into a
 *     real Date/serial-number type — e.g. it becomes "May 3" instead of
 *     "March 5". This is the case that was silently saving wrong dates.
 *
 * Fix: whenever the cell arrives as an actual date type (DateTimeInterface
 * or a numeric Excel serial), we swap day <-> month back, because we know
 * Excel only auto-converts these ambiguous strings when the *original*
 * day was <= 12 — which guarantees both components are in the 1-12 range
 * and the swap is always a valid date.
 */
trait ParsesExcelDates
{
    private function parseDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Real Excel date cells often come back as a native DateTime/Carbon
        // object (not a string or serial number) — must be checked first.
        // Passing an object into trim()/is_numeric() below would either
        // throw a TypeError or silently misbehave.
        if ($value instanceof \DateTimeInterface) {
            return $this->fixAmbiguousDayMonth(Carbon::instance($value))->startOfDay();
        }

        // Excel serial number (raw, unformatted date cell).
        if (is_numeric($value)) {
            try {
                $date = Carbon::instance(Date::excelToDateTimeObject($value));

                return $this->fixAmbiguousDayMonth($date)->startOfDay();
            } catch (\Throwable $e) {
                return null;
            }
        }

        $value = trim((string) $value);

        if ($value === '' || $value === '-') {
            return null;
        }

        // Strip a trailing time part if present, e.g. "3/6/2026 00:00:00"
        $value = preg_replace('/\s+\d{1,2}:\d{2}(:\d{2})?$/', '', $value);

        // dd/mm/yyyy, dd-mm-yyyy, or dd.mm.yyyy — zero-padded or not, with
        // either a 4-digit or 2-digit year, e.g. 05/03/2025, 5-3-2025,
        // 24/1/2026, 13/06/26
        if (preg_match('#^(\d{1,2})[/\-.](\d{1,2})[/\-.](\d{2}|\d{4})$#', $value, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];

            // 2-digit year, e.g. "26" -> 2026.
            if ($year < 100) {
                $year += ($year <= 68) ? 2000 : 1900;
            }

            // Safeguard: if the "month" slot is out of range (13-31) but
            // the "day" slot would be a valid month, that value was
            // actually written month-first — swap instead of dropping it.
            if ($month > 12 && $day <= 12) {
                [$day, $month] = [$month, $day];
            }

            try {
                return Carbon::createFromFormat('d/m/Y', sprintf('%02d/%02d/%04d', $day, $month, $year))->startOfDay();
            } catch (\Throwable $e) {
                return null;
            }
        }

        // yyyy-mm-dd (ISO), e.g. 2025-03-05
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            } catch (\Throwable $e) {
                return null;
            }
        }

        // Coba beberapa format yang umum dipakai
        $formats = [
            'd/m/Y',
            'd-m-Y',
            'd.m.Y',
            'd/m/y',
            'd-m-y',
            'd.m.y',
            'Y-m-d',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->startOfDay();
            } catch (\Throwable $e) {
                // coba format berikutnya
            }
        }

        // Terakhir, hanya gunakan Carbon::parse jika string mengandung nama bulan,
        // misalnya "5 Maret 2026" atau "March 5, 2026".
        if (preg_match('/[a-zA-Z]/', $value)) {
            try {
                return Carbon::parse($value)->startOfDay();
            } catch (\Throwable $e) {
                //
            }
        }

        return null;
    }

    /**
     * Correct Excel's mm/dd misinterpretation of an originally dd/mm string.
     *
     * Excel only ever auto-converts an ambiguous "dd/mm" string into a real
     * date when the first number (the intended day) is <= 12 — otherwise it
     * can't be read as a valid month and stays as text. That means whenever
     * we receive an actual Date-typed value here, its current `day`
     * component is guaranteed to be <= 12, so swapping day <-> month is
     * always safe and always produces a valid date.
     *
     * If `day` is > 12, this was never one of those ambiguous strings in
     * the first place (e.g. it came from a real date picker / formula), so
     * we leave it untouched rather than risk corrupting a correct value.
     */
    private function fixAmbiguousDayMonth(Carbon $date): Carbon
    {
        $day = $date->day;
        $month = $date->month;

        if ($day > 12) {
            return $date;
        }

        return Carbon::create($date->year, $day, $month, 0, 0, 0);
    }
}           