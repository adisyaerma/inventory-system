<?php

namespace App\Imports\Concerns;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Shared, defensive Excel date parsing — used by every Import class so
 * behaviour stays identical everywhere. Never throws: an unparseable date
 * always comes back as null instead of blocking the row from being saved.
 *
 * IMPORTANT CONTEXT / HISTORY:
 * Some old source spreadsheets were typed in dd/mm/yyyy (Indonesian)
 * format but opened/saved under a locale that reads mm/dd/yyyy. As a
 * result, whenever the intended DAY was <= 12 (e.g. "05/03/2026", meant
 * to be 5 March), Excel would wrongly treat the first number as the
 * MONTH and the second as the DAY, silently turning it into "May 3"
 * instead of "March 5".
 *
 * This trait used to "fix" that by swapping day <-> month back on EVERY
 * real Date-typed cell whose day was <= 12 (see the now-removed
 * fixAmbiguousDayMonth() helper). That rule is unsafe: a real Date cell
 * with day <= 12 is produced both by that Excel mis-read AND by a date
 * that was simply always correct (e.g. "2 September 2026" has day=2,
 * which is <= 12 too). There is no way to tell those two cases apart
 * from the value alone, so the blanket swap ended up corrupting
 * perfectly correct dates (2 Sep -> 9 Feb) far more often than it fixed
 * genuinely mis-read ones.
 *
 * Current behaviour: a cell that Excel already parsed into a real
 * Date/serial value is trusted as-is — no swap. The day<->month
 * correction is only ever applied to TEXT cells (see the dd/mm/yyyy
 * regex branch below), and only when the "month" slot is unambiguously
 * out of range (13-31) while the "day" slot is a valid month — that
 * case is provably a month-first write and safe to swap, unlike the
 * real-Date case above.
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
        //
        // Trusted as-is (see class docblock above for why no swap happens
        // here).
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->startOfDay();
        }

        // Excel serial number (raw, unformatted date cell). Trusted as-is,
        // same reasoning as the DateTimeInterface branch above.
        if (is_numeric($value)) {
            try {
                return Carbon::instance(Date::excelToDateTimeObject($value))->startOfDay();
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
}